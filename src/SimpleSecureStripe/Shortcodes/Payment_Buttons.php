<?php
namespace SimpleSecureWP\SimpleSecureStripe\Shortcodes;

use SimpleSecureWP\SimpleSecureStripe\Field_Manager;
use WC_Shortcodes;

/**
 * Payment buttons shortcode.
 * @since 1.0.0
 */
class Payment_Buttons extends Abstract_Shortcode {
	/**
	 * @inheritDoc
	 */
	public function shortcode() : string {
		return 'sswps_payment_buttons';
	}

	/**
	 * @inheritDoc
	 */
	public function output( array $atts ) : string {
		$method  = null;
		$wrapper = [
			'class' => 'sswps-shortcode',
		];

		if ( is_product() ) {
			$method            = 'output_product_checkout_fields';
			$wrapper['class'] .= ' sswps-shortcode-product-buttons';
		} elseif (
			! is_null( WC()->cart )
			&& (
				is_cart()
				|| (
					isset( $atts['page'] ) && $atts['page'] === 'cart'
				)
			)
		) {
			$method            = 'output_cart_fields';
			$wrapper['class'] .= ' sswps-shortcode-cart-buttons';
		}

		if ( ! $method ) {
			return '';
		}

		return WC_Shortcodes::shortcode_wrapper( [ Field_Manager::class, $method ], $atts, $wrapper );
	}
}