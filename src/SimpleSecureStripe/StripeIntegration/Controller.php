<?php
namespace SimpleSecureWP\SimpleSecureStripe\StripeIntegration;

use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;
use WC_Order;

class Controller extends Abstract_Controller {
	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( Client::class, Client::class );

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		add_filter( 'woocommerce_order_get_payment_method', [ $this, 'convert_payment_method' ], 10, 2 );
		add_filter( 'woocommerce_subscription_get_payment_method', [ $this, 'convert_payment_method' ], 10, 2 );
	}

	/**
	 *
	 * @param string   $payment_method
	 * @param WC_Order $order
	 */
	public function convert_payment_method( $payment_method, $order ) {
		if ( $payment_method !== 'stripe' ) {
			return $payment_method;
		}

		// Another Stripe plugin is active, don't convert $payment_method as that could affect
		// checkout functionality.
		if ( did_action( 'woocommerce_checkout_order_processed' ) ) {
			return $payment_method;
		}

		return 'sswps_cc';
	}
}