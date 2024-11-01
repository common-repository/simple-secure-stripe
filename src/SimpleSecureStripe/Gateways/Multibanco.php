<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

/**
 *
 * @package Stripe/Gateways
 * @author Simple & Secure WP
 *
 */
class Multibanco extends Abstract_Local_Payment {

	use Payment\Traits\Local_Charge;

	public function __construct() {
		$this->local_payment_type = 'multibanco';
		$this->currencies         = [ 'EUR' ];
		$this->countries          = [ 'PT' ];
		$this->id                 = 'sswps_multibanco';
		$this->tab_title          = __( 'Multibanco', 'simple-secure-stripe' );
		$this->template_name      = 'local-payment.php';
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'Multibanco', 'simple-secure-stripe' );
		$this->method_description = __( 'Multibanco gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/multibanco.svg' );
		parent::__construct();
	}
}
