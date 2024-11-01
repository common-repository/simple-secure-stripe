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
class PayNow extends Abstract_Local_Payment {

	use Payment\Traits\Local_Intent;

	protected string $payment_method_type = 'paynow';

	public function __construct() {
		$this->local_payment_type = 'paynow';
		$this->currencies         = [ 'SGD' ];
		$this->countries          = [ 'SG' ];
		$this->id                 = 'sswps_paynow';
		$this->tab_title          = __( 'PayNow', 'simple-secure-stripe' );
		$this->method_title       = __( 'PayNow', 'simple-secure-stripe' );
		$this->method_description = __( 'PayNow gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/paynow.svg' );
		parent::__construct();
	}

	public function get_local_payment_description() {
		$this->local_payment_description = sswps_get_template_html( 'checkout/paynow-instructions.php', [ 'button_text' => $this->order_button_text ] );

		return parent::get_local_payment_description();
	}


}
