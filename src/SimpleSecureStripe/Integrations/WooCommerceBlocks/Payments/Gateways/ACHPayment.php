<?php


namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;


use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripePayment;

class ACHPayment extends AbstractStripePayment {

	protected $name = 'sswps_ach';

	public function get_payment_method_script_handles() {
		$this->assets_api->register_script( 'sswps-blocks-ach', 'dist/sswps-ach.js' );

		return [ 'sswps-blocks-ach' ];
	}

	public function get_payment_method_icon() {
		return [
			'id'  => $this->get_name(),
			'alt' => 'ACH Payment',
			'src' => $this->payment_method->icon,
		];
	}

	public function get_payment_method_data() {
		return wp_parse_args( [
			'businessName' => $this->payment_method->get_option( 'business_name' ),
			'mandateText'  => $this->payment_method->get_mandate_text(),
		], parent::get_payment_method_data() );
	}

}