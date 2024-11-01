<?php

namespace SimpleSecureWP\SimpleSecureStripe;

use SimpleSecureWP\SimpleSecureStripe\Utils\URL;

/**
 *
 * @package Stripe/Classes
 *
 */
class Install {
	/**
	 * When initializing the plugin for the first time, redirect the user to the welcome page.
	 *
	 * @since 1.0.0
	 */
	public static function initialize() {
		if ( get_option( Constants::INITIAL_INSTALL, null ) === 'yes' ) {
			delete_option( Constants::INITIAL_INSTALL );
			wp_safe_redirect( URL::main() );
		}
	}

	/**
	 * Executed when the plugin is installed.
	 *
	 * @since 1.0.0
	 */
	public static function install() {
		// If the version key has never been set in wp_options, mark it as an initial install.
		if ( ! get_option( Constants::VERSION_KEY, false ) ) {
			update_option( Constants::INITIAL_INSTALL, 'yes' );
		}

		// Set the version in wp_options.
		update_option( Constants::VERSION_KEY, Plugin::VERSION );

		/**
		 * Schedule required actions. Actions are scheduled during install as they only need to be setup
		 * once.
		 */
		App::get( Plugin::class )->scheduled_actions();
	}

	/**
	 * @since 1.0.0
	 *
	 * @param array $links
	 */
	public static function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => sprintf( '<a href="%1$s">%2$s</a>', URL::wc_settings( 'sswps_api' ), esc_html__( 'Settings', 'simple-secure-stripe' ) ),
			'docs'     => sprintf( '<a target="_blank" href="https://sswp.io/stripe" rel="noopener noreferrer">%s</a>', __( 'Documentation', 'simple-secure-stripe' ) ),
		);

		return array_merge( $action_links, $links );
	}

}
