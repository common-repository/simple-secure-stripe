<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommercePreOrders;

use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommercePreOrders\Controllers\PaymentIntent;

class Package {

	public function __construct() {
		add_action( 'woocommerce_init', [ $this, 'initialize' ] );
	}

	public function initialize() {
		if ( $this->is_enabled() ) {
			new PaymentIntent( new FrontendRequests() );
		}
	}

	private function is_enabled() : bool {
		return sswps_pre_orders_active();
	}

}