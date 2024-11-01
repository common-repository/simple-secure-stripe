<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripePayment;

class GooglePayPayment extends AbstractStripePayment {

	protected $name = 'sswps_googlepay';

	public function get_payment_method_script_handles() {
		$this->assets_api->register_external_script( 'sswps-gpay-external', 'https://pay.google.com/gp/p/js/pay.js', [], null );
		$this->assets_api->register_script( 'sswps-blocks-googlepay', 'dist/sswps-googlepay.js', [ 'sswps-gpay-external' ] );

		return [ 'sswps-blocks-googlepay' ];
	}

	public function get_payment_method_data() {
		return wp_parse_args( [
			'icon'              => $this->get_payment_method_icon(),
			'editorIcons'       => [
				'long'  => $this->assets_api->get_asset_url( 'src/assets/img/gpay_button_buy_black.svg' ),
				'short' => $this->assets_api->get_asset_url( 'src/assets/img/gpay_button_black.svg' ),
			],
			'merchantId'        => $this->get_merchant_id(),
			'merchantName'      => $this->payment_method->get_option( 'merchant_name' ),
			'totalPriceLabel'   => __( 'Total', 'simple-secure-stripe' ),
			'buttonStyle'       => [
				'buttonColor'    => $this->payment_method->get_option( 'button_color' ),
				'buttonType'     => $this->payment_method->get_option( 'button_style' ),
				'buttonSizeMode' => 'fill',
				'buttonLocale'   => $this->payment_method->get_payment_button_locale(),
			],
			'buttonShape'       => $this->payment_method->get_option( 'button_shape', 'rect' ),
			'environment'       => $this->get_google_pay_environment(),
			'processingCountry' => WC()->countries ? WC()->countries->get_base_country() : wc_get_base_location()['country'],
		], parent::get_payment_method_data() );
	}

	protected function get_payment_method_icon() {
		$icon = $this->payment_method->get_option( 'icon' );

		return [
			'id'  => "{$this->name}_icon",
			'alt' => '',
			'src' => App::get( Plugin::class )->assets_url( "img/{$icon}.svg" ),
		];
	}

	private function get_merchant_id() {
		return 'test' === sswps_mode() ? '' : $this->payment_method->get_option( 'merchant_id' );
	}

	private function get_google_pay_environment() {
		return sswps_mode() === 'test' ? 'TEST' : 'PRODUCTION';
	}

}