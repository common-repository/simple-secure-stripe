<?php
namespace SimpleSecureWP\SimpleSecureStripe\Shortcodes;

abstract class Abstract_Shortcode implements Contracts\Shortcode {
	/**
	 * Abstract_Shortcode constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$callback = apply_filters( 'sswps/shortcode_callback', [ $this, 'output' ] );

		add_shortcode( $this->shortcode(), $callback );
	}

	/**
	 * Get the shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	abstract public function shortcode() : string;

	/**
	 * Output the shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	abstract public function output( array $atts ) : string;
}