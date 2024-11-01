<?php
/**
 * Plugin Name: Simple & Secure Stripe for WooCommerce
 * Plugin URI: https://github.com/simplesecurewp
 * Description: Accept credit cards, Apple Pay, Google Pay, ACH, Klarna and more with Stripe.
 * Version: 1.0.0
 * Author: SimpleSecureWP, simplesecurewp@gmail.com
 * Text Domain: simple-secure-stripe
 * Domain Path: /lang/
 * Tested up to: 6.4.2
 * License: GPLv2 or later
 * WC requires at least: 3.0.0
 * WC tested up to: 7.5
 */
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SIMPLESECUREWP_STRIPE_FILE', __FILE__ );
define( 'SIMPLESECUREWP_STRIPE_DIR', __DIR__ );

require_once SIMPLESECUREWP_STRIPE_DIR . '/src/functions/min-php.php';
require_once SIMPLESECUREWP_STRIPE_DIR . '/vendor/autoload.php';
require_once SIMPLESECUREWP_STRIPE_DIR . '/vendor/vendor-prefixed/autoload.php';

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

/**
 * Verifies if we need to warn the user about min PHP version and bail to avoid fatal errors.
 */
if ( sswps_is_not_min_php_version() ) {
	sswps_not_php_version_textdomain( 'simple-secure-stripe', SIMPLESECUREWP_STRIPE_FILE );

	/**
	 * Include the plugin name into the correct place.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $names current list of names.
	 *
	 * @return array List of names after adding the plugin.
	 */
	function sswps_not_php_version_plugin_name( $names ) {
		$names['simple-secure-stripe'] = esc_html__( 'Simple & Secure Stripe Payments for WooCommerce', 'simple-secure-stripe' );
		return $names;
	}

	add_filter( 'simplesecurewp/stripe/not_php_version_names', 'sswps_not_php_version_plugin_name' );

	if ( ! has_filter( 'admin_notices', 'sswps_not_php_version_notice' ) ) {
		add_action( 'admin_notices', 'sswps_not_php_version_notice' );
	}

	return false;
}

require_once SIMPLESECUREWP_STRIPE_DIR . '/src/functions/load.php';

add_action( 'plugins_loaded', function () {
	App::register( Plugin::class );
}, 100 );