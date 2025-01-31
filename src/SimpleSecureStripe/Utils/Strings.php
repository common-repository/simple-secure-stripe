<?php
/**
 * String Utilities
 *
 * @since   1.0.0
 */

namespace SimpleSecureWP\SimpleSecureStripe\Utils;

/**
 * Class Strings
 *
 * @since 1.0.0
 */
class Strings {

	/**
	 * Replace the first occurrence of a given value in the string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search  The string to search for and replace.
	 * @param string $replace The replacement string.
	 * @param string $subject The string to do the search and replace from.
	 *
	 * @return string The string with the first occurrence of a given value replaced.
	 */
	public static function replace_first( $search, $replace, $subject ) {
		if ( '' === $search ) {
			return $subject;
		}

		$position = strpos( $subject, $search );

		if ( $position !== false ) {
			return substr_replace( $subject, $replace, $position, strlen( $search ) );
		}

		return $subject;
	}

	/**
	 * Replace the last occurrence of a given value in the string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search  The string to search for and replace.
	 * @param string $replace The replacement string.
	 * @param string $subject The string to do the search and replace from.
	 *
	 * @return string The string with the last occurrence of a given value replaced.
	 */
	public static function replace_last( $search, $replace, $subject ) {
		$position = strrpos( $subject, $search );

		if ( $position !== false ) {
			return substr_replace( $subject, $replace, $position, strlen( $search ) );
		}

		return $subject;
	}
}