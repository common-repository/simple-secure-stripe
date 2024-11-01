<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets\Assets;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

/**
 *
 * @package Stripe/Gateways
 * @author Simple & Secure WP
 *
 */
class WeChat extends Abstract_Local_Payment {

	use Payment\Traits\Local_Charge;

	public function __construct() {
		$this->local_payment_type = 'wechat';
		$this->currencies         = [ 'AUD', 'CAD', 'CHF', 'CNY', 'DKK', 'EUR', 'GBP', 'HKD', 'JPY', 'NOK', 'SEK', 'SGD', 'USD' ];
		$this->id                 = 'sswps_wechat';
		$this->tab_title          = __( 'WeChat', 'simple-secure-stripe' );
		$this->template_name      = 'local-payment.php';
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'WeChat', 'simple-secure-stripe' );
		$this->method_description = __( 'WeChat gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/wechat.svg' );
		parent::__construct();
	}

	public function init_form_fields() {
		parent::init_form_fields();
		$this->form_fields['allowed_countries']['default'] = 'all';
	}

	public function get_local_payment_settings() {
		return array_merge( parent::get_local_payment_settings(), [
			'qr_size' => [
				'type'              => 'input',
				'title'             => __( 'QRCode Size', 'simple-secure-stripe' ),
				'default'           => '128',
				'desc_tip'          => true,
				'description'       => __( 'This option controls the width and height in pixels of the QRCode.', 'simple-secure-stripe' ),
				'sanitize_callback' => function( $value ) {
					if ( ! is_numeric( $value ) ) {
						$value = 128;
					}

					return $value;
				},
			],
		] );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Local_Payment::get_source_redirect_url()
	 */
	public function get_source_redirect_url( $source, $order ) {
		if ( sswps_mode() == 'live' ) {
			return sprintf(
				'#qrcode=%s',
				base64_encode(
					wp_json_encode(
						[
							'code'     => $source->wechat->offsetGet( 'qr_code_url' ),
							'redirect' => $this->get_return_url( $order ),
						]
					)
				)
			);
		}
		// test code
		// 'code' => 'weixin:\/\/wxpay\/bizpayurl?pr=tMih4Jo'

		// in test mode just return the redirect url
		return $source->wechat->offsetGet( 'qr_code_url' );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Local_Payment::get_localized_params()
	 */
	public function get_localized_params() {
		$data               = parent::get_localized_params();
		$data['qr_script']  = sprintf( App::get( Assets::class )->assets_url( 'js/frontend/qrcode.js?ver=%s' ), Plugin::VERSION );
		$data['qr_message'] = __( 'Scan the QR code using your WeChat app. Once scanned click the Place Order button.', 'simple-secure-stripe' );
		$data['qr_size']    = $this->get_option( 'qr_size', 128 );

		return $data;
	}

}
