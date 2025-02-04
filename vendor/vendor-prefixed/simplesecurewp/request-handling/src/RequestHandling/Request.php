<?php
/**
 * @license MIT
 *
 * Modified by sswp-bot on 26-December-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling;

use SimpleSecureWP\SimpleSecureStripe\StellarWP\Arrays\Arr;

class Request {
	/**
	 * Tests to see if the requested variable is set either as a post field or as a URL
	 * param and returns the value if so.
	 *
	 * Post data takes priority over fields passed in the URL query. If the field is not
	 * set then $default (null unless a different value is specified) will be returned.
	 *
	 * The variable being tested for can be an array if you wish to find a nested value.
	 *
	 * Alias for get_var().
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
	public static function get_sanitized_var( $var, $default = null ) {
		return static::get_var( $var, $default );
	}

	/**
	 * Grab sanitized _SERVER variable.
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
	public static function get_sanitized_server_var( $var, $default = null ) {
		$data = [];

		// Prevent a slew of warnings every time we call this.
		if ( ! empty( $_SERVER ) ) {
			$data[] = (array) $_SERVER;
		}

		if ( empty( $data ) ) {
			return $default;
		}

		$unsafe = Arr::get_in_any( $data, $var, $default );
		return static::sanitize_deep( $unsafe );
	}

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
	public static function get_var( $var, $default = null ) {
		$requests = [];

		// Prevent a slew of warnings every time we call this.
		if ( ! empty( $_REQUEST ) ) {
			$requests[] = (array) $_REQUEST;
		}

		if ( ! empty( $_GET ) ) {
			$requests[] = (array) $_GET;
		}

		if ( ! empty( $_POST ) ) {
			$requests[] = (array) $_POST;
		}

		if ( empty( $requests ) ) {
			return $default;
		}

		$unsafe = Arr::get_in_any( $requests, $var, $default );
		return static::sanitize_deep( $unsafe );
	}

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
	public static function sanitize_deep( &$value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( is_string( $value ) ) {
			$value = htmlspecialchars( $value );
			return $value;
		}
		if ( is_int( $value ) ) {
			$value = filter_var( $value, FILTER_VALIDATE_INT );
			return $value;
		}
		if ( is_float( $value ) ) {
			$value = filter_var( $value, FILTER_VALIDATE_FLOAT );
			return $value;
		}
		if ( is_array( $value ) ) {
			array_walk( $value, [ __CLASS__, 'sanitize_deep' ] );
			return $value;
		}

		return null;
	}
}
