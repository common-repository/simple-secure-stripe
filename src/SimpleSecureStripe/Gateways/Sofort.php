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
class Sofort extends Abstract_Local_Payment {

	use Payment\Traits\Local_Intent;

	protected string $payment_method_type = 'sofort';

	public function __construct() {
		$this->synchronous        = false;
		$this->local_payment_type = 'sofort';
		$this->currencies         = [ 'EUR' ];
		$this->countries          = $this->limited_countries = [ 'AT', 'BE', 'DE', 'ES', 'IT', 'NL' ];
		$this->id                 = 'sswps_sofort';
		$this->tab_title          = __( 'Sofort', 'simple-secure-stripe' );
		$this->template_name      = 'local-payment.php';
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'Sofort', 'simple-secure-stripe' );
		$this->method_description = __( 'Sofort gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/sofort.svg' );
		parent::__construct();
	}

}
