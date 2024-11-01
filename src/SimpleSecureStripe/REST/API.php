<?php

namespace SimpleSecureWP\SimpleSecureStripe\REST;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use WC_AJAX;

/**
 *
 * @author Simple & Secure WP
 * @package Stripe/Classes
 * @property Order_Actions    $order_actions
 * @property Cart             $cart
 * @property Checkout         $checkout
 * @property Payment_Intent   $payment_intent
 * @property Payment_Method   $payment_method
 * @property Google_Pay       $googlepay
 * @property Gateway_Settings $settings
 * @property Webhook          $webhook
 * @property Product_Data     $product_data
 * @property Source           $source
 * @property Signup           $signup
 */
class API {

	/**
	 *
	 * @var array
	 */
	private $controllers = [];

	/**
	 *
	 * @param string $key
	 *
	 * @return Abstract_REST
	 */
	public function __get( $key ) {
		$controller = isset( $this->controllers[ $key ] ) ? $this->controllers[ $key ] : '';
		if ( empty( $controller ) ) {
			wc_doing_it_wrong(
				__FUNCTION__,
				/* translators: %s: controller name. */
				sprintf( __( '%s is an invalid controller name.', 'simple-secure-stripe' ), $key ),
				Plugin::VERSION
			);
		}

		return $controller;
	}

	public function __set( $key, $value ) {
		$this->controllers[ $key ] = $value;
	}

	public function register_routes() {
		if ( self::is_rest_api_request() ) {
			foreach ( $this->controllers as $key => $controller ) {
				if ( is_callable( [ $controller, 'register_routes' ] ) ) {
					$controller->register_routes();
				}
			}
		}
	}

	/**
	 * @param string        $key
	 * @param Abstract_REST $object
	 */
	public function add_controller( string $key, Abstract_REST $object ) {
		$this->controllers[ $key ] = $object;
	}

	/**
	 * @return string
	 */
	public function rest_url() {
		return get_rest_url( null, $this->rest_uri() );
	}

	/**
	 * @return string
	 */
	public function rest_uri() {
		return 'sswps/v1/';
	}

	/**
	 * @return bool
	 */
	public static function is_rest_api_request() {
		global $wp;
		if ( ! empty( $wp->query_vars['rest_route'] ) && strpos( $wp->query_vars['rest_route'], App::get( static::class )->rest_uri() ) !== false ) {
			return true;
		}

		$request_uri = Request::get_sanitized_server_var( 'REQUEST_URI' );

		if ( ! empty( $request_uri ) && strpos( $request_uri, App::get( static::class )->rest_uri() ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Return true if this is a WP rest request. This function is a wrapper for WC()->is_rest_api_request()
	 * if it exists.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_wp_rest_request() {
		if ( function_exists( 'WC' ) && property_exists( WC(), 'is_rest_api_request' ) ) {
			return WC()->is_rest_api_request();
		}

		$request_uri = Request::get_sanitized_server_var( 'REQUEST_URI' );

		return ! empty( $request_uri ) && strpos( $request_uri, trailingslashit( rest_get_url_prefix() ) ) !== false;
	}

	/**
	 * @since 1.0.0
	 */
	public function process_frontend_request() {
		$path = Request::get_sanitized_var( 'path' );
		if ( ! empty( $path ) ) {
			global $wp;
			$wp->set_query_var( 'rest_route', sanitize_text_field( $path ) );
			rest_api_loaded();
		}
	}

	/**
	 * Return an endpoint for ajax requests that integrate with the WP Rest API.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function get_endpoint( $path ) {
		if ( version_compare( WC()->version, '3.2.0', '<' ) ) {
			$endpoint = esc_url_raw(
				apply_filters(
					'woocommerce_ajax_get_endpoint',
					add_query_arg(
						'wc-ajax',
						'sswps_frontend_request',
						remove_query_arg( [
							'remove_item',
							'add-to-cart',
							'added-to-cart',
							'order_again',
							'_wpnonce',
						], home_url( '/', 'relative' ) )
					),
					'sswps_frontend_request'
				)
			);
		} else {
			$endpoint = WC_AJAX::get_endpoint( 'sswps_frontend_request' );
		}

		return add_query_arg( 'path', '/' . trim( $path, '/' ), $endpoint );
	}

	public static function get_admin_endpoint( $path ) {
		$url = admin_url( 'admin-ajax.php' );

		return add_query_arg( [ 'action' => 'sswps_admin_request', 'path' => '/' . trim( $path, '/' ) ], $url );
	}

}