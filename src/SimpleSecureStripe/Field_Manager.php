<?php
namespace SimpleSecureWP\SimpleSecureStripe;

use SimpleSecureWP\SimpleSecureStripe\Assets\Data as AssetData;
use SimpleSecureWP\SimpleSecureStripe\Checker;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use WC_Order;

/**
 *
 * @since 1.0.0
 * @package Stripe/Classes
 * @author Simple & Secure WP
 *
 */
class Field_Manager {

	private static $_cart_priority = 30;

	private static $_product_button_position;

	public static $_mini_cart_count = 0;

	public static function init_action() {
		self::$_cart_priority = apply_filters( 'sswps/cart_buttons_order', 30 );
		add_action( 'woocommerce_proceed_to_checkout', array( __CLASS__, 'output_cart_fields' ), self::$_cart_priority );
	}

	public static function output_banner_checkout_fields() {
		$gateways = [];
		foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $gateway ) {
			if ( $gateway->supports( 'sswps_banner_checkout' ) && $gateway->banner_checkout_enabled() ) {
				$gateways[ $gateway->id ] = $gateway;
			}
		}
		if ( $gateways ) {
			sswps_get_template( 'checkout/checkout-banner.php', array( 'gateways' => $gateways ) );
		}
	}

	public static function output_checkout_fields() {
		if ( WC()->cart && sswps_pre_orders_active() && \WC_Pre_Orders_Cart::cart_contains_pre_order() && \WC_Pre_Orders_Product::product_is_charged_upon_release( \WC_Pre_Orders_Cart::get_pre_order_product() ) ) {
			App::get( AssetData::class )->print_data( 'sswps_preorder_exists', true );
		}
		do_action( 'sswps/output_checkout_fields' );
	}

	public static function before_add_to_cart() {
		global $product;
		self::$_product_button_position = is_object( $product ) ? $product->get_meta( Constants::BUTTON_POSITION ) : null;
		if ( empty( self::$_product_button_position ) ) {
			self::$_product_button_position = 'bottom';
		}

		if ( 'bottom' == self::$_product_button_position ) {
			$action = 'woocommerce_after_add_to_cart_button';
		} else {
			$action = 'woocommerce_before_add_to_cart_button';
		}
		add_action( $action, array( __CLASS__, 'output_product_checkout_fields' ) );
	}

	public static function output_product_checkout_fields() {
		global $product;
		$gateways        = [];
		$ordering        = $product->get_meta( Constants::PRODUCT_GATEWAY_ORDER );
		$ordering        = ! $ordering ? [] : $ordering;
		$is_subscription = Checker::is_woocommerce_subscriptions_active() && \WC_Subscriptions_Product::is_subscription( $product );
		$is_preorder     = sswps_pre_orders_active() && \WC_Pre_Orders_Product::product_is_charged_upon_release( $product );

		foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $id => $gateway ) {
			/**
			 *
			 * @var Gateways\Abstract_Gateway $gateway
			 */
			if ( $gateway->supports( 'sswps_product_checkout' ) && ! $product->is_type( 'external' ) ) {
				if ( ( $is_subscription && ! $gateway->supports( 'subscriptions' ) ) || ( $is_preorder && ! $gateway->supports( 'pre-orders' ) ) ) {
					continue;
				}
				$option = new Product_Gateway_Option( $product, $gateway );
				if ( $option->enabled() ) {
					if ( isset( $ordering[ $gateway->id ] ) ) {
						$gateways[ $ordering[ $gateway->id ] ] = $gateway;
					} else {
						$gateways[] = $gateway;
					}
				}
			}
		}
		ksort( $gateways );

		if ( count( apply_filters( 'sswps/product_payment_methods', $gateways, $product ) ) > 0 ) {
			sswps_get_template(
				'product/payment-methods.php',
				array(
					'position' => self::$_product_button_position,
					'gateways' => $gateways
				)
			);
		}
	}

	public static function output_cart_fields() {
		$gateways = [];
		foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $id => $gateway ) {
			/**
			 *
			 * @var Gateways\Abstract_Gateway $gateway
			 */
			if ( $gateway->supports( 'sswps_cart_checkout' ) && $gateway->cart_checkout_enabled() ) {
				$gateways[ $gateway->id ] = $gateway;
			}
		}
		if ( count( apply_filters( 'sswps/cart_payment_methods', $gateways ) ) > 0 ) {
			sswps_get_template(
				'cart/payment-methods.php',
				array(
					'gateways'   => $gateways,
					'after'      => self::$_cart_priority > 20,
					'cart_total' => WC()->cart->get_total( 'raw' ),
				)
			);
		}
	}

	public static function mini_cart_buttons() {
		$gateways = [];
		foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $id => $gateway ) {
			/**
			 *
			 * @var Gateways\Abstract_Gateway $gateway
			 */
			if ( $gateway->supports( 'sswps_mini_cart_checkout' ) && $gateway->mini_cart_enabled() ) {
				$gateways[ $gateway->id ] = $gateway;
			}
		}
		if ( count( apply_filters( 'sswps/mini_cart_payment_methods', $gateways ) ) > 0 ) {
			sswps_get_template(
				'mini-cart/payment-methods.php',
				array(
					'gateways' => $gateways
				)
			);
		}
	}

	public static function add_payment_method_fields() {
		sswps_hidden_field( 'billing_first_name', '', WC()->customer->get_first_name() );
		sswps_hidden_field( 'billing_last_name', '', WC()->customer->get_last_name() );
	}

	/**
	 * @param string   $page
	 * @param WC_Order $order
	 */
	public static function output_required_fields( $page, $order = null ) {
		if ( in_array( $page, array( 'cart', 'checkout' ) ) ) {
			if ( 'cart' === $page ) {
				self::output_fields( 'billing' );

				if ( WC()->cart->needs_shipping() ) {
					self::output_fields( 'shipping' );
				}
			}
		} elseif ( 'product' === $page ) {
			global $product;

			self::output_fields( 'billing' );

			if ( $product->needs_shipping() ) {
				self::output_fields( 'shipping' );
			}
		}
	}

	public static function output_fields( $prefix ) {
		$fields = WC()->checkout()->get_checkout_fields( $prefix );
		foreach ( $fields as $key => $field ) {
			printf( '<input type="hidden" id="%1$s" name="%1$s" value="%2$s"/>', esc_attr( $key ), esc_attr( WC()->checkout()->get_value( $key ) ) );
		}
	}

}