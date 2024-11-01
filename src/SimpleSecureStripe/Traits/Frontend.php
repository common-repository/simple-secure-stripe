<?php

namespace SimpleSecureWP\SimpleSecureStripe\Traits;

use SimpleSecureWP\SimpleSecureStripe\Gateways;
use WP_Error;
use WP_REST_Request;

/**
 * @since 1.0.0
 */
trait Frontend {

	/**
	 * @since 1.0.0
	 * @var WP_REST_Request
	 */
	private WP_REST_Request $request;

	/**
	 * @since 1.0.0
	 */
	protected function cart_includes() {
		include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
		include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
		wc_load_cart();
		// loads cart from session
		WC()->cart->get_cart();
		WC()->payment_gateways();
	}

	/**
	 * @since 1.0.0
	 */
	protected function frontend_includes() {
		WC()->frontend_includes();
		wc_load_cart();
		WC()->cart->get_cart();
		WC()->payment_gateways();
	}

	/**
	 * @since 1.0.0
	 *
	 * @param $request
	 *
	 * @return bool|WP_Error
	 */
	public function validate_rest_nonce( $request ) {
		if ( ! isset( $request['wp_rest_nonce'] ) || ! wp_verify_nonce( $request['wp_rest_nonce'], 'wp_rest' ) ) {
			return new WP_Error( 'rest_cookie_invalid_nonce', __( 'Cookie nonce is invalid' ), [ 'status' => 403 ] );
		}

		return true;
	}

	/**
	 * @since 1.0.0
	 *
	 * @param \WC_Cart $cart
	 *
	 * @return void
	 */
	protected function empty_cart( $cart ) {
		foreach ( $cart->get_cart() as $key => $item ) {
			unset( $cart->cart_contents[ $key ] );
		}
	}

	protected function get_supported_gateways( $context = 'product' ) {
		return array_filter( WC()->payment_gateways()->payment_gateways(), function ( $gateway ) use ( $context ) {
			return $gateway instanceof Gateways\Abstract_Gateway
				&& $gateway->supports( "sswps_{$context}_checkout" )
				&& wc_string_to_bool( $gateway->get_option( 'enabled' ) );
		} );
	}
}