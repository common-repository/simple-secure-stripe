<?php

namespace SimpleSecureWP\SimpleSecureStripe\Payment;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Customer_Manager;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentIntent;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WC_Order;

/**
 *
 * @since   1.0.0
 *
 * @author Simple & Secure WP
 * @package Stripe/Classes
 */
class Intent extends Abstract_Payment {

	private $update_payment_intent = false;

	private $retry_count = 0;

	private $payment_intent_args;

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::process_payment()
	 */
	public function process_payment( $order ) {
		// first check to see if a payment intent can be used
		if ( $intent = $this->can_use_payment_intent( $order ) ) {
			if ( $this->can_update_payment_intent( $order, $intent ) ) {
				$intent = $this->gateway->paymentIntents->update( $intent['id'], $this->get_payment_intent_args( $order, false, $intent ) );
			}
		} else {
			$intent = $this->gateway->paymentIntents->create( $this->get_payment_intent_args( $order ) );
		}

		if ( is_wp_error( $intent ) ) {
			if ( $this->should_retry_payment( $intent, $order ) ) {
				return $this->process_payment( $order );
			} else {
				$this->add_payment_failed_note( $order, $intent );

				return $intent;
			}
		}

		// always update the order with the payment intent.
		$order->update_meta_data( Constants::PAYMENT_INTENT_ID, $intent->id );
		$order->update_meta_data( Constants::PAYMENT_METHOD_TOKEN, is_object( $intent->payment_method ) ? $intent->payment_method->id : $intent->payment_method );
		$order->update_meta_data( Constants::MODE, sswps_mode() );
		$order->update_meta_data( Constants::PAYMENT_INTENT, Utils\Misc::sanitize_intent( $intent->toArray() ) );
		$order->save();

		if ( $intent->status === 'requires_confirmation' ) {
			$intent = $this->gateway->paymentIntents->confirm(
				$intent->id,
				apply_filters( 'sswps/payment_intent_confirmation_args', $this->payment_method->get_payment_intent_confirmation_args( $intent, $order ), $intent, $order )
			);
		}

		// the intent was processed.
		if ( $intent->status === 'succeeded' || $intent->status === 'requires_capture' ) {
			$charge = $intent->charges->data[0];
			if ( isset( $intent->setup_future_usage, $charge->payment_method_details ) && 'off_session' === $intent->setup_future_usage ) {
				if ( ! defined( Constants::PROCESSING_ORDER_PAY ) ) {
					$this->payment_method->save_payment_method(
						is_object( $intent->payment_method ) ? $intent->payment_method->id : $intent->payment_method, /* @phpstan-ignore-line */
						$order,
						$charge->payment_method_details
					);
				}
			}
			// remove metadata that's no longer needed
			$order->delete_meta_data( Constants::PAYMENT_INTENT );

			$this->destroy_session_data();

			return (object) [
				'complete_payment' => true,
				'charge'           => $charge,
			];
		}
		if ( $intent->status === 'processing' ) {
			$this->destroy_session_data();
			$order->update_status( apply_filters( 'sswps/charge_pending_order_status', 'on-hold', $intent->charges->data[0], $order ) );
			$this->payment_method->save_order_meta( $order, $intent->charges->data[0] );

			return (object) [
				'complete_payment' => false,
				'redirect'         => $this->payment_method->get_return_url( $order ),
			];
		}
		if ( in_array( $intent->status, [ 'requires_action', 'requires_payment_method', 'requires_source_action', 'requires_source' ], true ) ) {
			/**
			 * Allow 3rd party code to alter the order status of an asynchronous payment method.
			 * The plugin uses the charge.pending event to set the order's status to on-hold.
			 */
			if ( ! $this->payment_method->synchronous ) {
				$status = apply_filters( 'sswps/asynchronous_payment_method_order_status', 'pending', $order, $intent );
				if ( 'pending' !== $status ) {
					$order->update_status( $status );
				}
			}

			return (object) [
				'complete_payment' => false,
				'redirect'         => $this->payment_method->get_payment_intent_checkout_url( $intent, $order ),
			];
		}
	}

	public function scheduled_subscription_payment( $amount, $order ) {
		$update_subscription = false;
		$subscription        = null;
		$args                = $this->get_payment_intent_args( $order );

		// Unset in case 3rd-party code adds this attribute.
		if ( isset( $args['setup_future_usage'] ) ) {
			unset( $args['setup_future_usage'] );
		}

		$args['confirm']        = true;
		$args['off_session']    = true;
		$args['payment_method'] = trim( $this->payment_method->get_order_meta_data( Constants::PAYMENT_METHOD_TOKEN, $order ) );

		if ( ( $customer = $this->payment_method->get_order_meta_data( Constants::CUSTOMER_ID, $order ) ) ) {
			$args['customer'] = $customer;
		}

		if ( ( $mandate = $order->get_meta( Constants::STRIPE_MANDATE ) ) ) {
			$args['mandate'] = $mandate;
		}

		// if the payment method is empty, check the subscription's parent order to see if that has the payment method
		if ( empty( $args['payment_method'] ) ) {
			$subscription_id = $order->get_meta( '_subscription_renewal' );
			if ( $subscription_id ) {
				$subscription = wcs_get_subscription( absint( $subscription_id ) );
				if ( $subscription ) {
					$parent_order = $subscription->get_parent();
					if ( $parent_order ) {
						$payment_method_id = $parent_order->get_meta( Constants::PAYMENT_METHOD_TOKEN );
						if ( $payment_method_id ) {
							// retrieve the payment method
							$payment_method = $this->gateway->mode( $order )->paymentMethods->retrieve( $payment_method_id );
							if ( $payment_method ) {
								$args['payment_method'] = $payment_method->id;
								$args['customer']       = $payment_method->customer;
								$update_subscription    = true;
							}
						}
					}
				}
			}
		}

		$intent = $this->gateway->mode( $order )->paymentIntents->create( $args );

		$order->update_meta_data( Constants::PAYMENT_INTENT_ID, $intent->id );

		if ( $subscription && $update_subscription ) {
			$subscription->update_meta_data( Constants::PAYMENT_METHOD_TOKEN, is_object( $intent->payment_method ) ? $intent->payment_method->id : $intent->payment_method );
			$subscription->update_meta_data( Constants::CUSTOMER_ID, $intent->customer );
			$subscription->save();
		}

		$charge = $intent->offsetGet( 'charges' )->data[0];

		if ( in_array( $intent->status, [ 'succeeded', 'requires_capture', 'processing' ] ) ) {
			return (object) [
				'complete_payment' => true,
				'charge'           => $charge,
			];
		} else {
			return (object) [
				'complete_payment' => false,
				'charge'           => $charge,
			];
		}

	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::process_pre_order_payment()
	 */
	public function process_pre_order_payment( $order ) {
		$args = $this->get_payment_intent_args( $order );

		// Unset in case 3rd-party code adds this attribute.
		if ( isset( $args['setup_future_usage'] ) ) {
			unset( $args['setup_future_usage'] );
		}

		$args['confirm']        = true;
		$args['off_session']    = true;
		$args['payment_method'] = $this->payment_method->get_order_meta_data( Constants::PAYMENT_METHOD_TOKEN, $order );

		if ( ( $customer = $this->payment_method->get_order_meta_data( Constants::CUSTOMER_ID, $order ) ) ) {
			$args['customer'] = $customer;
		}

		$intent = $this->gateway->mode( sswps_order_mode( $order ) )->paymentIntents->create( $args );

		$order->update_meta_data( Constants::PAYMENT_INTENT_ID, $intent->id );

		$charge = $intent->offsetGet( 'charges' )->data[0];

		if ( in_array( $intent->status, [ 'succeeded', 'requires_capture', 'processing' ] ) ) {
			return (object) [
				'complete_payment' => true,
				'charge'           => $charge,
			];
		}

		return (object) [
			'complete_payment' => false,
			'charge'           => $charge,
		];
	}

	/**
	 * Compares the order's saved intent to the order's attributes.
	 * If there is a delta, then the payment intent can be updated. The intent should
	 * only be updated if this is the checkout page.
	 *
	 * @param WC_Order $order
	 */
	public function can_update_payment_intent( $order, $intent = null ) {
		$result = true;
		if ( ! $this->update_payment_intent && ( defined( Constants::WOOCOMMERCE_STRIPE_ORDER_PAY ) || ! is_checkout() || defined( Constants::REDIRECT_HANDLER ) || defined( Constants::PROCESSING_PAYMENT ) ) ) {
			$result = false;
		} else {
			$intent = ! $intent ? $order->get_meta( Constants::PAYMENT_INTENT ) : $intent;
			if ( $intent ) {
				$order_hash  = implode(
					'_',
					[
						Utils\Currency::add_number_precision( $order->get_total( 'raw' ), $order->get_currency() ),
						strtolower( $order->get_currency() ),
						$this->get_payment_method_charge_type(),
						sswps_get_customer_id( $order->get_user_id() ),
						$this->payment_method->get_payment_method_from_request(),
					]
				);
				$intent_hash = implode(
					'_',
					[
						$intent['amount'],
						$intent['currency'],
						$intent['capture_method'],
						$intent['customer'],
						isset( $intent['payment_method']['id'] ) ? $intent['payment_method']['id'] : '',
					]
				);
				$result      = $order_hash !== $intent_hash || ! in_array( $this->payment_method->get_payment_method_type(), $intent['payment_method_types'] );
			}
		}

		return apply_filters( 'sswps/can_update_payment_intent', $result, $intent, $order );
	}

	/**
	 *
	 * @param WC_Order $order
	 * @param bool $new
	 * @param PaymentIntent|false $intent
	 */
	public function get_payment_intent_args( $order, $new = true, $intent = null ) {
		$this->add_general_order_args( $args, $order );

		$args['capture_method'] = $this->get_payment_method_charge_type();
		if ( ( $statement_descriptor = App::get( Settings\Advanced::class )->get_option( 'statement_descriptor' ) ) ) {
			$args['statement_descriptor'] = Utils\Misc::sanitize_statement_descriptor( $statement_descriptor );
		}
		if ( $new ) {
			$args['confirmation_method'] = $this->payment_method->get_confirmation_method( $order );
			$args['confirm']             = false;
		} else {
			if ( $intent && $intent['status'] === 'requires_action' ) {
				unset( $args['capture_method'] );
			}
			if ( isset( $intent['payment_method']['type'] ) && $intent['payment_method']['type'] === 'link' ) {
				/**
				 * Unset the payment method so it's not updated by Stripe. We don't want to update the payment method
				 * if it exists because it already contains the Link mandate.
				 */
				unset( $args['payment_method'] );
			}
			if ( $intent && $intent->status === 'requires_action' ) {
				/**
				 * The statement_descriptor can't be updated when the intent's status is requires_action
				 */
				unset( $args['statement_descriptor'] );
			}
		}

		if ( App::get( Settings\Advanced::class )->is_email_receipt_enabled() && ( $email = $order->get_billing_email() ) ) {
			$args['receipt_email'] = $email;
		}

		if ( ( $customer_id = sswps_get_customer_id( $order->get_customer_id() ) ) ) {
			$args['customer'] = $customer_id;
		}

		if (
			$this->payment_method->should_save_payment_method( $order )
			|| ( $this->payment_method->supports( 'add_payment_method' )
				&& apply_filters(
					'sswps/force_save_payment_method',
					false,
					$order,
					$this->payment_method
				) )
		) {
			$args['setup_future_usage'] = 'off_session';
		}

		$args['payment_method_types'][] = $this->payment_method->get_payment_method_type();

		// if there is a payment method attached already, then ensure the payment_method_type
		// associated with that attached payment_method is included.
		if ( $intent && ! empty( $intent->payment_method ) && \is_array( $intent->payment_method_types ) ) {
			$args['payment_method_types'] = array_values( array_unique( array_merge( $args['payment_method_types'], $intent->payment_method_types ) ) );
		}

		$this->payment_method->add_stripe_order_args( $args, $order );

		/**
		 * @param array    $args
		 * @param WC_Order $order
		 * @param Intent   $object
		 */
		$this->payment_intent_args = apply_filters( 'sswps/payment_intent_args', $args, $order, $this );

		return $this->payment_intent_args;
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::capture_charge()
	 */
	public function capture_charge( $amount, $order ) {
		$payment_intent = $this->payment_method->get_order_meta_data( Constants::PAYMENT_INTENT_ID, $order );
		if ( empty( $payment_intent ) ) {
			$charge         = $this->gateway->mode( sswps_order_mode( $order ) )->charges->retrieve( $order->get_transaction_id() );
			$payment_intent = $charge->payment_intent;
			$order->update_meta_data( Constants::PAYMENT_INTENT_ID, $payment_intent );
			$order->save();
		}
		$params = apply_filters( 'sswps/payment_intent_capture_args', [ 'amount_to_capture' => Utils\Currency::add_number_precision( $amount, $order->get_currency() ) ], $amount, $order );

		$result = $this->gateway->mode( sswps_order_mode( $order ) )->paymentIntents->capture( $payment_intent, $params );
		return $result->offsetGet( 'charges' )->data[0];
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::void_charge()
	 */
	public function void_charge( $order ) {
		// fetch the intent and check its status
		$payment_intent = $this->gateway->mode( sswps_order_mode( $order ) )->paymentIntents->retrieve( $order->get_meta( Constants::PAYMENT_INTENT_ID ) );
		$statuses       = [ 'requires_payment_method', 'requires_capture', 'requires_confirmation', 'requires_action' ];
		if ( 'canceled' !== $payment_intent->status ) {
			if ( in_array( $payment_intent->status, $statuses ) ) {
				return $this->gateway->mode( sswps_order_mode( $order ) )->paymentIntents->cancel( $payment_intent->id );
			} elseif ( 'succeeded' === $payment_intent->status ) {
				return $this->process_refund( $order, $order->get_total( 'raw' ) - (float) $order->get_total_refunded() );
			}
		}
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::get_payment_method_from_charge()
	 */
	public function get_payment_method_from_charge( $charge ) {
		return $charge->payment_method;
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::add_order_payment_method()
	 */
	public function add_order_payment_method( &$args, $order ) {
		$args['payment_method'] = $this->payment_method->get_payment_method_from_request();
		if ( empty( $args['payment_method'] ) ) {
			unset( $args['payment_method'] );
		}
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	public function can_use_payment_intent( $order ) {
		$intent         = $order->get_meta( Constants::PAYMENT_INTENT );
		$session_intent = (array) Utils\Misc::get_payment_intent_from_session();
		if ( $session_intent ) {
			if ( ! $intent || $session_intent['id'] !== $intent['id'] ) {
				$intent = $session_intent;
			}
		}
		$intent = $intent ? $this->gateway->paymentIntents->retrieve( $intent['id'], apply_filters( 'sswps/payment_intent_retrieve_args', [ 'expand' => [ 'payment_method' ] ], $order, $intent['id'] ) ) : false;
		if ( $intent ) {
			// If an intent is cancelled, then it's likely that it timed out and can't be used.
			if ( $intent->status === 'canceled' ) {
				$intent = false;
			} else {
				if ( \in_array( $intent->status, [ 'succeeded', 'requires_capture', 'processing' ] ) && ! defined( Constants::REDIRECT_HANDLER ) ) {
					/**
					 * If the status is succeeded, and the order ID on the intent doesn't match this checkout's order ID, we know this is
					 * a previously processed intent and so should not be used.
					 */
					if ( isset( $intent->metadata['order_id'] ) && $intent->metadata['order_id'] != $order->get_id() ) {
						$intent = false;
					}
				} elseif ( $intent['confirmation_method'] != $this->payment_method->get_confirmation_method( $order ) ) {
					$intent = false;
				}
			}

			// compare the active environment to the order's environment
			$mode = sswps_order_mode( $order );
			if ( $mode && $mode !== sswps_mode() ) {
				$intent = false;
			}
		} else {
			$intent = false;
		}

		return $intent;
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::can_void_charge()
	 */
	public function can_void_order( $order ) {
		return $order->get_meta( Constants::PAYMENT_INTENT_ID );
	}

	public function set_update_payment_intent( $bool ) {
		$this->update_payment_intent = $bool;
	}

	public function destroy_session_data() {
		Utils\Misc::delete_payment_intent_to_session();
	}

	/**
	 * @param \WP_Error $error
	 * @param \WC_Order $order
	 */
	public function should_retry_payment( $error, $order ) {
		$result      = false;
		$data        = $error->get_error_data();
		$delete_data = function() use ( $order ) {
			Utils\Misc::delete_payment_intent_to_session();
			$order->delete_meta_data( Constants::PAYMENT_INTENT );
		};

		/**
		 * Merchants sometimes change the Stripe account that the plugin is connected to. This results in API errors
		 * because the customer ID doesn't exist in the new Stripe account. Create a customer ID to avoid this error.
		 *
		 * @return void
		 */
		$create_customer = function() use ( $order ) {
			if ( $order->get_customer_id() ) {
				$mode     = sswps_order_mode( $order );
				$response = App::get( Customer_Manager::class )->create_customer( new \WC_Customer( $order->get_customer_id() ), $mode );
				if ( ! is_wp_error( $response ) ) {
					sswps_save_customer( $response->id, $order->get_customer_id(), $mode );
					sswps_log_info(
						sprintf(
							__( 'Customer ID %1$s does not exist in Stripe account %2$s. New customer ID %3$s created for user ID %4$s.', 'simple-secure-stripe' ),
							$this->payment_intent_args['customer'],
							App::get( Settings\Account::class )->get_account_id( $mode ),
							$response->id,
							$order->get_customer_id()
						)
					);
					$order->save();
				}
			}
		};

		$delete_payment_token = function() use ( $order ) {
			// Remove the payment method that no longer exists.
			if ( $order->get_customer_id() ) {
				$token = $this->payment_method->get_token( $this->payment_intent_args['payment_method'], $order->get_customer_id() );
				if ( $token ) {
					$token->delete();
					sswps_log_info(
						sprintf(
							__( 'Order ID: %1$s. Customer attempted to use saved payment method %2$s but it does not exist in Stripe account %3$s. The payment method has been removed from the WooCommerce database.', 'simple-secure-stripe' ),
							$order->get_id(),
							$token->get_token(),
							App::get( Settings\Account::class )->get_account_id( sswps_order_mode( $order ) )
						)
					);

					if ( wp_doing_ajax() && WC()->session ) {
						// Trigger a page re-load so the page refreshes and the updated list of payment methods are rendered.
						WC()->session->reload_checkout = true; // @phpstan-ignore-line - We are dynamically adding this property.
					}
				}
			}
		};

		if ( $this->retry_count < 1 ) {
			if ( $data && is_array( $data ) ) {
				if ( isset( $data['payment_intent'] ) ) {
					if ( isset( $data['payment_intent']['status'] ) ) {
						$result = in_array( $data['payment_intent']['status'], [ 'succeeded', 'requires_capture' ], true );
						if ( $result ) {
							$delete_data();
						}
					}
				} elseif ( isset( $data['code'] ) ) {
					if ( $data['code'] === 'resource_missing' ) {
						if ( $data['param'] === 'customer' ) {
							$create_customer();
						} elseif ( $data['param'] === 'payment_method' ) {
							$delete_payment_token();

							return false;
						} else {
							$delete_data();
						}

						$result = true;
					}
				}
			}
			if ( $result ) {
				$this->retry_count += 1;
			}
		}

		return $result;
	}

	/**
	 * @param \WP_Error $error
	 * @param \WC_Order $order
	 */
	public function post_payment_process_error_handling( $error, $order ) {
		$data = $error->get_error_data();
		if ( isset( $data['payment_intent'] ) ) {
			Utils\Misc::save_payment_intent_to_session( $data['payment_intent'], $order );
		}
	}

}
