<?php

namespace SimpleSecureWP\SimpleSecureStripe\Payment;

use Exception;
use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Charge;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WC_Order;
use WC_Order_Item_Product;
use WP_Error;

/**
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Abstract
 *
 */
abstract class Abstract_Payment {

	/**
	 *
	 * @var Gateways\Abstract_Gateway|null
	 */
	protected $payment_method;

	/**
	 *
	 * @var Gateway|null
	 */
	protected $gateway;

	/**
	 *
	 * @param Gateways\Abstract_Gateway|null $payment_method
	 * @param Gateway|null                   $gateway
	 */
	public function __construct( $payment_method, $gateway ) {
		$this->payment_method = $payment_method;
		$this->gateway        = $gateway;
	}

	public function get_gateway() {
		return $this->gateway;
	}

	/**
	 * Process the payment for the order.
	 *
	 * @param WC_Order                  $order
	 */
	public abstract function process_payment( $order );

	/**
	 *
	 * @param float    $amount
	 * @param WC_Order $order
	 *
	 * @return Charge
	 */
	public abstract function capture_charge( $amount, $order );

	/**
	 *
	 * @param WC_Order $order
	 */
	public abstract function void_charge( $order );

	/**
	 *
	 * @param Charge $charge
	 */
	public abstract function get_payment_method_from_charge( $charge );

	/**
	 *
	 * @param array    $args
	 * @param WC_Order $order
	 */
	public abstract function add_order_payment_method( &$args, $order );

	/**
	 *
	 * @param float    $amount
	 * @param WC_Order $order
	 */
	public abstract function scheduled_subscription_payment( $amount, $order );

	/**
	 *
	 * @param WC_Order $order
	 */
	public abstract function process_pre_order_payment( $order );

	/**
	 * Return true if the charge can be voided.
	 *
	 * @param WC_Order $order
	 */
	public abstract function can_void_order( $order );

	/**
	 * Perform post payment processes
	 *
	 * @since 1.0.0
	 *
	 * @param Charge   $charge
	 *
	 * @param WC_Order $order
	 */
	public function payment_complete( $order, $charge ) {
		Utils\Misc::add_balance_transaction_to_order( $charge, $order );
		$this->payment_method->save_order_meta( $order, $charge );
		if ( 'pending' === $charge->status ) {
			$order->update_status(
				apply_filters( 'sswps/pending_charge_status', 'on-hold', $order, $this->payment_method ),
				sprintf(
					/* translators: 1: transaction ID, 2: payment method. */
					__( 'Charge %1$s is pending. Payment Method: %2$s. Payment will be completed once charge.succeeded webhook received from Stripe.', 'simple-secure-stripe' ),
					$order->get_transaction_id(),
					$order->get_payment_method_title()
				)
			);
		} else {
			if ( $charge->captured ) {
				$order->payment_complete( $charge->id );
			} else {
				$order_status = $this->payment_method->get_option( 'order_status' );
				$order->update_status( apply_filters( 'sswps/authorized_order_status', 'default' === $order_status ? 'on-hold' : $order_status, $order, $this->payment_method ) );
			}
			$order->add_order_note(
				sprintf(
					/* translators: 1: Stripe order capture state, 2: transaction ID, 3: payment method. */
					__( 'Order %1$s successful in Stripe. Charge: %2$s. Payment Method: %3$s', 'simple-secure-stripe' ),
					$charge->captured ? _x( 'charge', 'Stripe order capture state', 'simple-secure-stripe' ) : _x( 'authorization', 'Stripe order capture state', 'simple-secure-stripe' ),
					$order->get_transaction_id(),
					$order->get_payment_method_title()
				)
			);
		}

		/**
		 * @since 1.0.0
		 */
		do_action( 'sswps/order_payment_complete', $charge, $order );
	}

	/**
	 *
	 * @param WC_Order $order
	 * @param float    $amount
	 *
	 * @return bool|WP_Error
	 *
	 * @throws Exception
	 */
	public function process_refund( $order, $amount = null ) {
		$charge = $order->get_transaction_id();
		try {
			if ( empty( $charge ) ) {
				throw new Exception( __( 'Transaction Id cannot be empty.', 'simple-secure-stripe' ) );
			}

			/**
			 * @since 1.0.0
			 *
			 * @param array            $refund_args
			 * @param Abstract_Payment $object
			 * @param WC_Order         $order
			 * @param float            $amount
			 */
			$args   = apply_filters( 'sswps/refund_args', [
				'charge'   => $charge,
				'amount'   => Utils\Currency::add_number_precision( $amount, $order->get_currency() ),
				'metadata' => [
					'order_id'    => $order->get_id(),
					'created_via' => 'woocommerce',
				],
				'expand'   => App::get( Settings\Advanced::class )->is_fee_enabled() ? [ 'charge.balance_transaction', 'charge.refunds.data.balance_transaction' ] : [],
			], $this, $order, $amount );
			$result = $this->gateway->mode( sswps_order_mode( $order ) )->refunds->create( $args );
			Utils\Misc::add_balance_transaction_to_order( $result->charge, $order, true );

			/**
			 * @since 1.0.0
			 */
			do_action( 'sswps/process_refund_success', $order );

			return true;
		} catch ( Exception $e ) {
			return new WP_Error( 'refund-error', $e->getMessage() );
		}
	}

	/**
	 * @since 1.0.0
	 *
	 * @param Gateways\Abstract_Gateway $payment_method
	 *
	 * @param WC_Order                  $order
	 */
	public function process_zero_total_order( $order, $payment_method ) {
		$payment_method->save_zero_total_meta( $order );
		if ( 'capture' === $payment_method->get_option( 'charge_type' ) ) {
			$order->payment_complete();
		} else {
			$order_status = $payment_method->get_option( 'order_status' );
			$order->update_status( apply_filters( 'sswps/authorized_order_status', 'default' === $order_status ? 'on-hold' : $order_status, $order, $payment_method ) );
		}
		WC()->cart->empty_cart();
		$this->destroy_session_data();

		return [
			'result'   => 'success',
			'redirect' => $payment_method->get_return_url( $order ),
		];
	}

	/**
	 * Return a failed order response.
	 *
	 * @return array
	 */
	public function order_error() {
		sswps_set_checkout_error();

		return [ 'result' => 'failure' ];
	}

	/**
	 *
	 * @param array    $args
	 * @param WC_Order $order
	 */
	public function add_general_order_args( &$args, $order ) {
		$this->add_order_amount( $args, $order );
		$this->add_order_currency( $args, $order );
		$this->add_order_description( $args, $order );
		$this->add_order_shipping_address( $args, $order );
		$this->add_order_metadata( $args, $order );
		$this->add_order_payment_method( $args, $order );
	}

	/**
	 *
	 * @param array    $args
	 * @param WC_Order $order
	 */
	public function add_order_metadata( &$args, $order ) {
		$meta_data  = [
			'gateway_id' => $order->get_payment_method(),
			'order_id'   => $order->get_id(),
			'user_id'    => $order->get_user_id(),
			'ip_address' => $order->get_customer_ip_address(),
			'user_agent' => Request::get_sanitized_server_var( 'HTTP_USER_AGENT', 'unavailable' ),
			'partner'    => 'SimpleSecureWP',
			'created'    => time(),
		];
		$webhook_id = App::get( Settings\API::class )->get_option( 'webhook_id_' . sswps_mode() );
		if ( $webhook_id ) {
			$meta_data['webhook_id'] = $webhook_id;
		}
		if ( has_action( 'woocommerce_order_number' ) ) {
			$meta_data['order_number'] = $order->get_order_number();
		}
		$length = count( $meta_data );

		foreach ( $order->get_items( 'line_item' ) as $item ) {
			// Stripe limits metadata keys to 50 entries.
			if ( $length < 50 ) {
				/**
				 *
				 * @var WC_Order_Item_Product $item
				 */
				$key   = 'product_' . $item->get_product_id();
				$value = sprintf( '%s x %s', $item->get_name(), $item->get_quantity() );
				// Stripe limits key names to 40 chars and values to 500 chars
				if ( strlen( $key ) <= 40 && strlen( $value ) <= 500 ) {
					$meta_data[ $key ] = $value;
					$length++;
				}
			}
		}
		$args['metadata'] = apply_filters( 'sswps/order_meta_data', $meta_data, $order );
	}

	/**
	 *
	 * @param array    $args
	 * @param WC_Order $order
	 */
	public function add_order_description( &$args, $order ) {
		/* translators: 1: Order number, 2: site name. */
		$args['description'] = sprintf( __( 'Order %1$s from %2$s', 'simple-secure-stripe' ), $order->get_order_number(), get_bloginfo( 'name' ) );
	}

	/**
	 *
	 * @param array    $args
	 * @param WC_Order $order
	 * @param float    $amount
	 */
	public function add_order_amount( &$args, $order, $amount = null ) {
		$args['amount'] = Utils\Currency::add_number_precision( $amount ? $amount : $order->get_total( 'raw' ), $order->get_currency() );
	}

	/**
	 *
	 * @param array    $args
	 * @param WC_Order $order
	 */
	public function add_order_currency( &$args, $order ) {
		$args['currency'] = $order->get_currency();
	}

	/**
	 *
	 * @param array    $args
	 * @param WC_Order $order
	 */
	public function add_order_shipping_address( &$args, $order ) {
		if ( sswps_order_has_shipping_address( $order ) ) {
			$args['shipping'] = [
				'address' => [
					'city'        => $order->get_shipping_city(),
					'country'     => $order->get_shipping_country(),
					'line1'       => $order->get_shipping_address_1(),
					'line2'       => $order->get_shipping_address_2(),
					'postal_code' => $order->get_shipping_postcode(),
					'state'       => $order->get_shipping_state(),
				],
				'name'    => $this->get_name_from_order( $order, 'shipping' ),
			];
		} else {
			$args['shipping'] = [];
		}
	}

	/**
	 *
	 * @param WC_Order $order
	 * @param string   $type
	 *
	 * @return string
	 */
	public function get_name_from_order( $order, $type ) {
		if ( $type === 'billing' ) {
			return sprintf( '%s %s', $order->get_billing_first_name(), $order->get_billing_last_name() );
		} else {
			return sprintf( '%s %s', $order->get_shipping_first_name(), $order->get_shipping_last_name() );
		}
	}

	/**
	 * @since 1.0.0
	 *
	 * @param WP_Error $error
	 * @param WC_Order $order
	 */
	public function add_payment_failed_note( $order, $error ) {
		/* translators: %s: error reason. */
		$note = sprintf( __( 'Error processing payment. Reason: %s', 'simple-secure-stripe' ), $error->get_error_message() );

		/**
		 * @param string                    $note
		 * @param WP_Error                  $error
		 * @param Gateways\Abstract_Gateway $payment_method
		 *
		 */
		$note = apply_filters( 'sswps/order_failed_note', $note, $error, $this->payment_method );
		$order->update_status( 'failed', $note );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param $payment_method
	 *
	 */
	public function set_payment_method( $payment_method ) {
		$this->payment_method = $payment_method;
	}

	/**
	 * @since 1.0.0
	 */
	protected function get_payment_method_charge_type() {
		return $this->payment_method->get_option( 'charge_type' ) === 'capture' ? Constants::AUTOMATIC : Constants::MANUAL;
	}

	public function destroy_session_data() {}

}
