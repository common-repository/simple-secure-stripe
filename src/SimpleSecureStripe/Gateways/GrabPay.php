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
class GrabPay extends Abstract_Local_Payment {

	protected string $payment_method_type = 'grabpay';

	use Payment\Traits\Local_Intent;

	public function __construct() {
		$this->local_payment_type = 'grabpay';
		$this->currencies         = [ 'SGD', 'MYR' ];
		$this->countries          = [ 'MY', 'SG' ];
		$this->id                 = 'sswps_grabpay';
		$this->tab_title          = __( 'GrabPay', 'simple-secure-stripe' );
		$this->method_title       = __( 'GrabPay', 'simple-secure-stripe' );
		$this->method_description = __( 'GrabPay gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/grabpay.svg' );
		parent::__construct();
	}
}
