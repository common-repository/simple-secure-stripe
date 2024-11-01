<?php

namespace SimpleSecureWP\SimpleSecureStripe\Payment;

use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WC_Order;

/**
 *
 * @since   1.0.0
 *
 * @author Simple & Secure WP
 * @package Stripe/Classes
 */
class Charge extends Abstract_Payment {

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::process_payment()
	 */
	public function process_payment( $order ) {
		if ( $this->payment_method->should_save_payment_method( $order ) || ( $this->payment_method->supports( 'add_payment_method' ) && apply_filters( 'sswps/force_save_payment_method', false, $order, $this->payment_method ) ) ) {
			$result = $this->payment_method->save_payment_method( $this->payment_method->get_new_source_token(), $order );
			if ( is_wp_error( $result ) ) {
				$this->add_payment_failed_note( $order, $result );

				return $result;
			}
		}

		$args = $this->get_order_charge_args( $args, $order );

		$charge = $this->gateway->mode( sswps_order_mode( $order ) )->charges->create( $args );

		sswps_log_info( 'Stripe charge: ' . print_r( $charge, true ) );

		return (object) [
			'complete_payment' => true,
			'charge'           => $charge,
		];
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::capture_charge()
	 */
	public function capture_charge( $amount, $order ) {
		return $this->gateway->mode( sswps_order_mode( $order ) )->charges->capture(
			$order->get_transaction_id(),
			[
				'amount' => Utils\Currency::add_number_precision(
					$amount,
					$order->get_currency()
				),
			]
		);
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::void_charge()
	 */
	public function void_charge( $order ) {
		return $this->gateway->mode( sswps_order_mode( $order ) )->refunds->create( [ 'charge' => $order->get_transaction_id() ] );
	}

	public function scheduled_subscription_payment( $amount, $order ) {
		$this->get_order_charge_args( $args, $order );

		$args['source'] = $this->payment_method->get_order_meta_data( Constants::PAYMENT_METHOD_TOKEN, $order );

		if ( ( $customer_id = $this->payment_method->get_order_meta_data( Constants::CUSTOMER_ID, $order ) ) ) {
			$args['customer'] = $customer_id;
		} elseif ( ( $customer_id = sswps_get_customer_id( $order->get_customer_id(), sswps_order_mode( $order ) ) ) ) {
			$args['customer'] = $customer_id;
		}

		$charge = $this->gateway->mode( sswps_order_mode( $order ) )->charges->create( $args );

		return (object) [
			'complete_payment' => true,
			'charge'           => $charge,
		];
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::process_pre_order_payment()
	 */
	public function process_pre_order_payment( $order ) {
		$this->get_order_charge_args( $args, $order );

		$args['source'] = $order->get_meta( Constants::PAYMENT_METHOD_TOKEN );

		if ( ( $customer_id = $order->get_meta( Constants::CUSTOMER_ID ) ) ) {
			$args['customer'] = $customer_id;
		} elseif ( ( $customer_id = sswps_get_customer_id( $order->get_customer_id(), sswps_order_mode( $order ) ) ) ) {
			$args['customer'] = $customer_id;
		}

		$charge = $this->gateway->mode( sswps_order_mode( $order ) )->charges->create( $args );

		return (object) [
			'complete_payment' => true,
			'charge'           => $charge,
		];
	}

	/**
	 *
	 * @param array    $args
	 * @param WC_Order $order
	 */
	public function get_order_charge_args( &$args, $order ) {
		$this->add_general_order_args( $args, $order );

		if ( get_option( 'woocommerce_sswps_email_receipt', 'no' ) === 'yes' && ( $email = $order->get_billing_email() ) ) {
			$args['receipt_email'] = $email;
		}
		$args['capture'] = $this->payment_method->get_option( 'charge_type' ) === 'capture';

		$customer_id = sswps_get_customer_id( $order->get_user_id() );

		// only add customer ID if user is paying with a saved payment method
		if ( $customer_id && $this->payment_method->use_saved_source() ) {
			$args['customer'] = $customer_id;
		}

		$this->payment_method->add_stripe_order_args( $args, $order );

		return apply_filters( 'sswps/charge_order_args', $args, $order, $this->payment_method->id );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::get_payment_method_from_charge()
	 */
	public function get_payment_method_from_charge( $charge ) {
		return $charge->source->id;
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::add_order_payment_method()
	 */
	public function add_order_payment_method( &$args, $order ) {
		$args['source'] = $this->payment_method->get_payment_method_from_request();
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Payment::can_void_charge()
	 */
	public function can_void_order( $order ) {
		return $order->get_transaction_id();
	}

}
