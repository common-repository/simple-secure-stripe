<?php

namespace SimpleSecureWP\SimpleSecureStripe\Assets;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets\API as AssetAPI;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Gateways\Abstract_Gateway;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

class Assets {

	/**
	 * @var string
	 */
	public string $prefix = 'sswps-';

	/**
	 * @var array
	 */
	public array $registered_scripts = [];

	/**
	 * @var array
	 */
	public array $enqueued_scripts = [];

	/**
	 * @var array
	 */
	public array $localized_scripts = [];

	/**
	 * @var array
	 */
	public array $localized_data = [];

	/**
	 * Enqueue all frontend scripts needed by the plugin
	 */
	public function register_scripts() {
		Asset::register( 'sswps-stripe-external', 'https://js.stripe.com/v3/' )
			->add_to_group( 'sswps' )
			->set_type( 'js' )
			->set_action( 'wp_enqueue_scripts' );

		Asset::register( 'sswps-gpay', 'https://pay.google.com/gp/p/js/pay.js' )
			->add_to_group( 'sswps' )
			->set_action( 'wp_enqueue_scripts' );

		Asset::register( 'sswps-form-handler', 'frontend/form-handler.js' )
			->add_to_group( 'sswps' )
			->set_dependencies( [
				'jquery',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_form_handler_params',
				[
					'no_results' => __(
						'No matches found',
						'simple-secure-stripe'
					),
				]
			);

		Asset::register( 'sswps-script', 'frontend/sswps.js' )
			->add_to_group( 'sswps' )
			->set_dependencies( [
				'jquery',
				'sswps-stripe-external',
				'woocommerce',
				'sswps-form-handler',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_params_v3',
				$this->get_localize_script_data_sswps()
			)
			->add_localize_script(
				'sswps_messages',
				sswps_get_error_messages()
			)
			->add_localize_script(
				'sswps_checkout_fields',
				sswps_get_checkout_fields()
			);

		Asset::register( 'sswps-styles', 'stripe.css' )
			->add_to_group( 'sswps-payment-method' )
			->set_action( 'wp_enqueue_scripts' )
			->add_style_data( 'rtl', 'replace' );

		// mini cart is not relevant on cart and checkout page.
		if ( ! is_checkout() && ! is_cart() ) {
			foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
				if ( $gateway instanceof Gateways\Abstract_Gateway && $gateway->is_available() && $gateway->mini_cart_enabled() ) {
					$gateway->enqueue_frontend_scripts( 'mini_cart' );
				}
			}
		}

		if ( function_exists( 'wp_add_inline_script' ) ) {
			wp_add_inline_script(
				'sswps-script',
				'(function(){
				if(window.navigator.userAgent.match(/MSIE|Trident/)){
					var script = document.createElement(\'script\');
					script.setAttribute(\'src\', \'' . $this->assets_url( 'js/frontend/promise-polyfill.min.js' ) . '\');
					document.head.appendChild(script);
				}
			}());'
			);
		}

		App::get( API::class )->enqueue_group( 'sswps' );
	}

	public function localize_scripts() {
		// don't need to call localize_scripts twice.
		if ( doing_action( 'wp_print_scripts' ) ) {
			remove_action( 'wp_print_footer_scripts', [ $this, 'localize_scripts' ], 5 );
		}
	}

	/**
	 * Get the localize script data for the sswps script.
	 *
	 * @return array
	 */
	public function get_localize_script_data_sswps() : array {
		if ( ! function_exists( 'sswps_get_account_id' ) ) {
			return [];
		}

		$account_id = sswps_get_account_id();
		$data = [
			'api_key'      => sswps_get_publishable_key(),
			'account'      => $account_id,
			'page'         => $this->get_page_id(),
			'version'      => Plugin::VERSION,
			'mode'         => sswps_mode(),
			'stripeParams' => [
				'stripeAccount' => $account_id,
				'apiVersion'    => '2022-08-01',
				'betas'         => [],
			],
		];

		/**
		 * Filter the localize data for the sswps script.
		 *
		 * @since 1.0.0
		 *
		 * @param array $data The localize data.
		 */
		return (array) apply_filters( 'sswps/get_localize_script_data_sswps', $data );
	}

	public function enqueue_checkout_scripts() {
		$this->enqueue_local_payment_scripts();
	}

	public function enqueue_local_payment_scripts() {
		App::get( API::class )->get( 'sswps-local-payment' )
			->add_localize_script(
				'sswps_local_payment_params',
				sswps_get_local_payment_params()
			)
			->enqueue();
		App::get( API::class )->enqueue_group( 'sswps-local-payment' );
	}

	public function register_script( $handle, $src, $deps = [], $version = '', $footer = true ) {
		$version                    = empty( $version ) && null !== $version ? Plugin::VERSION : $version;
		$this->registered_scripts[] = $this->get_handle( $handle );
		wp_register_script( $this->get_handle( $handle ), $src, $deps, $version, $footer );
	}

	public function enqueue_script( $handle, $src = '', $deps = [], $version = '', $footer = true ) {
		$handle  = $this->get_handle( $handle );
		$version = empty( $version ) && null !== $version ? Plugin::VERSION : $version;
		if ( ! in_array( $handle, $this->registered_scripts ) ) {
			$this->register_script( $handle, $src, $deps, $version, $footer );
		}
		$this->enqueued_scripts[] = $handle;
		wp_enqueue_script( $handle );
	}

	/**
	 *
	 * @param string $handle
	 * @param array  $data
	 * @param string $object_name
	 */
	public function localize_script( $handle, $data, $object_name = '' ) {
		$handle = $this->get_handle( $handle );
		if ( wp_script_is( $handle, 'registered' ) ) {
			$name = str_replace( $this->prefix, '', $handle );
			if ( ! $object_name ) {
				$object_name = str_replace( '-', '_', $handle ) . '_params';
			}
			if ( ! in_array( $object_name, $this->localized_data ) ) {
				if ( $data ) {
					$this->localized_scripts[] = $handle;
					$this->localized_data[]    = $object_name;
					wp_localize_script( $handle, $object_name, $data );
				}
			}
		}
	}

	public function get_handle( $handle ) {
		return strpos( $handle, $this->prefix ) === false ? $this->prefix . $handle : $handle;
	}

	/**
	 *
	 * @param string $uri
	 */
	public function assets_url( $uri = '' ) {
		// if minification scripts required, convert the uri to its min format.
		// don't minify scripts in the build directory
		if ( strpos( $uri, 'dist/' ) !== 0 ) {
			$uri = ( ( $min = $this->get_min() ) ) ? preg_replace( '/([\w-]+)(\.(?<!min\.)(js|css))$/', '$1' . $min . '$2', $uri ) : $uri;
		}

		return untrailingslashit( App::get( Plugin::class )->assets_url( $uri ) );
	}

	public function get_min() {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}

	private function get_page_id() {
		return sswps_get_current_page();
	}

	public function print_footer_scripts() {
		global $wp;

		if ( is_checkout() && ! isset( $wp->query_vars['order_pay'] ) && ! is_order_received_page() && ! did_action( 'sswps_blocks_enqueue_styles' ) ) {
			$available_gateways = array_keys( WC()->payment_gateways()->get_available_payment_gateways() );
			$gateways           = array_filter( WC()->payment_gateways()->payment_gateways(), function( $gateway ) use ( $available_gateways ) {
				return $gateway instanceof Gateways\Abstract_Gateway
					&& $gateway->is_available()
					&& ! in_array( $gateway->id, $available_gateways );
			} );
			// If there are entries in the $gateways array that means some plugin filtered out the gateway.
			// It still needs to output its scripts
			foreach ( $gateways as $gateway ) {
				/**
				 * @var Gateways\Abstract_Gateway $gateway
				 */
				$gateway->enqueue_frontend_scripts( 'checkout' );
			}
		}
	}

}