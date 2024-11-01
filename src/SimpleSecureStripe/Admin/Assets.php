<?php
namespace SimpleSecureWP\SimpleSecureStripe\Admin;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets\Asset;
use SimpleSecureWP\SimpleSecureStripe\Assets\API as AssetAPI;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\REST;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;

/**
 *
 * @package Stripe/Admin
 */
class Assets {

	public function register_scripts() {
		$obj              = $this;
		$gateway_settings = App::get( REST\Gateway_Settings::class );

		Asset::register( 'sswps-admin-settings', 'admin/admin-settings.js' )
			->add_to_group( 'sswps-admin' )
			->set_dependencies( [
				'jquery',
				'jquery-blockui',
			] )
			->add_localize_script(
				'sswps_setting_params',
				[
					'routes'     => [
						'apple_domain'      => REST\API::get_admin_endpoint( $gateway_settings->rest_uri( 'apple-domain' ) ),
						'create_webhook'    => REST\API::get_admin_endpoint( $gateway_settings->rest_uri( 'create-webhook' ) ),
						'delete_webhook'    => REST\API::get_admin_endpoint( $gateway_settings->rest_uri( 'delete-webhook' ) ),
						'connection_test'   => REST\API::get_admin_endpoint( $gateway_settings->rest_uri( 'connection-test' ) ),
						'delete_connection' => REST\API::get_admin_endpoint( $gateway_settings->rest_uri( 'delete-connection' ) )
					],
					'rest_nonce' => wp_create_nonce( 'wp_rest' ),
					'messages'   => [
						'delete_connection' => __( 'Are you sure you want to delete your connection data?', 'simple-secure-stripe' )
					],
				]
			)
			->set_action( 'admin_enqueue_scripts' )
			->set_condition( [ $this, 'is_sswps_settings_page' ] );

		Asset::register( 'sswps-meta-boxes-order', 'admin/meta-boxes-order.js' )
			->add_to_group( 'sswps-admin' )
			->set_dependencies( [
				'jquery',
				'jquery-blockui',
			] )
			->set_action( 'admin_enqueue_scripts' );

		Asset::register( 'sswps-product-data', 'admin/meta-boxes-product-data.js' )
			->add_to_group( 'sswps-admin' )
			->set_dependencies( [
				'jquery',
				'jquery-blockui',
				'jquery-ui-sortable',
				'jquery-ui-widget',
				'jquery-ui-core',
				'jquery-tiptip',
			] )
			->add_localize_script(
				'sswps_product_params',
				[
					'_wpnonce' => wp_create_nonce( 'wp_rest' ),
					'routes'   => [
						'enable_gateway' => App::get( REST\Product_Data::class )->rest_url( 'gateway' ),
						'save'           => App::get( REST\Product_Data::class )->rest_url( 'save' ),
					],
				]
			)
			->set_action( 'admin_enqueue_scripts' )
			->set_condition( [ $this, 'is_product_page' ] );

		Asset::register( 'sswps-admin-style', 'admin/admin.css' )
			->add_to_group( 'sswps-admin' )
			->add_style_data( 'rtl', 'replace' )
			->set_action( 'admin_enqueue_scripts' )
			->set_condition( static function() use ( $obj ) {
				return $obj->is_order_page()
					|| $obj->is_product_page()
					|| $obj->is_sswps_settings_page();
			} );

		Asset::register( 'sswps-admin-main-style', 'admin/main.css' )
			->add_to_group( 'sswps-admin' )
			->set_dependencies( [
				'woocommerce_admin_styles',
			] )
			->set_action( 'admin_enqueue_scripts' )
			->set_condition( [ $this, 'is_sswps_welcome_page' ] );

		Asset::register( 'sswps-admin-main-script', 'admin/main.js' )
			->add_to_group( 'sswps-admin' )
			->set_dependencies( [
				'jquery',
			] )
			->set_action( 'admin_enqueue_scripts' )
			->set_condition( [ $this, 'is_sswps_welcome_page' ] );

		Asset::register( 'sswps-admin-new', 'admin/sswps-admin.css' )
			->add_to_group( 'sswps-admin' )
			->add_style_data( 'rtl', 'replace' )
			->set_action( 'admin_enqueue_scripts' )
			->set_condition( static function() use ( $obj ) {
				return $obj->is_order_page()
					|| $obj->is_product_page()
					|| $obj->is_sswps_settings_page()
					|| $obj->is_sswps_welcome_page();
			} );

		App::get( AssetAPI::class )->enqueue_group( 'sswps-admin' );
	}

	public function localize_scripts() {
		global $current_section, $sswps_subsection;
		if ( ! empty( $current_section ) ) {
			$sswps_subsection = sanitize_title( Request::get_sanitized_var( 'sub_section', '' ) );
			do_action( 'sswps/localize_' . $current_section . '_settings' );
			// added for WC 3.0.0 compatability.
			remove_action( 'admin_footer', [ $this, 'localize_scripts' ] );
		}
	}

	public function localize_advanced_scripts() {
		global $current_section, $sswps_subsection;
		do_action( 'sswps/localize_' . $sswps_subsection . '_settings' );
	}

	/**
	 * Is the current page an order settings page?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_order_page() : bool {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		return $screen_id === 'shop_order' || $screen_id === 'woocommerce_page_wc-orders';
	}

	/**
	 * Is the current page a product settings page?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_product_page() : bool {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		return $screen_id === 'product';
	}

	/**
	 * Is the current page an sswps settings page?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_sswps_settings_page() : bool {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( strpos( $screen_id, 'wc-settings' ) === false ) {
			return false;
		}

		$section = Request::get_sanitized_var( 'section' );

		if ( empty( $section ) || ! preg_match( '/sswps_[\w]*/', $section ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Is the current page an sswps welcome page?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_sswps_welcome_page() : bool {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		return $screen_id === 'woocommerce_page_sswps-main';
	}
}
