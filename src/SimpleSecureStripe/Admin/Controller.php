<?php

namespace SimpleSecureWP\SimpleSecureStripe\Admin;

use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;

class Controller extends Abstract_Controller {
	/**
	 * @var array
	 */
	private array $metaboxes = [
		Metaboxes\Order::class,
		Metaboxes\Product_Data::class,
	];

	/**
	 * @var array
	 */
	private array $settings = [
		'api_settings'      => Settings\API::class,
		'account_settings'  => Settings\Account::class,
		'advanced_settings' => Settings\Advanced::class,
	];

	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( Assets::class, Assets::class );
		$this->container->singleton( Menus::class, Menus::class );
		$this->container->singleton( Notices::class, Notices::class );
		$this->container->singleton( User_Edit::class, User_Edit::class );
		$this->container->singleton( Views\Welcome::class, Views\Welcome::class );
		$this->container->singleton( Views\Settings::class, Views\Settings::class );

		foreach ( $this->get_metabox_classes() as $class ) {
			$this->container->singleton( $class, $class );
		}

		foreach ( $this->get_settings_classes() as $class ) {
			$this->container->singleton( $class, $class );
		}

		$this->hooks();
	}

	/**
	 * @return array
	 */
	public function get_metabox_classes() : array {
		/**
		 * Filters the metabox classes.
		 *
		 * @since 1.0.0
		 *
		 * @param array $metaboxes The metabox classes.
		 */
		return apply_filters( 'sswps/metabox_classes', $this->metaboxes );
	}

	/**
	 * @return array
	 */
	public function get_settings_classes() : array {
		/**
		 * Filters the settings classes.
		 *
		 * @since 1.0.0
		 *
		 * @param array $settings The settings classes.
		 */
		return apply_filters( 'sswps/settings_classes', $this->settings );
	}

	/**
	 * Hooks.
	 */
	private function hooks() {
		add_action( 'admin_menu', $this->container->callback( Menus::class, 'sub_menu' ), 20 );
		add_action( 'admin_notices', $this->container->callback( Notices::class, 'notices' ) );

		add_action( 'woocommerce_settings_checkout', $this->container->callback( Views\Settings::class, 'output' ) );
		add_action( 'woocommerce_update_options_checkout', $this->container->callback( Views\Settings::class, 'save' ) );

		add_filter( 'sswps/settings_nav_tabs', $this->container->callback( Views\Settings::class, 'admin_settings_tabs' ), 20 );
		add_filter( 'sswps/settings_nav_tabs', $this->container->callback( Settings\Advanced::class, 'admin_nav_tab' ) );
		add_filter( 'sswps/settings_nav_tabs', $this->container->callback( Settings\API::class, 'admin_nav_tab' ) );

		add_action( 'add_meta_boxes', $this->container->callback( Metaboxes\Order::class, 'add_meta_boxes' ), 10, 2 );
		add_filter( 'woocommerce_product_data_tabs', $this->container->callback( Metaboxes\Product_Data::class, 'product_data_tabs' ) );
		add_action( 'woocommerce_product_data_panels', $this->container->callback( Metaboxes\Product_Data::class, 'output_panel' ) );
		add_action( 'woocommerce_admin_process_product_object', $this->container->callback( Metaboxes\Product_Data::class, 'save' ) );

		add_action( 'admin_enqueue_scripts', $this->container->callback( Assets::class, 'register_scripts' ) );
		add_action( 'wp_print_scripts', $this->container->callback( Assets::class, 'localize_scripts' ) );
		add_action( 'admin_footer', $this->container->callback( Assets::class, 'localize_scripts' ) );
		add_action( 'sswps/localize_sswps_advanced_settings', $this->container->callback( Assets::class, 'localize_advanced_scripts' ) );

		add_action( 'edit_user_profile', $this->container->callback( User_Edit::class, 'output' ) );
		add_action( 'show_user_profile', $this->container->callback( User_Edit::class, 'output' ) );
		add_action( 'edit_user_profile_update', $this->container->callback( User_Edit::class, 'save' ) );
		add_action( 'personal_options_update', $this->container->callback( User_Edit::class, 'save' ) );
	}
}