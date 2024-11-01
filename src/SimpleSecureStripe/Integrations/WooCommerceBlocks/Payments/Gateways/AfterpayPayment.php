<?php


namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripeLocalPayment;
use SimpleSecureWP\SimpleSecureStripe\Utils;

class AfterpayPayment extends AbstractStripeLocalPayment {

	protected $name = 'sswps_afterpay';

	public function get_payment_method_data() {
		$data = wp_parse_args( [
			'requiredParams' => $this->payment_method->get_required_parameters(),
			'msgOptions'     => $this->payment_method->get_afterpay_message_options(),
			'cartTotal'      => WC()->cart ? Utils\Currency::add_number_precision( WC()->cart->get_total( 'raw' ) ) : 0,
			'currency'       => get_woocommerce_currency(),
			'accountCountry' => $this->get_account_country(),
			'hideIneligible' => wc_string_to_bool( $this->get_setting( 'hide_ineligible', 'no' ) ),
		], parent::get_payment_method_data() );
		if ( ! in_array( $data['locale'], $this->payment_method->get_supported_locales() ) ) {
			$data['locale'] = 'auto';
		}

		return $data;
	}

	/**
	 * @since 1.0.0
	 * @return mixed|string
	 */
	private function get_account_country() {
		$mode    = sswps_mode();
		$country = App::get( Settings\Account::class )->get_account_country( $mode );
		if ( empty( $country ) && $mode === 'test' ) {
			$country = wc_get_base_location()['country'];
		}

		return $country;
	}

	public function get_supported_locales() {
		return apply_filters( 'sswps/afterpay_supported_locales', [ 'en-US', 'en-CA', 'en-AU', 'en-NZ', 'en-GB', 'fr-FR', 'it-IT', 'es-ES' ] );
	}

}