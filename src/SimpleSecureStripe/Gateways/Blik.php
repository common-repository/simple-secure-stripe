<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;

/**
 *
 * @package Stripe/Gateways
 * @author Simple & Secure WP
 *
 */
class Blik extends Abstract_Local_Payment {

	protected string $payment_method_type = 'blik';

	use Payment\Traits\Local_Intent;

	public function __construct() {
		$this->local_payment_type = 'blik';
		$this->currencies         = [ 'PLN' ];
		$this->countries          = [ 'PL' ];
		$this->id                 = 'sswps_blik';
		$this->tab_title          = __( 'BLIK', 'simple-secure-stripe' );
		$this->method_title       = __( 'BLIK', 'simple-secure-stripe' );
		$this->method_description = __( 'BLIK gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/blik.svg' );
		parent::__construct();
		$this->template_name = 'blik.php';
	}

	public function validate_fields() {
		foreach ( range( 0, 5 ) as $idx ) {
			$code = Request::get_sanitized_var( 'blik_code_' . $idx, null );
			if ( ! $code || strlen( $code ) === 0 ) {
				wc_add_notice( __( 'Please provide your 6 digit BLIK code.', 'simple-secure-stripe' ), 'error' );

				return false;
			}
		}

		return true;
	}

	public function get_payment_intent_confirmation_args( $intent, $order ) {
		$code = '';
		foreach ( range( 0, 5 ) as $idx ) {
			$code .= wc_clean( Request::get_sanitized_var( 'blik_code_' . $idx ) );
		}

		return [
			'payment_method_options' => [
				'blik' => [
					'code' => $code,
				],
			],
		];
	}

}
