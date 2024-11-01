<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

/**
 *
 * @package Stripe/Gateways
 * @author Simple & Secure WP
 *
 */
class Alipay extends Abstract_Local_Payment {

	use Payment\Traits\Local_Intent;

	protected string $payment_method_type = 'alipay';

	public function __construct() {
		$this->local_payment_type = 'alipay';
		$this->currencies         = [ 'AUD', 'CAD', 'EUR', 'GBP', 'HKD', 'JPY', 'SGD', 'USD', 'CNY', 'NZD', 'MYR' ];
		$this->id                 = 'sswps_alipay';
		$this->tab_title          = __( 'Alipay', 'simple-secure-stripe' );
		$this->template_name      = 'local-payment.php';
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'Alipay', 'simple-secure-stripe' );
		$this->method_description = __( 'Alipay gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/alipay.svg' );
		parent::__construct();
	}

	public function init_form_fields() {
		parent::init_form_fields();
		$this->form_fields['allowed_countries']['default'] = 'all';
	}

	/**
	 * @param string $currency
	 * @param string $billing_country
	 *
	 * @return bool
	 */
	public function validate_local_payment_available( $currency, $billing_country ) {
		$mode             = sswps_mode();
		$country          = App::get( Settings\Account::class )->get_account_country( $mode );
		$default_currency = App::get( Settings\Account::class )->get_account_currency( $mode );
		if ( empty( $country ) && $mode === 'test' ) {
			$country          = wc_get_base_location()['country'];
			$default_currency = $currency;
		}
		// https://stripe.com/docs/payments/alipay/accept-a-payment?platform=web#supported-currencies
		// Currency must be one of the allowed values
		if ( in_array( $currency, $this->currencies ) ) {
			// If CNY, it doesn't matter what the billing or default country is.
			if ( $currency === 'CNY' ) {
				return true;
			}

			// If merchant's country is DK, NO, SE, or CH, currency must be EUR.
			if ( in_array( $country, [ 'AT', 'BE', 'BG', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'NO', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'CH' ] ) ) {
				return $currency === 'EUR';
			} else {
				// For all other countries, Alipay is available if the currency matches the
				// Stripe account default currency
				return $currency === $default_currency;
			}
		}

		return false;
	}

	protected function get_payment_description() {
		return __(
			'Gateway will appear when store currency is CNY, or currency matches merchant\'s default Stripe currency. For merchants located in DK, NO, SE, & CH, currency must be EUR.',
			'simple-secure-stripe'
		);
	}

}

