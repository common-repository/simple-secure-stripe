<?php


namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;


use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripePayment;

class ApplePayPayment extends AbstractStripePayment {

	protected $name = 'sswps_applepay';

	public function get_payment_method_script_handles() {
		$this->assets_api->register_script( 'sswps-blocks-apple-pay', 'dist/sswps-applepay.js' );

		return [ 'sswps-blocks-apple-pay' ];
	}

	public function get_payment_method_data() {
		return wp_parse_args( [
			'buttonType'  => $this->payment_method->get_option( 'button_type_checkout' ),
			'buttonStyle' => $this->payment_method->get_option( 'button_style' ),
			'editorIcon'  => $this->assets_api->get_asset_url( 'src/assets/img/apple_pay_button_black.svg' ),
		], parent::get_payment_method_data() );
	}
}