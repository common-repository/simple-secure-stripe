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
class Giropay extends Abstract_Local_Payment {

	use Payment\Traits\Local_Intent;

	protected string $payment_method_type = 'giropay';

	public function __construct() {
		$this->local_payment_type = 'giropay';
		$this->currencies         = [ 'EUR' ];
		$this->countries          = [ 'DE' ];
		$this->id                 = 'sswps_giropay';
		$this->tab_title          = __( 'Giropay', 'simple-secure-stripe' );
		$this->template_name      = 'local-payment.php';
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'Giropay', 'simple-secure-stripe' );
		$this->method_description = __( 'Giropay gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/giropay.svg' );
		parent::__construct();
	}

}
