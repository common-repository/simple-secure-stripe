<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit\Checkout\Compatibility;

use SimpleSecureWP\SimpleSecureStripe\Gateways;

abstract class AbstractCompatibility {

	protected $id;

	/**
	 * @var Gateways\Abstract_Gateway
	 */
	protected $payment_gateway;

	public function __construct( $payment_gateway ) {
		$this->payment_gateway = $payment_gateway;
		$this->initialize();
	}

	protected function initialize() {
	}

	public function get_payment_gateway() {
		return $this->payment_gateway;
	}

	public function is_enabled() {
		return wc_string_to_bool( $this->payment_gateway->get_option( 'enabled' ) );
	}

	/**
	 * @return bool
	 */
	public function is_express_enabled() {
		return $this->payment_gateway->banner_checkout_enabled();
	}

	public function render_express_button() {
	}

}