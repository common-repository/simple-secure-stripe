<?php
namespace SimpleSecureWP\SimpleSecureStripe;

use SimpleSecureWP\SimpleSecureStripe\Gateways;
use WC_Product;

/**
 * @since 1.0.0
 * @package Stripe/Classes
 * @author Simple & Secure WP
 *
 */
class Product_Gateway_Option {

	/**
	 *
	 * @var array
	 */
	private $settings = [];

	/**
	 *
	 * @var WC_Product
	 */
	private $product;

	/**
	 *
	 * @var Gateways\Abstract_Gateway
	 */
	private $payment_method;

	/**
	 *
	 * @param int|WC_Product            $product
	 * @param Gateways\Abstract_Gateway $payment_method
	 */
	public function __construct( $product, $payment_method ) {
		if ( ! is_object( $product ) ) {
			$this->product = wc_get_product( $product );
		} else {
			$this->product = $product;
		}
		$this->payment_method = $payment_method;

		$this->init_settings();
	}

	/**
	 * Return the ID of this product option.
	 */
	public function get_id() {
		return '_' . $this->payment_method->id . '_options';
	}

	/**
	 * Save the settings
	 */
	public function save() {
		$this->product->update_meta_data( $this->get_id(), $this->settings );
		$this->product->save();
	}

	/**
	 * Initialzie the settings.
	 */
	public function init_settings() {
		if ( ! $this->settings && $this->product ) {
			$this->settings = $this->product->get_meta( $this->get_id() );
			$this->settings = is_array( $this->settings ) ? $this->settings : [];
			$this->settings = wp_parse_args( $this->settings, $this->get_default_values() );
		}
	}

	/**
	 * Return default options build from the payment gateway's options.
	 *
	 * @return array
	 */
	public function get_default_values() {
		return [
			'enabled'     => $this->payment_method->product_checkout_enabled(),
			'charge_type' => $this->payment_method->get_option( 'charge_type' ),
		];
	}

	/**
	 *
	 * @param string $key
	 * @param mixed  $default
	 */
	public function get_option( $key, $default = null ) {
		if ( ! isset( $this->settings[ $key ] ) && null != $default ) {
			$this->settings[ $key ] = $default;
		}

		return $this->settings[ $key ];
	}

	public function set_option( $key, $value ) {
		$this->settings[ $key ] = $value;
	}

	public function enabled() {
		return $this->get_option( 'enabled', false );
	}

	/**
	 * @since 1.0.0
	 * @return bool
	 */
	public function has_product() {
		return ! ! $this->product;
	}

}
