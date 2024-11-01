<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;

use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripePayment;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Features\Installments\Installments;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

class CreditCardPayment extends AbstractStripePayment {

	protected $name = 'sswps_cc';

	/**
	 * @var Installments
	 */
	private $installments;

	public function get_payment_method_script_handles() {
		$this->assets_api->register_script( 'sswps-block-credit-card', 'dist/sswps-credit-card.js' );

		return [ 'sswps-block-credit-card' ];
	}

	public function get_payment_method_data() {
		$assets_url = $this->assets_api->get_asset_url( '../../src/assets/img/cards/' );

		return wp_parse_args( [
			'cardOptions'            => $this->payment_method->get_card_form_options(),
			'customFieldOptions'     => $this->payment_method->get_card_custom_field_options(),
			'customFormActive'       => $this->payment_method->is_custom_form_active(),
			'isPaymentElement'       => $this->payment_method->is_payment_element_active(),
			'elementOptions'         => $this->payment_method->get_element_options(),
			'customForm'             => $this->payment_method->get_option( 'custom_form' ),
			'customFormLabels'       => wp_list_pluck( sswps_get_custom_forms(), 'label' ),
			'postalCodeEnabled'      => $this->payment_method->postal_enabled(),
			'saveCardEnabled'        => $this->payment_method->is_active( 'save_card_enabled' ),
			'savePaymentMethodLabel' => __( 'Save Card', 'simple-secure-stripe' ),
			'installmentsActive'     => $this->installments->is_available(),
			'cards'                  => [
				'visa'       => $assets_url . 'visa.svg',
				'amex'       => $assets_url . 'amex.svg',
				'mastercard' => $assets_url . 'mastercard.svg',
				'discover'   => $assets_url . 'discover.svg',
				'diners'     => $assets_url . 'diners.svg',
				'jcb'        => $assets_url . 'jcb.svg',
				'maestro'    => $assets_url . 'maestro.svg',
				'unionpay'   => $assets_url . 'china_union_pay.svg',
				'unknown'    => $this->payment_method->get_custom_form()['cardBrand'],
			],
		], parent::get_payment_method_data() );
	}

	protected function get_payment_method_icon() {
		$icons = [];
		$cards = $this->get_setting( 'cards', [] );
		$cards = ! \is_array( $cards ) ? [] : $cards;
		foreach ( $cards as $id ) {
			$icons[] = [
				'id'  => $id,
				'alt' => '',
				'src' => App::get( Plugin::class )->assets_url( "img/cards/{$id}.svg" ),
			];
		}

		return $icons;
	}

	/**
	 * @param \SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Assets\Api $style_api
	 */
	public function enqueue_payment_method_styles( $style_api ) {
		if ( $this->payment_method->is_custom_form_active() ) {
			$form = $this->payment_method->get_option( 'custom_form' );
			if ( \in_array( $form, [ 'bootstrap', 'simple' ] ) ) {
				wp_enqueue_style( 'sswps-credit-card-style', $style_api->get_asset_url( "credit-card/{$form}.css" ) );
				wp_style_add_data( 'sswps-credit-card-style', 'rtl', 'replace' );
			}
		}
	}

	public function set_installments( Installments $installments ) {
		$this->installments = $installments;
	}

	public function is_payment_element_active() {
		return $this->get_setting( 'form_type' ) === 'payment';
	}

}