<?php
namespace SimpleSecureWP\SimpleSecureStripe\Payment\Traits;

use SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentIntent;
use WC_Order;

/**
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Trait
 *
 */
trait Local_Intent {

	use Intent;

	/**
	 *
	 * @param PaymentIntent $intent
	 * @param WC_Order      $order
	 * @param string        $type
	 */
	public function get_payment_intent_checkout_url( $intent, $order, $type = 'payment_intent' ) {
		// rand is used to generate some random entropy so that window hash events are triggered.
		return sprintf(
			'#response=%s',
			rawurlencode(
				base64_encode(
					wp_json_encode( $this->get_payment_intent_checkout_params( $intent, $order, $type ) )
				)
			)
		);
	}

	protected function get_payment_intent_checkout_params( $intent, $order, $type ) {
		return [
			'type'               => $type,
			'client_secret'      => $intent->client_secret,
			'gateway_id'         => $this->id,
			'order_id'           => $order->get_id(),
			'order_key'          => $order->get_order_key(),
			'return_url'         => $this->get_local_payment_return_url( $order ),
			'order_received_url' => $this->get_return_url( $order ),
			'entropy'            => rand(
				0,
				999999
			),
		];
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	public function get_confirmation_method( $order = null ) {
		return 'automatic';
	}

	/**
	 * @param PaymentIntent $intent
	 * @param WC_Order      $order
	 */
	public function get_payment_intent_confirmation_args( $intent, $order ) {
		return [
			'return_url' => $this->get_local_payment_return_url( $order ),
		];
	}

}