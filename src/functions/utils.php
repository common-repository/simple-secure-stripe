<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets\Asset;
use SimpleSecureWP\SimpleSecureStripe\Context\Context;
use SimpleSecureWP\SimpleSecureStripe\Utils\Arr;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;

if ( ! function_exists( 'sswps_asset' ) ) {
	/**
	 * Create an asset.
	 *
	 * @param string $slug The asset slug.
	 * @param string $file The asset file path.
	 * @param string $version The asset version.
	 * @param string|null $plugin_path The path to the root of the plugin.
	 */
	function sswps_asset( string $slug, string $file, string $version, $plugin_path = null ) {
		$asset = new Asset( $slug, $file, $version, $plugin_path );

		return $asset;
	}
}

if ( ! function_exists( 'sswps_is_truthy' ) ) {
	/**
	 * Determines if the provided value should be regarded as 'true'.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $var
	 *
	 * @return bool
	 */
	function sswps_is_truthy( $var ) : bool {
		if ( is_bool( $var ) ) {
			return $var;
		}

		/**
		 * Provides an opportunity to modify strings that will be
		 * deemed to evaluate to true.
		 *
		 * @param array $truthy_strings
		 */
		$truthy_strings = (array) apply_filters( 'sswps/is_truthy_strings', [
			'1',
			'enable',
			'enabled',
			'on',
			'y',
			'yes',
			'true',
		] );

		// Makes sure we are dealing with lowercase for testing
		if ( is_string( $var ) ) {
			$var = strtolower( $var );
		}

		// If $var is a string, it is only true if it is contained in the above array
		if ( in_array( $var, $truthy_strings, true ) ) {
			return true;
		}

		// All other strings will be treated as false
		if ( is_string( $var ) ) {
			return false;
		}

		// For other types (ints, floats etc) cast to bool
		return (bool) $var;
	}
}

if ( ! function_exists( 'sswps_asset_url' ) ) {
	/**
	 * Returns or echoes a url to a file in the plugin assets directory
	 *
	 * @param string $resource The filename of the resource.
	 * @param string|null $plugin_path Path to the root of the plugin.
	 * @param string|null $relative_path_to_assets Relative path to the assets directory.
	 *
	 * @return string
	 **/
	function sswps_asset_url( string $resource, string $plugin_path = null, string $relative_path_to_assets = null ) {
		static $_plugin_url = [];

		if ( $plugin_path === null ) {
			$plugin_path = dirname( dirname( dirname( __DIR__ ) ) );
		}

		if ( ! isset( $_plugin_url[ $plugin_path ] ) ) {
			$_plugin_url[ $plugin_path ] = trailingslashit( plugins_url( basename( $plugin_path ), $plugin_path ) );
		}

		$plugin_base_url = $_plugin_url[ $plugin_path ];

		$extension = pathinfo( $resource, PATHINFO_EXTENSION );
		$resource_path = $relative_path_to_assets;

		if ( is_null( $resource_path ) ) {
			$resources_path = 'src/assets/';
			switch ( $extension ) {
				case 'css':
					$resource_path = $resources_path . 'css/';
					break;
				case 'js':
					$resource_path = $resources_path . 'js/';
					break;
				case 'scss':
					$resource_path = $resources_path . 'scss/';
					break;
				default:
					$resource_path = $resources_path;
					break;
			}
		}

		$url = $plugin_base_url . $resource_path . $resource;

		/**
		 * Filters the resource URL
		 *
		 * @param string $url
		 * @param string $resource
		 */
		$url = apply_filters( 'sswps/resource_url', $url, $resource );

		return $url;
	}
}

if ( ! function_exists( 'sswps_sort_by_priority' ) ) {
	/**
	 * Sorting function based on Priority
	 *
	 * @since 1.0.0
	 *
	 * @param object|array $b Second subject to compare.
	 * @param object|array $a First Subject to compare.
	 * @param string $method Method to use for sorting.
	 *
	 * @return int
	 */
	function sswps_sort_by_priority( $a, $b, $method = null ) {
		if ( is_array( $a ) ) {
			$a_priority = $a['priority'];
		} else {
			$a_priority = $method ? $a->$method() : $a->priority;
		}

		if ( is_array( $b ) ) {
			$b_priority = $b['priority'];
		} else {
			$b_priority = $method ? $b->$method() : $b->priority;
		}

		if ( (int) $a_priority === (int) $b_priority ) {
			return 0;
		}

		return (int) $a_priority > (int) $b_priority ? 1 : -1;
	}
}

if ( ! function_exists( 'sswps_get_request_var' ) ) {
	/**
	 * Tests to see if the requested variable is set either as a post field or as a URL
	 * param and returns the value if so.
	 *
	 * Post data takes priority over fields passed in the URL query. If the field is not
	 * set then $default (null unless a different value is specified) will be returned.
	 *
	 * The variable being tested for can be an array if you wish to find a nested value.
	 *
	 * @since 1.0.0
	 *
	 * @see   Arr::get()
	 *
	 * @param string|array $var
	 * @param mixed        $default
	 *
	 * @return mixed
	 */
	function sswps_get_request_var( $var, $default = null ) {
		return Request::get_var( $var, $default );
	}
}

if ( ! function_exists( 'sswps_sanitize_deep' ) ) {

	/**
	 * Sanitizes a value according to its type.
	 *
	 * The function will recursively sanitize array values.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The value, or values, to sanitize.
	 *
	 * @return mixed|null Either the sanitized version of the value, or `null` if the value is not a string, number or
	 *                    array.
	 */
	function sswps_sanitize_deep( &$value ) {
		return Request::sanitize_deep( $value );
	}
}

if ( ! function_exists( 'sswps_context' ) ) {
	/**
	 * A wrapper function to get the singleton, immutable, global context object.
	 *
	 * Due to its immutable nature any method that would modify the context will return
	 * a clone of the context, not the original one.
	 *
	 * @since 4.9.5
	 *
	 * @return Context The singleton, immutable, global object instance.
	 */
	function sswps_context() {
		$context = App::get( 'context' );

		/**
		 * Filters the global context object.
		 *
		 * @since 1.0.0
		 *
		 * @param Context $context The singleton, immutable, global object instance.
		 */
		$context = apply_filters( 'sswps/global_context', $context );

		return $context;
	}
}