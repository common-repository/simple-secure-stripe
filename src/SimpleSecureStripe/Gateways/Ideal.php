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
class Ideal extends Abstract_Local_Payment {

	protected string $payment_method_type = 'ideal';

	use Payment\Traits\Local_Intent;

	public function __construct() {
		$this->local_payment_type = 'ideal';
		$this->currencies         = [ 'EUR' ];
		$this->countries          = [ 'NL' ];
		$this->id                 = 'sswps_ideal';
		$this->tab_title          = __( 'iDEAL', 'simple-secure-stripe' );
		$this->method_title       = __( 'iDEAL', 'simple-secure-stripe' );
		$this->method_description = __( 'Ideal gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/ideal.svg' );
		parent::__construct();
	}
}
