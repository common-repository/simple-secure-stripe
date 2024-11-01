<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;

class Controller extends Abstract_Controller {
	/**
	 * REST controllers.
	 */
	private array $gateways = [
		ACH::class,
		Affirm::class,
		Afterpay::class,
		Alipay::class,
		Apple_Pay::class,
		Bancontact::class,
		Becs::class,
		Blik::class,
		Boleto::class,
		CC::class,
		EPS::class,
		FPX::class,
		Giropay::class,
		Google_Pay::class,
		GrabPay::class,
		Ideal::class,
		Klarna::class,
		Konbini::class,
		Multibanco::class,
		OXXO::class,
		P24::class,
		Payment_Request::class,
		PayNow::class,
		Sepa::class,
		Sofort::class,
		WeChat::class,
	];

	/**
	 * @inheritDoc
	 */
	public function register() {
		foreach ( $this->gateways as $key => $class ) {
			$this->container->singleton( $class, $class );
		}

		$this->hooks();
	}

	/**
	 * Gets the gateway classes.
	 */
	public function get_gateways() {
		/**
		 * Filters the list of gateways.
		 *
		 * @param array $gateways The list of gateways.
		 */
		return apply_filters( 'sswps/get_gateways', $this->gateways );
	}

	/**
	 * Bind hooks.
	 */
	public function hooks() {
	}
}