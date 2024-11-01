<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\REST;
use WC_Shipping_Rate;

/**
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Gateways
 */
class Google_Pay extends Abstract_Gateway {

	use Payment\Traits\Intent;

	protected string $payment_method_type = 'card';

	public function __construct() {
		$this->id                 = 'sswps_googlepay';
		$this->tab_title          = __( 'Google Pay', 'simple-secure-stripe' );
		$this->template_name      = 'googlepay.php';
		$this->token_type         = 'Stripe_GooglePay';
		$this->method_title       = __( 'Google Pay', 'simple-secure-stripe' );
		$this->method_description = __( 'Google Pay gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->has_digital_wallet = true;
		parent::__construct();
		$this->icon = App::get( Plugin::class )->assets_url( 'img/' . $this->get_option( 'icon' ) . '.svg' );
	}

	public function init_supports() {
		parent::init_supports();
		$this->supports[] = 'sswps_cart_checkout';
		$this->supports[] = 'sswps_product_checkout';
		$this->supports[] = 'sswps_banner_checkout';
		$this->supports[] = 'sswps_mini_cart_checkout';
	}

	/**
	 * @inheritDoc
	 */
	public function register_assets() {
		parent::register_assets();

		Assets\Asset::register( 'sswps-googlepay-checkout', 'frontend/googlepay-checkout.js' )
			->add_to_group( 'sswps-local-payment' )
			->set_dependencies( [
				'sswps-script',
				'sswps-gpay',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_googlepay_checkout_params',
				$this->get_localized_params()
			)
			->set_condition( [ $this, 'is_available' ] );

		Assets\Asset::register( 'sswps-googlepay-cart', 'frontend/googlepay-cart.js' )
			->add_to_group( 'sswps-local-payment-cart' )
			->set_dependencies( [
				'sswps-script',
				'sswps-gpay',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_googlepay_cart_params',
				$this->get_localized_params()
			)
			->set_condition( [ $this, 'is_available' ] );

		Assets\Asset::register( 'sswps-googlepay-product', 'frontend/googlepay-product.js' )
			->add_to_group( 'sswps-local-payment-product' )
			->set_dependencies( [
				'sswps-script',
				'sswps-gpay',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_googlepay_product_params',
				$this->get_localized_params()
			)
			->set_condition( [ $this, 'is_available' ] );

		Assets\Asset::register( 'sswps-gpay-admin', 'admin/admin-googlepay.js' )
			->add_to_group( 'sswps-admin' )
			->set_dependencies( [
				'sswps-admin-settings',
				'sswps-gpay',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->set_condition( [ $this, 'is_available' ] );
	}

	public function enqueue_admin_scripts() {
		wp_enqueue_script(
			'sswps-gpay-admin',
			App::get( Plugin::class )->assets_url( 'js/admin/admin-googlepay.js' ),
			[
				'sswps-gpay',
				'sswps-admin-settings',
			],
			\SimpleSecureWP\SimpleSecureStripe\Plugin::VERSION,
			true
		);
	}

	public function get_localized_params() {
		$data = array_merge_recursive(
			parent::get_localized_params(),
			[
				'environment'        => sswps_mode() === 'test' ? 'TEST' : 'PRODUCTION',
				'merchant_id'        => sswps_mode() === 'test' ? '' : $this->get_option( 'merchant_id' ),
				'merchant_name'      => $this->get_option( 'merchant_name' ),
				'processing_country' => WC()->countries ? WC()->countries->get_base_country() : wc_get_base_location()['country'],
				'button_color'       => $this->get_option( 'button_color', 'black' ),
				'button_style'       => $this->get_option( 'button_style' ),
				'button_size_mode'   => 'fill',
				'button_locale'      => $this->get_payment_button_locale(),
				'button_shape'       => $this->get_option( 'button_shape', 'rect' ),
				'total_price_label'  => __( 'Total', 'simple-secure-stripe' ),
				'routes'             => [ 'payment_data' => REST\API::get_endpoint( App::get( REST\Google_Pay::class )->rest_uri( 'shipping-data' ) ) ],
				'messages'           => [ 'invalid_amount' => __( 'Please update you product quantity before using Google Pay.', 'simple-secure-stripe' ) ],
			]
		);

		return $data;
	}

	protected function get_display_item_for_cart( $price, $label, $type, ...$args ) {
		switch ( $type ) {
			case 'tax':
				$type = 'TAX';
				break;
			default:
				$type = 'LINE_ITEM';
				break;
		}

		return [
			'label' => $label,
			'type'  => $type,
			'price' => wc_format_decimal( $price, 2 ),
		];
	}

	protected function get_display_item_for_product( $product ) {
		return [
			'label' => esc_attr( $product->get_name() ),
			'type'  => 'SUBTOTAL',
			'price' => wc_format_decimal( $product->get_price(), 2 ),
		];
	}

	protected function get_display_item_for_order( $price, $label, $order, $type, ...$args ) {
		switch ( $type ) {
			case 'tax':
				$type = 'TAX';
				break;
			default:
				$type = 'LINE_ITEM';
				break;
		}

		return [
			'label' => $label,
			'type'  => $type,
			'price' => wc_format_decimal( $price, 2 ),
		];
	}

	public function get_formatted_shipping_methods( $methods = [] ) {
		$methods = parent::get_formatted_shipping_methods( $methods );
		if ( empty( $methods ) ) {
			// GPay does not like empty shipping methods. Make a temporary one;
			$methods[] = [
				'id'          => 'default',
				'label'       => __( 'Waiting...', 'simple-secure-stripe' ),
				'description' => __( 'loading shipping methods...', 'simple-secure-stripe' ),
			];
		}

		return $methods;
	}

	public function get_formatted_shipping_method( $price, $rate, $i, $package, $incl_tax ) {
		return [
			'id'          => $this->get_shipping_method_id( $rate->get_id(), $i ),
			'label'       => $this->get_formatted_shipping_label( $price, $rate, $incl_tax ),
			'description' => '',
		];
	}

	/**
	 * @param float            $price
	 * @param WC_Shipping_Rate $rate
	 * @param bool             $incl_tax
	 *
	 * @return string|void
	 */
	protected function get_formatted_shipping_label( $price, $rate, $incl_tax ) {
		$label = sprintf( '%s: %s %s', esc_attr( $rate->get_label() ), number_format( $price, 2 ), get_woocommerce_currency() );
		if ( $incl_tax ) {
			if ( $rate->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
				$label .= ' ' . WC()->countries->inc_tax_or_vat();
			}
		} else {
			if ( $rate->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
				$label .= ' ' . WC()->countries->ex_tax_or_vat();
			}
		}

		return $label;
	}

	/**
	 * @param array $deps
	 * @param       $scripts
	 *
	 * @return array
	 */
	public function get_mini_cart_dependencies( $deps, $scripts ) {
		if ( $this->mini_cart_enabled() ) {
			$deps[] = $scripts->get_handle( 'gpay' );
		}

		return $deps;
	}

	/**
	 * @since 1.0.0
	 * @return mixed|void
	 */
	public function get_payment_button_locale() {
		$locale        = sswps_get_site_locale();
		$button_locale = null;
		if ( 'auto' !== $locale ) {
			$button_locale = substr( $locale, 0, 2 );
			if ( ! in_array( $button_locale, $this->get_supported_button_locales() ) ) {
				$button_locale = null;
			}
		}

		return apply_filters( 'sswps/googlepay_get_button_locale', $button_locale, $this );
	}

	/**
	 * @since 1.0.0
	 * @return mixed|void
	 */
	public function get_supported_button_locales() {
		return apply_filters(
			'sswps/googlepay_supported_button_locales',
			[
				'en',
				'ar',
				'bg',
				'ca',
				'cs',
				'da',
				'de',
				'el',
				'es',
				'et',
				'fi',
				'fr',
				'hr',
				'id',
				'it',
				'ja',
				'ko',
				'ms',
				'nl',
				'no',
				'pl',
				'pt',
				'ru',
				'sk',
				'sl',
				'sr',
				'sv',
				'th',
				'tr',
				'uk',
				'zh',
			]
		);
	}

}
