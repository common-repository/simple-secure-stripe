<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\CheckoutWC;

use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Gateways;

class AssetsController {

	public function __construct() {
		$this->initialize();
	}

	private function initialize() {
		add_action( 'cfw_payment_request_buttons', [ $this, 'enqueue_styles' ] );
	}

	public function enqueue_styles() {
		foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
			if ( $gateway instanceof Gateways\Abstract_Gateway ) {
				if ( $gateway->supports( 'sswps_banner_checkout' ) && $gateway->banner_checkout_enabled() ) {
					wp_enqueue_style( 'sswps-checkoutwc-style', SIMPLESECUREWP_STRIPE_ASSETS . 'integrations/CheckoutWC/checkoutwc-styles.css', [], Plugin::VERSION );
					break;
				}
			}
		}
	}

}