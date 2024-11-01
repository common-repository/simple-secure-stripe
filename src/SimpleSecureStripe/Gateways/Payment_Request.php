<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

/**
 * This gateway is provided so merchants can accept Chrome Payments, Microsoft Pay, etc.
 *
 * @author Simple & Secure WP
 * @package Stripe/Gateways
 *
 */
class Payment_Request extends Abstract_Gateway {

	use Payment\Traits\Intent;

	protected string $payment_method_type = 'card';

	public function __construct() {
		$this->id                 = 'sswps_payment_request';
		$this->tab_title          = __( 'PaymentRequest Gateway', 'simple-secure-stripe' );
		$this->template_name      = 'payment-request.php';
		$this->token_type         = 'Stripe_CC';
		$this->method_title       = __( 'Payment Request', 'simple-secure-stripe' );
		$this->method_description = __( 'Gateway that renders based on the user\'s browser. Chrome payment methods, Microsoft pay, etc.', 'simple-secure-stripe' );
		$this->has_digital_wallet = true;
		parent::__construct();
	}

	public function init_supports() {
		parent::init_supports();
		$this->supports[] = 'sswps_cart_checkout';
		$this->supports[] = 'sswps_product_checkout';
		$this->supports[] = 'sswps_banner_checkout';
		$this->supports[] = 'sswps_mini_cart_checkout';
	}

	public function get_icon() {
		return sswps_get_template_html( 'payment-request-icons.php' );
	}

	/**
	 * @inheritDoc
	 */
	public function register_assets() {
		parent::register_assets();

		Assets\Asset::register( 'sswps-payment-request', 'frontend/payment-request.js' )
			->add_to_group( 'sswps-local-payment' )
			->add_to_group( 'sswps-local-payment-cart' )
			->add_to_group( 'sswps-local-payment-product' )
			->set_dependencies( [
				'sswps-script',
				'sswps-stripe-external',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_payment_request_params',
				$this->get_localized_params()
			)
			->set_condition( [ $this, 'is_available' ] );
	}

	public function get_localized_params() {
		return array_merge_recursive(
			parent::get_localized_params(),
			[
				'button'   => [
					'type'   => $this->get_option( 'button_type' ),
					'theme'  => $this->get_option( 'button_theme' ),
					'height' => $this->get_button_height(),
				],
				'icons'    => [ 'chrome' => App::get( Plugin::class )->assets_url( 'img/chrome.svg' ) ],
				'messages' => [
					'invalid_amount' => __( 'Please update you product quantity before paying.', 'simple-secure-stripe' ),
					'add_to_cart'    => __( 'Adding to cart...', 'simple-secure-stripe' ),
					'choose_product' => __( 'Please select a product option before updating quantity.', 'simple-secure-stripe' ),
				],
			]
		);
	}

	public function get_button_height() {
		$value = $this->get_option( 'button_height' );
		$value .= strpos( $value, 'px' ) === false ? 'px' : '';

		return $value;
	}
}
