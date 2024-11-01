<?php
namespace SimpleSecureWP\SimpleSecureStripe\Dependencies;

use SimpleSecureWP\SimpleSecureStripe\Plugin;

/**
 * A list of Simple & Secure WP's major plugins. Useful when encouraging users to download one of these.
 */
class Plugins {

	/**
	 * A list of Simple & Secure WP plugin's details in this array format:
	 *
	 * [
	 *  'short_name'   => Common name for the plugin, used in places such as WP Admin messages
	 *  'class'        => Main plugin class
	 *  'thickbox_url' => Download or purchase URL for plugin from within /wp-admin/ thickbox
	 * ]
	 *
	 * @since 1.0.0
	 */
	private $plugins = [
		[
			'short_name'   => 'Simple & Secure Stripe Payments',
			'class'        => Plugin::class,
			'thickbox_url' => 'plugin-install.php?tab=plugin-information&plugin=simple-secure-stripe&TB_iframe=true',
		],
		[
			'short_name'   => 'WooCommerce',
			'class'        => 'WooCommerce',
			'thickbox_url' => 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true',
		],
	];

	/**
	 * Searches the plugin list for key/value pair and return the full details for that plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_key The array key this value will appear in.
	 * @param string $search_val The value itself.
	 *
	 * @return array|null
	 */
	public function get_plugin_by_key( $search_key, $search_val ) {
		foreach ( $this->get_list() as $plugin ) {
			if ( isset( $plugin[ $search_key ] ) && $plugin[ $search_key ] === $search_val ) {
				return $plugin;
			}
		}

		return null;
	}

	/**
	 * Retrieves plugins details by plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Common name for the plugin, not necessarily the lengthy name in the WP Admin Plugins list.
	 *
	 * @return array|null
	 */
	public function get_plugin_by_name( $name ) {
		return $this->get_plugin_by_key( 'short_name', $name );
	}

	/**
	 * Retrieves plugins details by class name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_class Main/base class for this plugin
	 *
	 * @return array|null
	 */
	public function get_plugin_by_class( $main_class ) {
		return $this->get_plugin_by_key( 'class', $main_class );
	}

	/**
	 * Retrieves the entire list.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_list() {
		/**
		 * Gives an opportunity to filter the list of plugins.
		 *
		 * @since 1.0.0
		 *
		 * @param array $plugins Contains a list of all plugins.
		 */
		return apply_filters( 'sswps/plugins_get_list', $this->plugins );
	}

	/**
	 * Checks if given plugin is active.
	 *
	 * @param string $plugin_name The name of the plugin. Each plugin defines their name upon hooking on the filter.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if plugin is active. False if plugin is not active.
	 */
	public static function is_active( $plugin_name ) {
		if ( ! did_action( 'plugins_loaded' ) ) {
			_doing_it_wrong(
				__METHOD__,
				__( 'Using this function before "plugins_loaded" action has fired can return unreliable results.', 'simple-secure-stripe' ),
				'1.0.0'
			);
		}

		/**
		 * Filters the array that each plugin overrides to
		 * set itself as active when this function is called.
		 *
		 * @example [ 'simple-secure-stripe' => true ]
		 *
		 * @since   1.0.0
		 *
		 * @return array Plugin slugs as keys and bool as value for whether it's active or not.
		 */
		$plugins = apply_filters( 'sswps/active_plugins', [] );

		return isset( $plugins[ $plugin_name ] ) && sswps_is_truthy( $plugins[ $plugin_name ] );
	}
}