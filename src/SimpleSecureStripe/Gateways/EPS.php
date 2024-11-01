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
class EPS extends Abstract_Local_Payment {

	use Payment\Traits\Local_Intent;

	protected string $payment_method_type = 'eps';

	public function __construct() {
		$this->local_payment_type = 'eps';
		$this->currencies         = [ 'EUR' ];
		$this->countries          = [ 'AT' ];
		$this->id                 = 'sswps_eps';
		$this->tab_title          = __( 'EPS', 'simple-secure-stripe' );
		$this->template_name      = 'local-payment.php';
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'EPS', 'simple-secure-stripe' );
		$this->method_description = __( 'EPS gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/eps.svg' );
		parent::__construct();
	}

}
