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
class P24 extends Abstract_Local_Payment {

	use Payment\Traits\Local_Intent;

	protected string $payment_method_type = 'p24';

	public function __construct() {
		$this->local_payment_type = 'p24';
		$this->currencies         = [ 'EUR', 'PLN' ];
		$this->countries          = [ 'PL' ];
		$this->id                 = 'sswps_p24';
		$this->tab_title          = __( 'Przelewy24', 'simple-secure-stripe' );
		$this->template_name      = 'local-payment.php';
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'Przelewy24', 'simple-secure-stripe' );
		$this->method_description = __( 'P24 gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/p24.svg' );
		parent::__construct();
	}
}
