<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Utils;

class AffirmPayment extends \SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripeLocalPayment {

	protected $name = 'sswps_affirm';

	public function get_payment_method_data() {
		$data = parent::get_payment_method_data();
		if ( WC()->cart ) {
			$currency = get_woocommerce_currency();
			$data     = array_merge( $data, [
				'cartTotals'     => [
					'value' => Utils\Currency::add_number_precision( (float) WC()->cart->get_total( 'raw' ), $currency ),
				],
				'currency'       => $currency,
				'requirements'   => $this->payment_gateway->get_payment_method_requirements(), // @phpstan-ignore-line - this is a valid method
				'accountCountry' => App::get( Settings\Account::class )->get_account_country( sswps_mode() ),
				'messageOptions' => [
					'logoColor' => $this->get_setting( "checkout_logo_color", 'primary' ),
					'fontColor' => $this->get_setting( "checkout_font_color", 'black' ),
					'fontSize'  => $this->get_setting( "checkout_font_size", '1em' ),
					'textAlign' => $this->get_setting( "checkout_text_align", 'start' ),
				],
			] );
		}

		return $data;
	}

}