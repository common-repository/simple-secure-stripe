<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways\Link;

class LinkPaymentGateway extends \WC_Payment_Gateway {

	public $id = 'sswps_link_checkout';

	public function __construct() {
		$this->supports = [];
	}

	public function is_available() {
		return true;
	}


}