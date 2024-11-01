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
class FPX extends Abstract_Local_Payment {

	use Payment\Traits\Local_Intent;

	protected string $payment_method_type = 'fpx';

	public function __construct() {
		$this->local_payment_type = 'fpx';
		$this->currencies         = [ 'MYR' ];
		$this->countries          = [ 'MY' ];
		$this->id                 = 'sswps_fpx';
		$this->tab_title          = __( 'FPX', 'simple-secure-stripe' );
		$this->method_title       = __( 'FPX', 'simple-secure-stripe' );
		$this->method_description = __( 'FPX gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/fpx.svg' );
		parent::__construct();
	}

	public function get_element_params() {
		$params                      = parent::get_element_params();
		$params['accountHolderType'] = 'individual';

		return $params;
	}
}
