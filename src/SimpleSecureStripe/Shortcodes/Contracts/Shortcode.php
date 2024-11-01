<?php
namespace SimpleSecureWP\SimpleSecureStripe\Shortcodes\Contracts;

interface Shortcode {
	/**
	 * Get the shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function shortcode() : string;

	/**
	 * Output the shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function output( array $atts ) : string;
}