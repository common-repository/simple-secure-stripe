<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommercePreOrders\Controllers;

use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommercePreOrders\FrontendRequests;

class PaymentIntent {

	private $request;

	public function __construct( FrontendRequests $request ) {
		$this->request = $request;
		$this->initialize();
	}

	private function initialize() {
		add_filter( 'sswps/create_setup_intent', [ $this, 'maybe_create_setup_intent' ] );
	}

	public function maybe_create_setup_intent( $bool ) {
		if ( $this->request->is_checkout_with_preorder_requires_tokenization() ) {
			return true;
		}

		if ( $this->request->is_order_pay_with_preorder_requires_tokenization() ) {
			return true;
		}

		return $bool;
	}

}