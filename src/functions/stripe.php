<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SimpleSecureWP\SimpleSecureStripe\Admin;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Checker;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Stripe\ApiResource;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Charge;
use SimpleSecureWP\SimpleSecureStripe\Tokens;
use SimpleSecureWP\SimpleSecureStripe\Utils;

/**
 *
 * @since   1.0.0
 *
 * @param array  $args
 *
 * @param string $template_name
 *
 * @package Stripe/Functions
 *          Wrapper for wc_get_template that returns Stripe specific templates.
 */
function sswps_get_template( $template_name, $args = [] ) {
	wc_get_template( $template_name, $args, App::get( Plugin::class )->template_path(), App::get( Plugin::class )->get_view_path() );
}

/**
 *
 *
 * Wrapper for wc_get_template_html that returns Stripe specific templates in an html string.
 *
 * @since   1.0.0
 *
 * @param array  $args
 *
 * @param string $template_name
 *
 * @return string
 * @package Stripe/Functions
 */
function sswps_get_template_html( $template_name, $args = [] ) {
	return wc_get_template_html( $template_name, $args, App::get( Plugin::class )->template_path(), App::get( Plugin::class )->get_view_path() );
}

/**
 *
 * @param Gateways\Abstract_Gateway $gateway
 *
 * @package Stripe/Functions
 */
function sswps_token_field( $gateway ) {
	sswps_hidden_field( $gateway->token_key, 'sswps-token-field' );
}

/**
 *
 * @param Gateways\Abstract_Gateway $gateway
 *
 * @package Stripe/Functions
 */
function sswps_payment_intent_field( $gateway ) {
	sswps_hidden_field( $gateway->payment_intent_key, 'sswps-payment-intent-field' );
}

/**
 *
 * @param string $id
 * @param string $class
 * @param string $value
 *
 * @package Stripe/Functions
 */
function sswps_hidden_field( $id, $class = '', $value = '' ) {
	printf( '<input type="hidden" class="%1$s" id="%2$s" name="%2$s" value="%3$s"/>', $class, esc_attr( $id ), esc_attr( $value ) );
}

/**
 * Return the mode for the plugin.
 *
 * @return string
 * @package Stripe/Functions
 */
function sswps_mode() {
	return apply_filters( 'sswps/mode', App::get( Admin\Settings\API::class )->get_option( 'mode' ) );
}

function sswps_test_mode() {
	return sswps_mode() === 'test';
}

/**
 * Return the secret key for the provided mode.
 * If no mode given, the key for the active mode is returned.
 *
 * @since   1.0.0
 *
 * @param string $mode
 *
 * @package Stripe/Functions
 */
function sswps_get_secret_key( $mode = '' ) {
	$mode = empty( $mode ) ? sswps_mode() : $mode;

	return apply_filters( 'sswps/get_secret_key', App::get( Admin\Settings\API::class )->get_option( "secret_key_{$mode}" ), $mode );
}

/**
 * Return the publishable key for the provided mode.
 * If no mode given, the key for the active mode is returned.
 *
 * @since   1.0.0
 *
 * @param string $mode
 *
 * @package Stripe/Functions
 */
function sswps_get_publishable_key( $mode = '' ) {
	$mode = empty( $mode ) ? sswps_mode() : $mode;

	return apply_filters( 'sswps/get_publishable_key', App::get( Admin\Settings\API::class )->get_option( "publishable_key_{$mode}" ), $mode );
}

/**
 * Return the merchant's Stripe account.
 *
 * @since   1.0.0
 * @return string
 * @package Stripe/Functions
 */
function sswps_get_account_id() {
	return apply_filters( 'sswps/get_account_id', App::get( Admin\Settings\API::class )->get_account_id() );
}

/**
 * Return the stripe customer ID
 *
 * @since   1.0.0
 *
 * @param int|string $user_id
 * @param string     $mode
 *
 * @package Stripe/Functions
 */
function sswps_get_customer_id( $user_id = '', $mode = '' ) {
	$mode = empty( $mode ) ? sswps_mode() : $mode;
	if ( $user_id === 0 ) {
		return '';
	}
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	/**
	 * @since 1.0.0
	 *
	 * @param string $option
	 * @param int    $user_id
	 * @param string $mode
	 */
	return apply_filters( 'sswps/get_customer_id', get_user_option( "sswps_customer_{$mode}", $user_id ), $user_id, $mode );
}

/**
 *
 * @param string $customer_id
 * @param int    $user_id
 * @param string $mode
 *
 * @package Stripe/Functions
 */
function sswps_save_customer( $customer_id, $user_id, $mode = '' ) {
	$mode = empty( $mode ) ? sswps_mode() : $mode;
	$key  = "sswps_customer_{$mode}";
	update_user_option( $user_id, $key, apply_filters( 'sswps/save_customer', $customer_id, $user_id, $mode ) );
}

/**
 * @since 1.0.0
 *
 * @param string $mode
 * @param bool   $global
 *
 * @param int    $user_id
 */
function sswps_delete_customer( $user_id, $mode = '', $global = false ) {
	$mode = empty( $mode ) ? sswps_mode() : $mode;
	delete_user_option( $user_id, "sswps_customer_{$mode}", $global );
}

/**
 *
 * @since   1.0.0
 *
 * @param WC_Payment_Token $token
 *
 * @param int              $token_id
 *
 * @package Stripe/Functions
 */
function sswps_woocommerce_payment_token_deleted( $token_id, $token ) {
	if ( ! did_action( 'woocommerce_payment_gateways' ) ) {
		WC_Payment_Gateways::instance();
	}
	/**
	 * @since 1.0.0
	 */
	if ( is_account_page() ) {
		do_action( 'sswps/payment_token_deleted_' . $token->get_gateway_id(), $token_id, $token );
	}
}

/**
 * Log the provided message in the WC logs directory.
 *
 * @since   1.0.0
 *
 * @param string     $message
 * @param int|string $level
 *
 * @package Stripe/Functions
 */
function sswps_log( $level, $message ) {
	if ( App::get( Admin\Settings\API::class )->is_active( 'debug_log' ) ) {
		$log = wc_get_logger();
		$log->log( $level, $message, [ 'source' => 'sswps' ] );
	}
}

/**
 *
 * @since   1.0.0
 *
 * @param string $message
 *
 * @package Stripe/Functions
 */
function sswps_log_error( $message ) {
	sswps_log( WC_Log_Levels::ERROR, $message );
}

/**
 *
 * @since   1.0.0
 *
 * @param string $message
 *
 * @package Stripe/Functions
 */
function sswps_log_info( $message ) {
	sswps_log( WC_Log_Levels::INFO, $message );
}

/**
 * Return the mode that the order was created in.
 * Values can be <strong>live</strong> or <strong>test</strong>
 *
 * @since   1.0.0
 *
 * @param WC_Order|int $order
 *
 * @package Stripe/Functions
 */
function sswps_order_mode( $order ) {
	if ( ! is_object( $order ) ) {
		$order = wc_get_order( $order );
	}

	return $order->get_meta( Constants::MODE, true );
}

/**
 *
 * @since   1.0.0
 *
 * @param array $gateways
 *
 * @package Stripe\Functions
 */
function sswps_payment_gateways( $gateways ) {
	return array_merge( $gateways, App::get( Gateways\Controller::class )->get_gateways() );
}

/**
 * Cancel the Stripe charge
 *
 * @param int      $order_id
 * @param WC_Order $order
 *
 * @package Stripe/Functions
 */
function sswps_order_cancelled( $order_id, $order ) {
	if ( App::get( Admin\Settings\Advanced::class )->is_refund_cancel_enabled() ) {
		$gateways = WC()->payment_gateways()->payment_gateways();
		/**
		 *
		 * @var WC_Payment_Gateway $gateway
		 */
		$gateway = isset( $gateways[ $order->get_payment_method() ] ) ? $gateways[ $order->get_payment_method() ] : null;

		if ( $gateway instanceof Gateways\Abstract_Gateway ) {
			$gateway->void_charge( $order );
		}
	}
}

/**
 *
 * @since   1.0.0
 *
 * @param WC_Order $order
 *
 * @param int      $order_id
 *
 * @package Stripe/Functions
 */
function sswps_order_status_completed( $order_id, $order ) {
	$gateways = WC()->payment_gateways()->payment_gateways();
	/**
	 *
	 * @var WC_Payment_Gateway $gateway
	 */
	$gateway = isset( $gateways[ $order->get_payment_method() ] ) ? $gateways[ $order->get_payment_method() ] : null;
	// @since 1.0.0
	if ( $gateway instanceof Gateways\Abstract_Gateway && ! $gateway->processing_payment ) {
		if ( App::get( Admin\Settings\Advanced::class )->get_option( 'capture_status', 'completed' ) === $order->get_status() ) {
			$gateway->capture_charge( $order->get_total( 'raw' ), $order );
		}
	}
}

/**
 *
 * @since   1.0.0
 *
 * @param array $address
 *
 * @throws Exception
 * @package Stripe/Functions
 */
function sswps_update_customer_location( $address ) {
	// address validation for countries other than US is problematic when using responses from payment sources like
	// Apple Pay.
	if ( $address['postcode'] && $address['country'] === 'US' && ! WC_Validation::is_postcode( $address['postcode'], $address['country'] ) ) {
		throw new Exception( __( 'Please enter a valid postcode / ZIP.', 'simple-secure-stripe' ) );
	} elseif ( $address['postcode'] ) {
		$address['postcode'] = wc_format_postcode( $address['postcode'], $address['country'] );
	}

	if ( $address['country'] ) {
		WC()->customer->set_billing_location( $address['country'], $address['state'], $address['postcode'], $address['city'] );
		WC()->customer->set_shipping_location( $address['country'], $address['state'], $address['postcode'], $address['city'] );
		// set the customer's address if it's in the $address array
		if ( ! empty( $address['address_1'] ) ) {
			WC()->customer->set_shipping_address_1( wc_clean( $address['address_1'] ) );
		}
		if ( ! empty( $address['address_2'] ) ) {
			WC()->customer->set_shipping_address_2( wc_clean( $address['address_2'] ) );
		}
		if ( ! empty( $address['first_name'] ) ) {
			WC()->customer->set_shipping_first_name( $address['first_name'] );
		}
		if ( ! empty( $address['last_name'] ) ) {
			WC()->customer->set_shipping_last_name( $address['last_name'] );
		}
	} else {
		WC()->customer->set_billing_address_to_base();
		WC()->customer->set_shipping_address_to_base();
	}

	WC()->customer->set_calculated_shipping( true );
	WC()->customer->save();

	do_action( 'woocommerce_calculated_shipping' );
}

/**
 *
 * @since   1.0.0
 *
 * @param array $methods
 *
 * @package Stripe/Functions
 */
function sswps_update_shipping_methods( $methods ) {
	$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods', [] );

	foreach ( $methods as $i => $method ) {
		$chosen_shipping_methods[ $i ] = $method;
	}

	WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
}

/**
 * Return true if there are shipping packages that contain rates.
 *
 * @since   1.0.0
 *
 * @param array $packages
 *
 * @return boolean
 * @package Stripe/Functions
 */
function sswps_shipping_address_serviceable( $packages = [] ) {
	if ( $packages ) {
		foreach ( $packages as $package ) {
			if ( count( $package['rates'] ) > 0 ) {
				return true;
			}
		}
	}

	return false;
}

/**
 *
 * @since   1.0.0
 * @package Stripe/Functions
 */
function sswps_set_checkout_error() {
	add_action( 'woocommerce_after_template_part', 'sswps_output_checkout_error' );
}

/**
 *
 * @since   1.0.0
 *
 * @param string $template_name
 *
 * @package Stripe/Functions
 */
function sswps_output_checkout_error( $template_name ) {
	if ( $template_name === 'notices/error.php' && is_ajax() ) {
		echo '<input type="hidden" id="sswps_checkout_error" value="true"/>';
		remove_action( 'woocommerce_after_template_part', 'sswps_output_checkout_error' );
		add_filter( 'wp_kses_allowed_html', 'sswps_add_allowed_html', 10, 2 );
	}
}

/**
 *
 * @since   1.0.0
 * @package Stripe/Functions
 */
function sswps_add_allowed_html( $tags, $context ) {
	if ( $context === 'post' ) {
		$tags['input'] = [
			'id'    => true,
			'type'  => true,
			'value' => true,
		];
	}

	return $tags;
}

/**
 * Save WCS meta data when it's changed in the admin section.
 * By default WCS saves the
 * payment method title as the gateway title. This method saves the payment method title in
 * a human readable format suitable for the frontend.
 *
 * @param int     $post_id
 * @param WP_Post $post
 *
 * @package Stripe/Functions
 */
function sswps_process_shop_subscription_meta( $post_id, $post ) {
	$subscription = wcs_get_subscription( $post_id );
	$gateway_id   = $subscription->get_payment_method();
	$gateways     = WC()->payment_gateways()->payment_gateways();
	if ( isset( $gateways[ $gateway_id ] ) ) {
		$gateway = $gateways[ $gateway_id ];
		if ( $gateway instanceof Gateways\Abstract_Gateway ) {
			$token = $gateway->get_token( $subscription->get_meta( Constants::PAYMENT_METHOD_TOKEN ), $subscription->get_customer_id() );
			if ( $token ) {
				$subscription->set_payment_method_title( $token->get_payment_method_title() );
				$subscription->save();
			}
		}
	}
}

/**
 * Filter the WC payment gateways based on criteria specific to Stripe functionality.
 *
 * <strong>Example:</strong> on add payment method page, only show the CC gateway for Stripe.
 *
 * @since   1.0.0
 *
 * @param WC_Payment_Gateway[] $gateways
 *
 * @package Stripe/Functions
 */
function sswps_available_payment_gateways( $gateways ) {
	global $wp;
	if ( is_add_payment_method_page() && ! isset( $wp->query_vars['payment-methods'] ) ) {
		foreach ( $gateways as $gateway ) {
			if ( $gateway instanceof Gateways\Abstract_Gateway ) {
				if ( 'sswps_cc' !== $gateway->id ) {
					unset( $gateways[ $gateway->id ] );
				}
			}
		}
	}

	return $gateways;
}

/**
 *
 * @since   1.0.0
 * @return array
 * @package Stripe/Functions
 */
function sswps_get_local_payment_params() {
	global $wp;
	$data     = [];
	$gateways = WC()->payment_gateways()->payment_gateways();
	foreach ( $gateways as $gateway ) {
		if ( $gateway instanceof Gateways\Abstract_Local_Payment && $gateway->is_available() ) {
			$data['gateways'][ $gateway->id ] = $gateway->get_localized_params();
			if ( isset( $wp->query_vars['order-pay'] ) ) {
				$data['gateways'][ $gateway->id ]['order_id'] = $wp->query_vars['order-pay'];
			}
		}
	}
	$data['api_key'] = sswps_get_publishable_key();

	return $data;
}

/**
 *
 * @since   1.0.0
 *
 * @param array $gateways
 *
 * @return WC_Payment_Gateway[]
 * @package Stripe/Functions
 */
function sswps_get_available_local_gateways( $gateways ) {
	foreach ( $gateways as $gateway ) {
		if ( $gateway instanceof Gateways\Abstract_Local_Payment ) {
			if ( ! $gateway->is_local_payment_available() ) {
				unset( $gateways[ $gateway->id ] );
			}
		}
	}

	return $gateways;
}

/**
 *
 * @since   1.0.0
 *
 * @param string|int $key
 *
 * @package Stripe/Functions
 */
function sswps_set_idempotency_key( $key ) {
	global $sswps_idempotency_key;
	$sswps_idempotency_key = $key;
}

/**
 *
 * @since   1.0.0
 * @return mixed
 * @package Stripe/Functions
 */
function sswps_get_idempotency_key() {
	global $sswps_idempotency_key;

	return $sswps_idempotency_key;
}

/**
 *
 * @since   1.0.0
 *
 * @param array $options
 *
 * @return array
 * @package Stripe/Functions
 */
function sswps_api_options( $options ) {
	$key = sswps_get_idempotency_key();
	if ( $key ) {
		$options['idempotency_key'] = $key;
	}

	return $options;
}

/**
 *
 * @since   1.0.0
 * <br/><strong>3.1.7</strong> - default $order argument of null added to prevent errors when 3rd party plugins trigger
 * action woocommerce_payment_complete_order_status and don't pass three arguments.
 *
 * @param int      $order_id
 * @param WC_Order $order
 *
 * @param string   $order_status
 *
 * @package Stripe/Functions
 */
function sswps_payment_complete_order_status( $order_status, $order_id, $order = null ) {
	if ( is_checkout() && $order && $order->get_payment_method() ) {
		$gateway = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ];
		if ( $gateway instanceof Gateways\Abstract_Gateway && 'default' !== $gateway->get_option( 'order_status', 'default' ) ) {
			$order_status = $gateway->get_option( 'order_status' );
		}
	}

	return $order_status;
}

/**
 * Return an array of credit card forms.
 *
 * @since   1.0.0
 * @return mixed
 * @package Stripe/Functions
 */
function sswps_get_custom_forms() {
	return apply_filters(
		'sswps_get_custom_forms',
		[
			'bootstrap'  => [
				'template'       => 'cc-forms/bootstrap.php',
				'label'          => __( 'Bootstrap form', 'simple-secure-stripe' ),
				'cardBrand'      => App::get( Plugin::class )->assets_url( 'img/card_brand2.svg' ),
				'elementStyles'  => [
					'base'    => [
						'color'             => '#495057',
						'fontWeight'        => 300,
						'fontFamily'        => 'Roboto, sans-serif, Source Code Pro, Consolas, Menlo, monospace',
						'fontSize'          => '16px',
						'fontSmoothing'     => 'antialiased',
						'::placeholder'     => [
							'color'    => '#fff',
							'fontSize' => '0px',
						],
						':-webkit-autofill' => [ 'color' => '#495057' ],
					],
					'invalid' => [
						'color'         => '#E25950',
						'::placeholder' => [ 'color' => '#757575' ],
					],
				],
				'elementOptions' => [
					'fonts' => [ [ 'cssSrc' => 'https://fonts.googleapis.com/css?family=Source+Code+Pro' ] ],
				],
			],
			'simple'     => [
				'template'       => 'cc-forms/simple.php',
				'label'          => __( 'Simple form', 'simple-secure-stripe' ),
				'cardBrand'      => App::get( Plugin::class )->assets_url( 'img/card_brand2.svg' ),
				'elementStyles'  => [
					'base'    => [
						'color'             => '#32325D',
						'fontWeight'        => 500,
						'fontFamily'        => 'Source Code Pro, Consolas, Menlo, monospace',
						'fontSize'          => '16px',
						'fontSmoothing'     => 'antialiased',
						'::placeholder'     => [ 'color' => '#CFD7DF' ],
						':-webkit-autofill' => [ 'color' => '#32325D' ],
					],
					'invalid' => [
						'color'         => '#E25950',
						'::placeholder' => [ 'color' => '#FFCCA5' ],
					],
				],
				'elementOptions' => [
					'fonts' => [ [ 'cssSrc' => 'https://fonts.googleapis.com/css?family=Source+Code+Pro' ] ],
				],
			],
			'minimalist' => [
				'template'       => 'cc-forms/minimalist.php',
				'label'          => __( 'Minimalist form', 'simple-secure-stripe' ),
				'cardBrand'      => App::get( Plugin::class )->assets_url( 'img/card_brand2.svg' ),
				'elementStyles'  => [
					'base'    => [
						'color'             => '#495057',
						'fontWeight'        => 300,
						'fontFamily'        => 'Roboto, sans-serif, Source Code Pro, Consolas, Menlo, monospace',
						'fontSize'          => '30px',
						'fontSmoothing'     => 'antialiased',
						'::placeholder'     => [
							'color'    => '#fff',
							'fontSize' => '0px',
						],
						':-webkit-autofill' => [ 'color' => '#495057' ],
					],
					'invalid' => [
						'color'         => '#495057',
						'::placeholder' => [ 'color' => '#495057' ],
					],
				],
				'elementOptions' => [
					'fonts' => [ [ 'cssSrc' => 'https://fonts.googleapis.com/css?family=Source+Code+Pro' ] ],
				],
			],
			'inline'     => [
				'template'       => 'cc-forms/inline.php',
				'label'          => __( 'Inline Form', 'simple-secure-stripe' ),
				'cardBrand'      => App::get( Plugin::class )->assets_url( 'img/card_brand.svg' ),
				'elementStyles'  => [
					'base'    => [
						'color'               => '#819efc',
						'fontWeight'          => 600,
						'fontFamily'          => 'Roboto, Open Sans, Segoe UI, sans-serif',
						'fontSize'            => '16px',
						'fontSmoothing'       => 'antialiased',
						':focus'              => [ 'color' => '#819efc' ],
						'::placeholder'       => [ 'color' => '#87BBFD' ],
						':focus::placeholder' => [ 'color' => '#CFD7DF' ],
						':-webkit-autofill'   => [ 'color' => '#819efc' ],
					],
					'invalid' => [ 'color' => '#f99393' ],
				],
				'elementOptions' => [
					'fonts' => [ [ 'cssSrc' => 'https://fonts.googleapis.com/css?family=Roboto' ] ],
				],
			],
			'rounded'    => [
				'template'       => 'cc-forms/round.php',
				'label'          => __( 'Rounded Form', 'simple-secure-stripe' ),
				'cardBrand'      => App::get( Plugin::class )->assets_url( 'img/card_brand.svg' ),
				'elementStyles'  => [
					'base'    => [
						'color'               => '#fff',
						'fontWeight'          => 600,
						'fontFamily'          => 'Quicksand, Open Sans, Segoe UI, sans-serif',
						'fontSize'            => '16px',
						'fontSmoothing'       => 'antialiased',
						':focus'              => [ 'color' => '#424770' ],
						'::placeholder'       => [ 'color' => '#9BACC8' ],
						':focus::placeholder' => [ 'color' => '#CFD7DF' ],
						':-webkit-autofill'   => [ 'color' => '#fff' ],
					],
					'invalid' => [
						'color'         => '#fff',
						':focus'        => [ 'color' => '#FA755A' ],
						'::placeholder' => [ 'color' => '#FFCCA5' ],
					],
				],
				'elementOptions' => [
					'fonts' => [ [ 'cssSrc' => 'https://fonts.googleapis.com/css?family=Quicksand' ] ],
				],
			],
		]
	);
}

/**
 *
 * @since   1.0.0
 *
 * @param WC_Order $order
 *
 * @package Stripe/Functions
 */
function sswps_order_has_shipping_address( $order ) {
	if ( method_exists( $order, 'has_shipping_address' ) ) {
		return $order->has_shipping_address();
	} else {
		return $order->get_shipping_address_1() || $order->get_shipping_address_2();
	}
}

/**
 *
 * @since   1.0.0
 * @package Stripe/Functions
 */
function sswps_display_prices_including_tax() {
	$cart = WC()->cart;
	if ( $cart && method_exists( $cart, 'display_prices_including_tax' ) ) {
		return $cart->display_prices_including_tax();
	}
	if ( $cart && is_callable( [ $cart, 'get_tax_price_display_mode' ] ) ) {
		return 'incl' == $cart->get_tax_price_display_mode() && ( WC()->customer && ! WC()->customer->is_vat_exempt() );
	}

	return 'incl' == $cart->tax_display_cart && ( WC()->customer && ! WC()->customer->is_vat_exempt() );
}

/**
 * Return true if the WC pre-orders plugin is active
 *
 * @since   1.0.0
 * @package Stripe/Functions
 */
function sswps_pre_orders_active() {
	return class_exists( 'WC_Pre_Orders' );
}

/**
 *
 * @since   1.0.0
 *
 * @param string $source_id
 *
 * @package Stripe/Functions
 */
function sswps_get_order_from_source_id( $source_id ) {
	if ( \SimpleSecureWP\SimpleSecureStripe\Utils\FeaturesUtil::is_custom_order_tables_enabled() ) {
		$order_ids = wc_get_orders( [
			'type'       => 'shop_order',
			'limit'      => 1,
			'return'     => 'ids',
			'meta_query' => [
				[
					'key'   => Constants::SOURCE_ID,
					'value' => $source_id,
				],
			],
		] );
		$order_id  = ! empty( $order_ids ) ? $order_ids[0] : null;
	} else {
		global $wpdb;
		$order_id
			= $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID
				FROM {$wpdb->posts} AS posts
				LEFT JOIN {$wpdb->postmeta} AS meta
				  ON posts.ID = meta.post_id
				WHERE posts.post_type = %s
				  AND meta.meta_key = %s
				  AND meta.meta_value = %s
				LIMIT 1",
				'shop_order',
				Constants::SOURCE_ID,
				$source_id
			)
		);
	}

	return wc_get_order( $order_id );
}

/**
 *
 * @since   1.0.0
 *
 * @param string $transaction_id
 *
 * @return WC_Order|WC_Order_Refund|boolean
 * @package Stripe/Functions
 */
function sswps_get_order_from_transaction( $transaction_id ) {
	if ( \SimpleSecureWP\SimpleSecureStripe\Utils\FeaturesUtil::is_custom_order_tables_enabled() ) {
		$order_ids = wc_get_orders( [
			'type'           => 'shop_order',
			'limit'          => 1,
			'return'         => 'ids',
			'transaction_id' => $transaction_id,
		] );
		$order_id  = ! empty( $order_ids ) ? $order_ids[0] : null;
	} else {
		global $wpdb;
		$order_id
			= $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID
				FROM {$wpdb->posts} AS posts
				LEFT JOIN {$wpdb->postmeta} AS meta
				  ON posts.ID = meta.post_id
				WHERE posts.post_type = %s
				  AND meta.meta_key = %s
				  AND meta.meta_value = %s
				LIMIT 1",
				'shop_order',
				'_transaction_id',
				$transaction_id
			)
		);
	}

	return wc_get_order( $order_id );
}

/**
 * Stash the WC cart contents in the session and empty it's contents.
 * If $product_cart is true, add the stashed product(s)
 * to the cart.
 *
 * @since   1.0.0
 *
 * @param bool    $product_cart
 *
 * @param WC_Cart $cart
 *
 * @todo    Maybe empty cart silently so actions are not triggered that cause session data to be removed
 *       from 3rd party plugins.
 *
 * @package Stripe/Functions
 */
function sswps_stash_cart( $cart, $product_cart = true ) {
	$data         = WC()->session->get( 'sswps_cart', [] );
	$data['cart'] = $cart->get_cart_for_session();
	WC()->session->set( 'sswps_cart', $data );
	$cart->empty_cart( false );
	if ( $product_cart && isset( $data['product_cart'] ) ) {
		// if there are args, map them to the request
		if ( isset( $data['request_params'] ) ) {
			foreach ( $data['request_params'] as $key => $value ) {
				$_REQUEST[ $key ] = $value;
			}
		}
		foreach ( $data['product_cart'] as $cart_item ) {
			$cart->add_to_cart( $cart_item['product_id'], $cart_item['quantity'], $cart_item['variation_id'], $cart_item['variation'] );
		}
	}
}

/**
 *
 * @since   1.0.0
 *
 * @param array   $params
 *
 * @param WC_Cart $cart
 *
 * @package Stripe/Functions
 */
function sswps_stash_product_cart( $cart, $params = [] ) {
	$data                   = WC()->session->get( 'sswps_cart', [] );
	$data['product_cart']   = $cart->get_cart_for_session();
	$data['request_params'] = $params;
	WC()->session->set( 'sswps_cart', $data );
	WC()->cart->set_session();
}

/**
 *
 * @since   1.0.0
 *
 * @param WC_Cart $cart
 *
 * @package Stripe/Functions
 */
function sswps_restore_cart( $cart ) {
	$data                = WC()->session->get( 'sswps_cart', [ 'cart' => [] ] );
	$cart->cart_contents = $data['cart'];
	$cart->set_session();
}

/**
 *
 * @since   1.0.0
 * @package Stripe/Functions
 */
function sswps_restore_cart_after_product_checkout() {
	sswps_restore_cart( WC()->cart );
	$cart_contents = [];
	foreach ( WC()->cart->get_cart() as $key => $cart_item ) {
		$cart_item['data']     = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
		$cart_contents[ $key ] = $cart_item;
	}
	WC()->cart->cart_contents = $cart_contents;
	WC()->cart->calculate_totals();
}

/**
 *
 * @since   1.0.0
 *
 * @param int                $user_id
 * @param string             $gateway_id
 *
 * @param WC_Payment_Token[] $tokens
 *
 * @return WC_Payment_Token[]
 * @package Stripe/Functions
 */
function sswps_get_customer_payment_tokens( $tokens, $user_id, $gateway_id ) {
	foreach ( $tokens as $idx => $token ) {
		if ( $token instanceof Tokens\Abstract_Token ) {
			$mode = sswps_mode();
			if ( $token->get_environment() != $mode ) {
				unset( $tokens[ $idx ] );
			}
		}
	}

	return $tokens;
}

/**
 *
 * @since   1.0.0
 *
 * @param array $labels
 *
 * @return array
 * @package Stripe/Functions
 */
function sswps_credit_card_labels( $labels ) {
	if ( ! isset( $labels['amex'] ) ) {
		$labels['amex'] = __( 'Amex', 'simple-secure-stripe' );
	}

	return $labels;
}

/**
 * Return an array of Stripe error messages.
 *
 * @since   1.0.0
 * @package Stripe/Functions
 */
function sswps_get_error_messages() {
	return apply_filters(
		'sswps_get_error_messages',
		[
			'sswps_cc_generic'                                   => __( 'There was an error processing your credit card.', 'simple-secure-stripe' ),
			'incomplete_number'                                   => __( 'Your card number is incomplete.', 'simple-secure-stripe' ),
			'incomplete_expiry'                                   => __( 'Your card\'s expiration date is incomplete.', 'simple-secure-stripe' ),
			'incomplete_cvc'                                      => __( 'Your card\'s security code is incomplete.', 'simple-secure-stripe' ),
			'incomplete_zip'                                      => __( 'Your card\'s zip code is incomplete.', 'simple-secure-stripe' ),
			'incorrect_number'                                    => __( 'The card number is incorrect. Check the card\'s number or use a different card.', 'simple-secure-stripe' ),
			'incorrect_cvc'                                       => __( 'The card\'s security code is incorrect. Check the card\'s security code or use a different card.', 'simple-secure-stripe' ),
			'incorrect_zip'                                       => __( 'The card\'s ZIP code is incorrect. Check the card\'s ZIP code or use a different card.', 'simple-secure-stripe' ),
			'invalid_number'                                      => __( 'The card number is invalid. Check the card details or use a different card.', 'simple-secure-stripe' ),
			'invalid_characters'                                  => __( 'This value provided to the field contains characters that are unsupported by the field.', 'simple-secure-stripe' ),
			'invalid_cvc'                                         => __( 'The card\'s security code is invalid. Check the card\'s security code or use a different card.', 'simple-secure-stripe' ),
			'invalid_expiry_month'                                => __( 'The card\'s expiration month is incorrect. Check the expiration date or use a different card.', 'simple-secure-stripe' ),
			'invalid_expiry_year'                                 => __( 'The card\'s expiration year is incorrect. Check the expiration date or use a different card.', 'simple-secure-stripe' ),
			'incorrect_address'                                   => __( 'The card\'s address is incorrect. Check the card\'s address or use a different card.', 'simple-secure-stripe' ),
			'expired_card'                                        => __( 'The card has expired. Check the expiration date or use a different card.', 'simple-secure-stripe' ),
			'card_declined'                                       => __( 'The card has been declined.', 'simple-secure-stripe' ),
			'invalid_expiry_year_past'                            => __( 'Your card\'s expiration year is in the past.', 'simple-secure-stripe' ),
			'account_number_invalid'                              => __(
				'The bank account number provided is invalid (e.g., missing digits). Bank account information varies from country to country. We recommend creating validations in your entry forms based on the bank account formats we provide.',
				'simple-secure-stripe'
			),
			'amount_too_large'                                    => __( 'The specified amount is greater than the maximum amount allowed. Use a lower amount and try again.', 'simple-secure-stripe' ),
			'amount_too_small'                                    => __( 'The specified amount is less than the minimum amount allowed. Use a higher amount and try again.', 'simple-secure-stripe' ),
			'authentication_required'                             => __(
				'The payment requires authentication to proceed. If your customer is off session, notify your customer to return to your application and complete the payment. If you provided the error_on_requires_action parameter, then your customer should try another card that does not require authentication.',
				'simple-secure-stripe'
			),
			'balance_insufficient'                                => __(
				'The transfer or payout could not be completed because the associated account does not have a sufficient balance available. Create a new transfer or payout using an amount less than or equal to the account\'s available balance.',
				'simple-secure-stripe'
			),
			'bank_account_declined'                               => __( 'The bank account provided can not be used to charge, either because it is not verified yet or it is not supported.', 'simple-secure-stripe' ),
			'bank_account_exists'                                 => __(
				'The bank account provided already exists on the specified Customer object. If the bank account should also be attached to a different customer, include the correct customer ID when making the request again.',
				'simple-secure-stripe'
			),
			'bank_account_unusable'                               => __( 'The bank account provided cannot be used for payouts. A different bank account must be used.', 'simple-secure-stripe' ),
			'bank_account_unverified'                             => __( 'Your Connect platform is attempting to share an unverified bank account with a connected account.', 'simple-secure-stripe' ),
			'bank_account_verification_failed'                    => __(
				'The bank account cannot be verified, either because the microdeposit amounts provided do not match the actual amounts, or because verification has failed too many times.',
				'simple-secure-stripe'
			),
			'card_decline_rate_limit_exceeded'                    => __(
				'This card has been declined too many times. You can try to charge this card again after 24 hours. We suggest reaching out to your customer to make sure they have entered all of their information correctly and that there are no issues with their card.',
				'simple-secure-stripe'
			),
			'charge_already_captured'                             => __( 'The charge you\'re attempting to capture has already been captured. Update the request with an uncaptured charge ID.', 'simple-secure-stripe' ),
			'charge_already_refunded'                             => __(
				'The charge you\'re attempting to refund has already been refunded. Update the request to use the ID of a charge that has not been refunded.',
				'simple-secure-stripe'
			),
			'charge_disputed'                                     => __(
				'The charge you\'re attempting to refund has been charged back. Check the disputes documentation to learn how to respond to the dispute.',
				'simple-secure-stripe'
			),
			'charge_exceeds_source_limit'                         => __(
				'This charge would cause you to exceed your rolling-window processing limit for this source type. Please retry the charge later, or contact us to request a higher processing limit.',
				'simple-secure-stripe'
			),
			'charge_expired_for_capture'                          => __(
				'The charge cannot be captured as the authorization has expired. Auth and capture charges must be captured within seven days.',
				'simple-secure-stripe'
			),
			'charge_invalid_parameter'                            => __(
				'One or more provided parameters was not allowed for the given operation on the Charge. Check our API reference or the returned error message to see which values were not correct for that Charge.',
				'simple-secure-stripe'
			),
			'email_invalid'                                       => __(
				'The email address is invalid (e.g., not properly formatted). Check that the email address is properly formatted and only includes allowed characters.',
				'simple-secure-stripe'
			),
			'idempotency_key_in_use'                              => __(
				'The idempotency key provided is currently being used in another request. This occurs if your integration is making duplicate requests simultaneously.',
				'simple-secure-stripe'
			),
			'invalid_charge_amount'                               => __(
				'The specified amount is invalid. The charge amount must be a positive integer in the smallest currency unit, and not exceed the minimum or maximum amount.',
				'simple-secure-stripe'
			),
			'invalid_source_usage'                                => __(
				'The source cannot be used because it is not in the correct state (e.g., a charge request is trying to use a source with a pending, failed, or consumed source). Check the status of the source you are attempting to use.',
				'simple-secure-stripe'
			),
			'missing'                                             => __(
				'Both a customer and source ID have been provided, but the source has not been saved to the customer. To create a charge for a customer with a specified source, you must first save the card details.',
				'simple-secure-stripe'
			),
			'postal_code_invalid'                                 => __( 'The ZIP code provided was incorrect.', 'simple-secure-stripe' ),
			'processing_error'                                    => __( 'An error occurred while processing the card. Try again later or with a different payment method.', 'simple-secure-stripe' ),
			'card_not_supported'                                  => __( 'The card does not support this type of purchase.', 'simple-secure-stripe' ),
			'call_issuer'                                         => __( 'The card has been declined for an unknown reason.', 'simple-secure-stripe' ),
			'card_velocity_exceeded'                              => __( 'The customer has exceeded the balance or credit limit available on their card.', 'simple-secure-stripe' ),
			'currency_not_supported'                              => __( 'The card does not support the specified currency.', 'simple-secure-stripe' ),
			'do_not_honor'                                        => __( 'The card has been declined for an unknown reason.', 'simple-secure-stripe' ),
			'fraudulent'                                          => __( 'The payment has been declined as Stripe suspects it is fraudulent.', 'simple-secure-stripe' ),
			'generic_decline'                                     => __( 'The card has been declined for an unknown reason.', 'simple-secure-stripe' ),
			'incorrect_pin'                                       => __( 'The PIN entered is incorrect. ', 'simple-secure-stripe' ),
			'insufficient_funds'                                  => __( 'The card has insufficient funds to complete the purchase.', 'simple-secure-stripe' ),
			'empty_element'                                       => __( 'Please select a payment method before proceeding.', 'simple-secure-stripe' ),
			'empty_element_sepa_debit'                            => __( 'Please enter your IBAN before proceeding.', 'simple-secure-stripe' ),
			'empty_element_ideal'                                 => __( 'Please select a bank before proceeding', 'simple-secure-stripe' ),
			'incomplete_iban'                                     => __( 'The IBAN you entered is incomplete.', 'simple-secure-stripe' ),
			'incomplete_boleto_tax_id'                            => __( 'Please enter a valid CPF / CNPJ', 'simple-secure-stripe' ),
			'test_mode_live_card'                                 => __(
				'Your card was declined. Your request was in test mode, but you used a real credit card. Only test cards can be used in test mode.',
				'simple-secure-stripe'
			),
			'server_side_confirmation_beta'                       => __( 'You do not have permission to use the PaymentElement card form. Please send a request to https://support.stripe.com/ and ask for the "server_side_confirmation_beta" to be added to your account.', 'simple-secure-stripe' ),
			'phone_required'                                      => __( 'Please provide a billing phone number.', 'simple-secure-stripe' ),
			'ach_instant_only'                                    => __( 'Your payment could not be processed at this time because your bank account does not support instant verification.', 'simple-secure-stripe' ),
			'payment_intent_konbini_rejected_confirmation_number' => __( 'The confirmation number was rejected by Konbini. Please try again.', 'simple-secure-stripe' ),
			'payment_intent_payment_attempt_expired'              => __( 'The payment attempt for this payment method has expired. Please try again.', 'simple-secure-stripe' ),
		]
	);
}

/**
 * Function that triggers a filter on the order id.
 * Allows 3rd parties to
 * convert the order_id from the metadata of the Stripe object.
 *
 * @since   1.0.0
 *
 * @param ApiResource|Charge $object
 *
 * @param int                $order_id
 *
 * @package Stripe/Functions
 */
function sswps_filter_order_id( $order_id, $object ) {
	return apply_filters( 'sswps/filter_order_id', $order_id, $object );
}

/**
 * Removes order locks that have expired so the options table does not get cluttered with transients.
 *
 * @since    1.0.0
 * @package  Stripe/Functions
 */
function sswps_remove_order_locks() {
	global $wpdb;

	// this operation could take some time, ensure it completes.
	wc_set_time_limit();

	$results = $wpdb->get_results( $wpdb->prepare(
		"SELECT option_name
		FROM $wpdb->options
		WHERE option_name LIKE %s
		AND option_value < %d",
		'_transient_timeout_stripe_lock_order_%',
		time()
	) );
	if ( $results ) {
		foreach ( $results as $result ) {
			// call delete_transient so WordPress can fire all it's transient actions.
			delete_transient( substr( $result->option_name, strlen( '_transient_timeout_' ) ) );
		}
	}
}

/**
 * Returns an array of checkout fields needed to complete an order.
 *
 * @since 1.0.0
 * @return array
 */
function sswps_get_checkout_fields() {
	global $wp;
	$fields = [];
	$order  = false;
	if ( ! empty( $wp->query_vars['order-pay'] ) ) {
		$order = wc_get_order( absint( ( $wp->query_vars['order-pay'] ) ) );
	}
	foreach ( [ 'billing', 'shipping' ] as $key ) {
		if ( ( $field_set = WC()->checkout()->get_checkout_fields( $key ) ) ) {
			$fields = array_merge( $fields, $field_set );
		}
	}
	// loop through fields and assign their value to the field.
	array_walk( $fields, function( &$field, $key ) use ( $order ) {
		if ( $order ) {
			if ( is_callable( [ $order, "get_{$key}" ] ) ) {
				$field['value'] = $order->{"get_{$key}"}();
			} else {
				$field['value'] = WC()->checkout()->get_value( $key );
			}
		} else {
			$field['value'] = WC()->checkout()->get_value( $key );
		}
		/**
		 * Some 3rd party plugins hook in to WC filters and alter the expected
		 * type for required. This ensures it's converted back to a boolean.
		 */
		if ( isset( $field['required'] ) && ! is_bool( $field['required'] ) ) {
			$field['required'] = (bool) $field['required'];
		}
	} );
	do_action( 'sswps/after_get_checkout_fields', $fields );

	return $fields;
}

/**
 * Filters a state value, making sure the abbreviated state value recognized by WC is returned.
 * Example: Texas = TX
 *
 * @since 1.0.0
 *
 * @param string $country
 *
 * @param string $state
 *
 * @return string
 *
 */
function sswps_filter_address_state( $state, $country ) {
	$states = WC()->countries ? WC()->countries->get_states( $country ) : [];
	if ( ! empty( $states ) && is_array( $states ) && ! isset( $states[ $state ] ) ) {
		$state_keys = array_flip( array_map( 'strtoupper', $states ) );
		if ( isset( $state_keys[ strtoupper( $state ) ] ) ) {
			$state = $state_keys[ strtoupper( $state ) ];
		}
	}

	return $state;
}

/**
 * @since 1.0.0
 * @retun string
 */
function sswps_get_current_page() {
	global $wp;
	if ( is_product() ) {
		return 'product';
	}
	if ( is_cart() ) {
		return 'cart';
	}
	if ( is_checkout() ) {
		if ( ! empty( $wp->query_vars['order-pay'] ) ) {
			if ( Checker::is_woocommerce_subscriptions_active() && \WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment ) {
				return 'change_payment_method';
			}

			return 'order_pay';
		}

		return 'checkout';
	}
	if ( is_add_payment_method_page() ) {
		return 'add_payment_method';
	}

	return '';
}

/**
 * @since 1.0.0
 * @return mixed|void
 */
function sswps_get_site_locale() {
	if ( App::get( Admin\Settings\Advanced::class )->get_option( 'locale', 'auto' ) !== 'auto' ) {
		if ( ( $locale = get_locale() ) ) {
			$locale = str_replace( '_', '-', substr( $locale, 0, 5 ) );
		} else {
			$locale = 'auto';
		}
	} else {
		$locale = 'auto';
	}

	return apply_filters( 'sswps/get_site_locale', $locale );
}
