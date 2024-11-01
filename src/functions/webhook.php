<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SimpleSecureWP\SimpleSecureStripe\Admin;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Charge;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Dispute;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException;
use SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentIntent;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Refund;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Review;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Source;
use SimpleSecureWP\SimpleSecureStripe\Utils;
/**
 * Processes the charge via webhooks for local payment methods like P24, EPS, etc.
 *
 * @param Source  $source
 * @param WP_REST_Request|null $request
 *
 * @since 1.0.0
 * @package Stripe/Functions
 */
function sswps_process_source_chargeable( $source, $request ) {
	if ( isset( $source->metadata['order_id'] ) ) {
		$order = wc_get_order( sswps_filter_order_id( $source->metadata['order_id'], $source ) );
	} else {
		// try finding order using source.
		$order = sswps_get_order_from_source_id( $source->id );
	}
	if ( ! $order ) {
		/**
		 * If the order ID metadata is empty, it's possible the source became chargeable before
		 * the plugin had a chance to update the order ID. Schedule a cron job to execute in 60 seconds
		 * so the plugin can update the order ID and the charge can be processed.
		 */
		if ( empty( $source->metadata['order_id'] ) ) {
			if ( method_exists( WC(), 'queue' ) && ! doing_action( 'sswps_retry_source_chargeable' ) ) {
				WC()->queue()->schedule_single( time() + MINUTE_IN_SECONDS, 'sswps_retry_source_chargeable', [ $source->id ] );
			}
		} else {
			/* translators: %s - source name */
			sswps_log_error( sprintf( __( 'Could not create a charge for source %s. No order ID was found in your WordPress database.', 'simple-secure-stripe' ), $source->id ) );
		}

		return;
	}

	/**
	 *
	 * @var Gateways\Abstract_Gateway $payment_method
	 */
	$payment_method = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ];

	// if the order has a transaction ID, then a charge has already been created.
	if ( $payment_method->has_order_lock( $order ) || ( $transaction_id = $order->get_transaction_id() ) ) {
		/* translators: %s - Order ID */
		sswps_log_info( sprintf( __( 'source.chargeable event received. Charge has already been created for order %s. Event exited.', 'simple-secure-stripe' ), $order->get_id() ) );

		return;
	}
	$payment_method->set_order_lock( $order );
	$payment_method->set_new_source_token( $source->id );
	$result = $payment_method->payment_object->process_payment( $order );

	if ( ! is_wp_error( $result ) && $result->complete_payment ) {
		$payment_method->payment_object->payment_complete( $order, $result->charge );
	}
}

/**
 * When the charge has succeeded, the order should be completed.
 *
 * @param Charge  $charge
 * @param WP_REST_Request $request
 *
 * @since 1.0.0
 * @package Stripe/Functions
 */
function sswps_process_charge_succeeded( $charge, $request ) {
	// charges that belong to a payment intent can be skipped
	// because the payment_intent.succeeded event will be called.
	if ( $charge->payment_intent ) {
		return;
	}
	$order = wc_get_order( sswps_filter_order_id( $charge->metadata['order_id'], $charge ) );
	if ( ! $order ) {
		/* translators: 1 - charge, 2 - Order ID */
		sswps_log_error( sprintf( __( 'Could not complete payment for charge %1$s. No order ID %2$s was found in your WordPress database.', 'simple-secure-stripe' ), $charge->id, $charge->metadata['order_id'] ) );

		return;
	}

	/**
	 *
	 * @var WC_Payment_Gateway $payment_method
	 */
	$payment_method = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ];
	/**
	 * Make sure the payment method is asynchronous because synchronous payments are handled via the source.chargeable event which processes the payment.
	 * This event is relevant for payment methods that receive a charge.succeeded event at some arbitrary amount of time
	 * after the source is chargeable.
	 */
	if ( $payment_method instanceof Gateways\Abstract_Gateway && ! $payment_method->synchronous ) {
		// If the order's charge status is not equal to charge status from Stripe, then complete_payment.
		if ( $order->get_meta( Constants::CHARGE_STATUS ) != $charge->status ) {
			// want to prevent plugin from processing capture_charge since charge has already been captured.
			remove_action( 'woocommerce_order_status_completed', 'sswps_order_status_completed' );

			if ( App::get( Admin\Settings\Advanced::class )->is_fee_enabled() ) {
				// retrieve the balance transaction
				$balance_transaction = Gateway::load()->mode( sswps_order_mode( $order ) )->balanceTransactions->retrieve( $charge->balance_transaction );
				$charge->balance_transaction = $balance_transaction;
			}
			// call payment complete so shipping, emails, etc are triggered.
			$payment_method->payment_object->payment_complete( $order, $charge );
			$order->add_order_note( __( 'Charge. succeeded webhook received. Payment has been completed.', 'simple-secure-stripe' ) );
		}
	}
}

/**
 *
 * @param PaymentIntent $intent
 * @param WP_REST_Request       $request
 *
 * @since 1.0.0
 * @package Stripe/Functions
 */
function sswps_process_payment_intent_succeeded( $intent, $request ) {
	$order = Utils\Misc::get_order_from_payment_intent( $intent );
	if ( ! $order ) {
		sswps_log_info( sprintf(
			/* translators: 1: intent ID, 2: Order ID. */
			__( 'Could not complete payment_intent. succeeded event for payment_intent %1$s. No order ID %2$s was found in your WordPress database. This typically happens when you have multiple webhooks setup for the same Stripe account. This order most likely originated from a different site.', 'simple-secure-stripe' ),
			$intent->id,
			isset( $intent->metadata->order_id ) ? $intent->metadata->order_id : __( '(No Order ID in metadata)', 'simple-secure-stripe' )
		) );

		return;
	}

	$payment_methods = WC()->payment_gateways()->payment_gateways();

	/**
	 * @var Gateways\Abstract_Gateway $payment_method
	 */
	$payment_method = $payment_methods[ $order->get_payment_method() ];

	if (
		$payment_method instanceof Gateways\Abstract_Local_Payment
		|| (
			$payment_method instanceof Gateways\Abstract_Gateway
			&& ! $payment_method->synchronous
		)
		|| (
			in_array( 'card', $intent->payment_method_types )
			&& $order->get_meta( Constants::STRIPE_MANDATE )
		)
	) {
		if ( $payment_method->has_order_lock( $order ) || $order->get_date_completed() ) {
			sswps_log_info( sprintf(
				/* translators: %s: Order ID */
				__( 'payment_intent. succeeded event received. Intent has been completed for order %s. Event exited.', 'simple-secure-stripe' ),
				$order->get_id()
			) );

			return;
		}

		$payment_method->set_order_lock( $order );
		$order->update_meta_data( Constants::PAYMENT_INTENT, Utils\Misc::sanitize_intent( $intent->toArray() ) );
		$result = $payment_method->payment_object->process_payment( $order );
		if ( ! is_wp_error( $result ) && $result->complete_payment ) {
			$payment_method->payment_object->payment_complete( $order, $result->charge );
			$order->add_order_note( __( 'payment_intent.succeeded webhook received. Payment has been completed.', 'simple-secure-stripe' ) );
		}
	}
}

/**
 *
 * @param Charge  $charge
 * @param WP_REST_Request $request
 *
 * @since 1.0.0
 * @package Stripe/Functions
 */
function sswps_process_charge_failed( $charge, $request ) {
	$order = wc_get_order( sswps_filter_order_id( $charge->metadata['order_id'], $charge ) );

	if ( $order ) {
		$payment_methods = WC()->payment_gateways()->payment_gateways();
		if ( isset( $payment_methods[ $order->get_payment_method() ] ) ) {
			/**
			 *
			 * @var Gateways\Abstract_Gateway $payment_method
			 */
			$payment_method = $payment_methods[ $order->get_payment_method() ];
			// only update order status if this is an asynchronous payment method,
			// and there is no completed date on the order. If there is a complete date it
			// means payment_complete was called on the order at some point
			if ( ! $payment_method->synchronous && ! $order->get_date_completed() ) {
				$order->update_status( apply_filters( 'sswps/charge_failed_status', 'failed' ), $charge->failure_message );
			}
		}
	}
}

/**
 * Function that processes the charge.refund webhook. If the refund is created in the Stripe dashboard, a
 * refund will be created in the WC system to keep WC and Stripe in sync.
 *
 * @param Charge $charge
 *
 * @since 1.0.0
 */
function sswps_process_create_refund( $charge ) {
	$mode  = $charge->livemode ? 'live' : 'test';
	$order = null;
	// get the order ID from the charge
	$order = Utils\Misc::get_order_from_charge( $charge );
	try {
		if ( ! $order ) {
			/* translators: %s: charge ID */
			throw new Exception( sprintf( __( 'Could not match order with charge %s.', 'simple-secure-stripe' ), $charge->id ) );
		}
		$response = Gateway::load( sswps_order_mode( $order ) )->refunds->all( array( 'charge' => $charge->id ) );
		$refunds  = $response->data;
		usort( $refunds, function ( $a, $b ) {
			// sort so refund with most recent created timestamp is first
			return $a->created < $b->created ? 1 : - 1;
		} );
		$refund = $refunds[0];

		/**
		 * @var Refund $refund
		 */
		// refund was not created via WC
		if ( ! isset( $refund->metadata['order_id'], $refund->metadata['created_via'] ) ) {
			$args = [
				'amount'         => Utils\Currency::remove_number_precision( $refund->amount, $order->get_currency() ),
				'order_id'       => $order->get_id(),
				'reason'         => $refund->reason,
				'refund_payment' => false
			];
			// if the order has been fully refunded, items should be re-stocked
			if ( $order->get_total( 'raw' ) == ( $args['amount'] + $order->get_total_refunded() ) ) {
				$args['restock_items'] = true;
				$line_items            = [];
				foreach ( $order->get_items() as $item_id => $item ) {
					$line_items[ $item_id ] = [
						'qty' => $item->get_quantity()
					];
				}
				$args['line_items'] = $line_items;
			}
			// create the refund
			$result = wc_create_refund( $args );

			// Update the refund in Stripe with metadata
			if ( ! is_wp_error( $result ) ) {
				$client = Gateway::load( $mode );
				/* translators: %s: refund amount. */
				$order->add_order_note( sprintf( __( 'Order refunded in Stripe. Amount: %s', 'simple-secure-stripe' ), $result->get_formatted_refund_amount() ) );
				$client->refunds->update( $refund->id, [
					'metadata' => [
						'order_id'    => $order->get_id(),
						'created_via' => 'stripe_dashboard'
					]
				] );
				if ( App::get( Admin\Settings\Advanced::class )->is_fee_enabled() ) {
					// retrieve the charge but with expanded objects so fee and net can be calculated.
					$charge = $client->charges->retrieve( $charge->id, [ 'expand' => [ 'balance_transaction', 'refunds.data.balance_transaction' ] ] );
					Utils\Misc::add_balance_transaction_to_order( $charge, $order, true );
				}
			} else {
				throw new Exception( $result->get_error_message() );
			}
		}
	} catch ( Exception $e ) {
		/* translators: %s: Stripe error message. */
		sswps_log_error( sprintf( __(  'Error processing refund webhook. Error: %s', 'simple-secure-stripe' ), $e->getMessage() ) );
	}
}

/**
 * @param $source_id
 *
 * @throws ApiErrorException
 */
function sswps_retry_source_chargeable( $source_id ) {
	$source = Gateway::load()->sources->retrieve( $source_id );
	sswps_log_info( sprintf( 'Processing source.chargeable via scheduled action. Source ID %s', $source_id ) );
	sswps_process_source_chargeable( $source, null );
}

/**
 * @param Dispute $dispute
 */
function sswps_charge_dispute_created( $dispute ) {
	if ( App::get( Admin\Settings\Advanced::class )->is_dispute_created_enabled() ) {
		$order = sswps_get_order_from_transaction( $dispute->charge );
		if ( ! $order ) {
			sswps_log_info( sprintf(
				/* translators: 1: dispute charge, 2: dispute ID */
				__( 'No order found for charge %1$s. Dispute %2$s', 'simple-secure-stripe' ),
				$dispute->charge, $dispute->id
			) );
		} else {
			$current_status = $order->get_status();
			/* translators: 1: dispute charge, 2: dispute status */
			$message = sprintf( __( 'A dispute has been created for charge %1$s. Dispute status: %2$s.', 'simple-secure-stripe' ), $dispute->charge, strtoupper( $dispute->status ) );
			$order->update_status(
				apply_filters(
					'sswps/dispute_created_order_status',
					App::get( Admin\Settings\Advanced::class )->get_option( 'dispute_created_status', 'on-hold' ),
					$dispute,
					$order
				),
				$message
			);

			// update the dispute with metadata that can be used later
			Gateway::load( sswps_order_mode( $order ) )->disputes->update( $dispute->id, [
				'metadata' => [
					'order_id'          => $order->get_id(),
					'prev_order_status' => $current_status
				]
			] );
			// @todo send an email to the admin so they know a dispute was created
		}
	}
}

/**
 * @param Dispute $dispute
 */
function sswps_charge_dispute_closed( $dispute ) {
	if ( App::get( Admin\Settings\Advanced::class )->is_dispute_closed_enabled() ) {
		if ( isset( $dispute->metadata['order_id'] ) ) {
			$order = wc_get_order( absint( $dispute->metadata['order_id'] ) );
		} else {
			$order = sswps_get_order_from_transaction( $dispute->charge );
		}
		if ( ! $order ) {
			/* translators: 1: dispute charge, 2: dispute ID */
			return sswps_log_info( sprintf( __( 'No order found for charge %1$s. Dispute %2$s', 'simple-secure-stripe' ), $dispute->charge, $dispute->id ) );
		}

		/* translators: 1: dispute ID, 2: dispute status */
		$message = sprintf( __( 'Dispute %1$s has been closed. Result: %2$s.', 'simple-secure-stripe' ), $dispute->id, $dispute->status );
		switch ( $dispute->status ) {
			case 'won':
				//set the order's status back to what it was before the dispute
				if ( isset( $dispute->metadata['prev_order_status'] ) ) {
					$status = $dispute->metadata['prev_order_status'];
				} else {
					$status = $order->needs_processing() ? 'processing' : 'completed';
				}
				$order->update_status( $dispute->metadata['prev_order_status'], $message );
				break;
			case 'lost':
				$order->update_status( apply_filters( 'sswps/dispute_closed_order_status', 'failed', $dispute, $order ), $message );
		}
	}
}

/**
 * @param Review $review
 */
function sswps_review_opened( Review $review ) {
	$order = null;

	if ( ! App::get( Admin\Settings\Advanced::class )->is_review_opened_enabled() ) {
		return;
	}

	if ( isset( $review->charge ) ) {
		$order = sswps_get_order_from_transaction( $review->charge );
	} else {
		// In some cases, Stripe does not provide the charge ID in the Review object.
		$pi = $review->payment_intent;
		if ( $pi ) {
			$payment_intent = Gateway::load()->mode( $review )->paymentIntents->retrieve( $pi );
			if ( isset( $payment_intent->metadata['order_id'] ) ) {
				$order = wc_get_order( $payment_intent->metadata['order_id'] );
			}
		}
	}

	if ( ! $order ) {
		return;
	}

	$status = $order->get_status();
	$order->update_meta_data( Constants::PREV_STATUS, $status );

	/* translators: 1: charge, 2: reason */
	$message = sprintf( __( 'A review has been opened for charge %1$s. Reason: %2$s.', 'simple-secure-stripe' ), $review->charge, strtoupper( $review->reason ) );
	$order->update_status( apply_filters( 'sswps/review_opened_order_status', 'on-hold', $review, $order ), $message );
}

/**
 * @param Review $review
 */
function sswps_review_closed( Review $review ) {
	$order = null;

	if ( ! App::get( Admin\Settings\Advanced::class )->is_review_closed_enabled() ) {
		return;
	}

	if ( isset( $review->charge ) ) {
		$order = sswps_get_order_from_transaction( $review->charge );
	} else {
		// In some cases, Stripe does not provide the charge ID in the Review object.
		$pi = $review->payment_intent;
		if ( $pi ) {
			$payment_intent = Gateway::load()->mode( $review )->paymentIntents->retrieve( $pi );
			if ( isset( $payment_intent->metadata['order_id'] ) ) {
				$order = wc_get_order( $payment_intent->metadata['order_id'] );
			}
		}
	}

	if ( ! $order ) {
		return;
	}

	$status = $order->get_meta( Constants::PREV_STATUS );
	if ( ! $status ) {
		$status = $order->needs_processing() ? 'processing' : 'completed';
	}
	$order->delete_meta_data( Constants::PREV_STATUS );
	/* translators: 1: charge, 2: reason */
	$message = sprintf( __( 'A review has been closed for charge %1$s. Reason: %2$s.', 'simple-secure-stripe' ), $review->charge, strtoupper( $review->reason ) );
	$order->update_status( $status, $message );
}

/**
 * @param PaymentIntent $payment_intent
 */
function sswps_process_requires_action( $payment_intent ) {
	if ( isset( $payment_intent->metadata['gateway_id'], $payment_intent->metadata['order_id'] ) ) {
		if ( in_array( $payment_intent->metadata['gateway_id'], [ 'sswps_oxxo', 'sswps_boleto', 'sswps_konbini' ], true ) ) {
			$order = Utils\Misc::get_order_from_payment_intent( $payment_intent );
			if ( ! $order ) {
				return;
			}

			$gateways = WC()->payment_gateways()->payment_gateways();

			if ( ! empty( $gateways[ $payment_intent->metadata['gateway_id'] ] ) ) {
				/**
				 * @var Gateways\Abstract_Gateway $payment_method
				 */
				$payment_method = $gateways[ $payment_intent->metadata['gateway_id'] ];
				$payment_method->process_voucher_order_status( $order );
				/* translators: 1: order ID, 2: payment intent ID */
				sswps_log_info( sprintf( __( 'Order status processed for Voucher payment. Order ID %1$s. Payment Intent %2$s', 'simple-secure-stripe' ), $order->get_id(), $payment_intent->id ) );
			}
		}
	}
}

/**
 * @param Charge $charge
 */
function sswps_process_charge_pending( $charge ) {
	if ( isset( $charge->metadata['gateway_id'], $charge->metadata['order_id'] ) ) {
		$payment_methods = WC()->payment_gateways()->payment_gateways();
		$payment_method  = $charge->metadata['gateway_id'];
		$payment_method  = isset( $payment_methods[ $payment_method ] ) ? $payment_methods[ $payment_method ] : null;
		if ( $payment_method instanceof Gateways\Abstract_Gateway && ! $payment_method->synchronous ) {
			$order = wc_get_order( sswps_filter_order_id( $charge->metadata['order_id'], $charge ) );
			if ( $order ) {
				// temporary check to prevent race conditions caused by status update also occurring in
				// class-sswps-payment-intent.php line 89 at the same time as the webhook being received for ach payments
				if ( $payment_method->id !== 'sswps_ach' && ! $payment_method->has_order_lock( $order ) ) {
					$payment_method->set_order_lock( $order );
					$payment_method->payment_object->payment_complete( $order, $charge );
					$payment_method->release_order_lock( $order );
				}
			}
		}
	}
}
