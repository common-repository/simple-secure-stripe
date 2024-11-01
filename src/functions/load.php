<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides functions to handle the loading operations of the plugin.
 *
 * The functions are defined in the global namespace to allow easier loading in the main plugin file.
 *
 * @since 1.0.0
 */

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

/**
 * Shows a message to indicate the plugin cannot be loaded due to missing requirements.
 *
 * @since 1.0.0
 */
function sswps_show_fail_message() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	Plugin::load_text_domain();

	$url  = 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true';
	$link = sprintf(
		'<a href="%1$s" class="thickbox" title="WooCommerce">WooCommerce</a>',
		esc_url( $url ),
	);

	$message = sprintf(
		esc_html__(
			/* Translators: %s - linked "WooCommerce" */
			'To begin using Simple & Secure Stripe Payments, please install the latest version of %1$s.',
			'simple-secure-stripe'
		),
		$link,
	);

	// The message HTML is escaped in the line above.
	// phpcs:ignore
	echo '<div class="error"><p>' . $message . '</p></div>';
}