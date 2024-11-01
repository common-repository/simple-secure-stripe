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
class Bancontact extends Abstract_Local_Payment {

	use Payment\Traits\Local_Intent;

	protected string $payment_method_type = 'bancontact';

	public function __construct() {
		$this->local_payment_type = 'bancontact';
		$this->currencies         = [ 'EUR' ];
		$this->countries          = [ 'BE' ];
		$this->id                 = 'sswps_bancontact';
		$this->tab_title          = __( 'Bancontact', 'simple-secure-stripe' );
		$this->method_title       = __( 'Bancontact', 'simple-secure-stripe' );
		$this->method_description = __( 'Bancontact gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/bancontact.svg' );
		parent::__construct();
	}

}
