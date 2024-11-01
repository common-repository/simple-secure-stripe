<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

/**
 *
 * @package Stripe/Gateways
 * @author Simple & Secure WP
 *
 */
class Apple_Pay extends Abstract_Gateway {

	use Payment\Traits\Intent;

	protected string $payment_method_type = 'card';

	public function __construct() {
		$this->id                 = 'sswps_applepay';
		$this->tab_title          = __( 'Apple Pay', 'simple-secure-stripe' );
		$this->template_name      = 'applepay.php';
		$this->token_type         = 'Stripe_ApplePay';
		$this->method_title       = __( 'Apple Pay', 'simple-secure-stripe' );
		$this->method_description = __( 'Apple Pay gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->has_digital_wallet = true;
		parent::__construct();
		$this->icon = App::get( Plugin::class )->assets_url( 'img/applepay.svg' );
	}

	public function init_supports() {
		parent::init_supports();
		$this->supports[] = 'sswps_cart_checkout';
		$this->supports[] = 'sswps_product_checkout';
		$this->supports[] = 'sswps_banner_checkout';
		$this->supports[] = 'sswps_mini_cart_checkout';
	}

	/**
	 * @inheritDoc
	 */
	public function register_assets() {
		parent::register_assets();

		Assets\Asset::register( 'sswps-applepay-checkout', 'frontend/applepay-checkout.js' )
			->add_to_group( 'sswps-local-payment' )
			->set_dependencies( [
				'sswps-script',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_applepay_checkout_params',
				$this->get_localized_params()
			)
			->set_condition( [ $this, 'is_available' ] );

		Assets\Asset::register( 'sswps-applepay-cart', 'frontend/applepay-cart.js' )
			->add_to_group( 'sswps-local-payment-cart' )
			->set_dependencies( [
				'sswps-script',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_applepay_cart_params',
				$this->get_localized_params()
			)
			->set_condition( [ $this, 'is_available' ] );

		Assets\Asset::register( 'sswps-applepay-product', 'frontend/applepay-product.js' )
			->add_to_group( 'sswps-local-payment-product' )
			->set_dependencies( [
				'sswps-script',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_applepay_product_params',
				$this->get_localized_params()
			)
			->set_condition( [ $this, 'is_available' ] );
	}

	public function get_localized_params() {
		return array_merge_recursive(
			parent::get_localized_params(),
			[
				'messages' => [
					'invalid_amount' => __( 'Please update you product quantity before using Apple Pay.', 'simple-secure-stripe' ),
					'choose_product' => __( 'Please select a product option before updating quantity.', 'simple-secure-stripe' ),
				],
				'button'   => sswps_get_template_html(
					'applepay-button.php',
					[
						'style'       => $this->get_option( 'button_style' ),
						'type'        => $this->get_button_type(),
						'button_type' => $this->get_applepay_button_style_type(),
					]
				),
			]
		);
	}

	/**
	 * Returns the Apple Pay button type based on the current page.
	 *
	 * @return string
	 */
	protected function get_button_type() {
		if ( is_checkout() ) {
			return $this->get_option( 'button_type_checkout' );
		}
		if ( is_cart() ) {
			return $this->get_option( 'button_type_cart' );
		}
		if ( is_product() ) {
			return $this->get_option( 'button_type_product' );
		}

		return $this->get_option( 'button_type_product' );
	}

	private function get_applepay_button_style_type() {
		$style = $this->get_option( 'button_style' );
		switch ( $style ) {
			case 'apple-pay-button-white':
				return 'white';
			case 'apple-pay-button-white-with-line':
				return 'white-outline';
			default:
				return 'black';
		}
	}
}
