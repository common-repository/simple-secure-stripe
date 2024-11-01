<?php


namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;

use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripePayment;

/**
 * Class PaymentRequest
 *
 * @package SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments
 */
class PaymentRequest extends AbstractStripePayment {

	protected $name = 'sswps_payment_request';

	public function get_payment_method_script_handles() {
		$this->assets_api->register_script( 'sswps-blocks-payment-request', 'dist/sswps-payment-request.js' );

		return [ 'sswps-blocks-payment-request' ];
	}

	public function get_payment_method_data() {
		return wp_parse_args( [
			'paymentRequestButton' => [
				'type'   => $this->payment_method->get_option( 'button_type' ),
				'theme'  => $this->payment_method->get_option( 'button_theme' ),
				'height' => $this->payment_method->get_button_height(),
			],
		], parent::get_payment_method_data() );
	}
}