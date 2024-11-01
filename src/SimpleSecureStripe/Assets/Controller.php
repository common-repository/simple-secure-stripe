<?php

namespace SimpleSecureWP\SimpleSecureStripe\Assets;

use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

/**
 * @since 1.0.0
 */
class Controller extends Abstract_Controller {
	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( API::class, API::class );
		$this->container->singleton( Assets::class, Assets::class );
		$this->container->singleton( Data::class, Data::class );

		$this->hooks();

		if ( $this->container->get( Plugin::class )->is_request( 'frontend' ) ) {
			$this->container->get( Assets::class );
		}
	}

	/**
	 * Bind hooks.
	 */
	protected function hooks() {
		add_action( 'wp_enqueue_scripts', $this->container->callback( Assets::class, 'register_scripts' ), 10 );
		add_action( 'wp_print_scripts', $this->container->callback( Assets::class, 'localize_scripts' ), 5 );
		add_action( 'wp_print_footer_scripts', $this->container->callback( Assets::class, 'localize_scripts' ), 5 );
		add_action( 'wp_print_footer_scripts', $this->container->callback( Assets::class, 'print_footer_scripts' ), 6 );

		// Hook the actual registering of.
		add_action( 'init', $this->container->callback( API::class, 'register_in_wp' ), 1, 0 );
		add_filter( 'script_loader_tag', $this->container->callback( API::class, 'filter_tag_async_defer' ), 50, 2 );
		add_filter( 'script_loader_tag', $this->container->callback( API::class, 'filter_modify_to_module' ), 50, 2 );
		add_filter( 'script_loader_tag', $this->container->callback( API::class, 'filter_print_before_after_script' ), 100, 2 );

		// Enqueue late.
		add_filter( 'script_loader_tag', $this->container->callback( API::class, 'filter_add_localization_data' ), 500, 2 );
	}
}