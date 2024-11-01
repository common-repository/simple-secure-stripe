<?php

namespace SimpleSecureWP\SimpleSecureStripe;

/**
 *
 * @author Simple & Secure WP
 * @package Stripe/Classes
 *
 */
class Update {

	private static $updates = [];

	/**
	 * Performs an update on the plugin if required.
	 */
	public static function update() {
		// if option is not set, make the default version 3.0.6.
		$current_version = get_option( Constants::VERSION_KEY, '3.0.6' );

		// if database version is less than plugin version, an update might be required.
		if ( version_compare( $current_version, Plugin::VERSION, '<' ) ) {
			foreach ( self::$updates as $version => $path ) {
				/*
				 * If the current version is less than the version in the loop, then perform upgrade.
				 */
				if ( version_compare( $current_version, $version, '<' ) ) {
					$file = SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/updates/' . $path;
					if ( file_exists( $file ) ) {
						include $file;
					}
					$current_version = $version;
					update_option( Constants::VERSION_KEY, $current_version );
					add_action(
						'admin_notices',
						function() use ( $current_version ) {
							/* translators: %s: WooCommerce version number */
							$message = sprintf( __( 'Thank you for updating Stripe for WooCommerce to version %1$s.', 'simple-secure-stripe' ), $current_version );

							printf( '<div class="notice notice-success is-dismissible"><p>%1$s</p></div>', $message );
						}
					);
				}
			}
			// save latest version.
			update_option( Constants::VERSION_KEY, Plugin::VERSION );
		}
	}

}
