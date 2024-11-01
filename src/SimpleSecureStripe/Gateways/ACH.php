<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use WC_Cart;
use WC_Order;

/**
 * Gateway that processes ACH payments.
 * Only available for U.S. based merchants at this time.
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Gateways
 *
 */
class ACH extends Abstract_Gateway {

	use Payment\Traits\Intent;

	protected string $payment_method_type = 'us_bank_account';

	public bool $synchronous = false;

	public function __construct() {
		$this->id                 = 'sswps_ach';
		$this->tab_title          = __( 'ACH', 'simple-secure-stripe' );
		$this->template_name      = 'ach-connections.php';
		$this->token_type         = 'Stripe_ACH';
		$this->method_title       = __( 'ACH', 'simple-secure-stripe' );
		$this->method_description = __( 'ACH gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/ach.svg' );
		parent::__construct();
		$this->settings['charge_type'] = 'capture';
		$this->order_button_text       = $this->get_option( 'order_button_text' );
	}

	public static function init() {
		add_action( 'woocommerce_checkout_update_order_review', [ __CLASS__, 'update_order_review' ] );
		add_action( 'woocommerce_checkout_process', [ __CLASS__, 'add_fees_for_checkout' ] );
	}

	public function get_confirmation_method( $order = null ) {
		return 'automatic';
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Gateway::is_available()
	 */
	public function is_available() {
		$is_available = parent::is_available();
		global $wp;
		if ( isset( $wp->query_vars['order-pay'] ) ) {
			$order = wc_get_order( absint( $wp->query_vars['order-pay'] ) );

			return $is_available && $order && $order->get_currency() === 'USD';
		} elseif ( $this->is_change_payment_method_request() ) {
			return $is_available;
		}

		return $is_available && get_woocommerce_currency() === 'USD';
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Gateway::init_supports()
	 */
	public function init_supports() {
		parent::init_supports();
		unset( $this->supports['add_payment_method'] );
	}

	/**
	 * @inheritDoc
	 */
	public function register_assets() {
		parent::register_assets();

		Assets\Asset::register( 'sswps-ach-connections', 'frontend/ach-connections.js' )
			->add_to_group( 'sswps-local-payment' )
			->set_dependencies( [
				'sswps-stripe-external',
				'sswps-script',
				'wp-polyfill',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_ach_connections_params',
				$this->get_localized_params()
			)
			->set_condition( [ $this, 'is_available' ] );
	}

	public function get_saved_methods_label() {
		return __( 'Saved Banks', 'simple-secure-stripe' );
	}

	public function get_new_method_label() {
		return __( 'New Bank', 'simple-secure-stripe' );
	}

	public function generate_ach_fee_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = [
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => 'max-width: 150px; min-width: 150px;',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => [],
			'options'           => [],
		];
		$data      = wp_parse_args( $data, $defaults );
		ob_start();
		include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/ach-fee.php';

		return ob_get_clean();
	}

	public function validate_ach_fee_field( $key, $value ) {
		$value = empty( $value ) ? [
			'type'    => 'none',
			'taxable' => 'no',
			'value'   => '0',
		] : $value;
		if ( ! isset( $value['taxable'] ) ) {
			$value['taxable'] = 'no';
		}

		return $value;
	}

	public function fees_enabled() {
		$fee = $this->get_option(
			'fee',
			[
				'type'  => 'none',
				'value' => '0',
			]
		);

		return ! empty( $fee ) && $fee['type'] != 'none'; // @phpstan-ignore-line
	}

	/**
	 *
	 * @param WC_Cart $cart
	 */
	public function calculate_cart_fees( $cart ) {
		$this->calculate_fees( $cart );
	}

	/**
	 *
	 * @param WC_Cart $cart
	 */
	public function calculate_fees( $cart ) {
		$fee     = $this->get_option( 'fee' );
		$taxable = wc_string_to_bool( $fee['taxable'] ); // @phpstan-ignore-line
		switch ( $fee['type'] ) { // @phpstan-ignore-line
			case 'amount':
				$cart->add_fee( __( 'ACH Fee', 'simple-secure-stripe' ), $fee['value'], $taxable ); // @phpstan-ignore-line
				break;
			case 'percent':
				$cart_total = $cart->get_subtotal() + $cart->get_shipping_total() + $cart->get_subtotal_tax() + $cart->get_shipping_tax();
				$cart->add_fee( __( 'ACH Fee', 'simple-secure-stripe' ), $fee['value'] * $cart_total, $taxable ); // @phpstan-ignore-line
				break;
		}
	}

	public static function update_order_review() {
		$payment_method = Request::get_sanitized_var( 'payment_method' );
		if ( ! empty( $payment_method ) && wc_clean( $payment_method ) === 'sswps_ach' ) {
			$payment_method = new ACH();
			if ( $payment_method->fees_enabled() ) {
				add_action( 'woocommerce_cart_calculate_fees', [ $payment_method, 'calculate_cart_fees' ] );
			}
		}
	}

	public static function add_fees_for_checkout() {
		$payment_method = Request::get_sanitized_var( 'payment_method' );
		if ( ! empty( $payment_method ) && wc_clean( $payment_method ) === 'sswps_ach' ) {
			$payment_method = WC()->payment_gateways()->payment_gateways()['sswps_ach'];
			if ( $payment_method && $payment_method->fees_enabled() ) {
				add_action( 'woocommerce_cart_calculate_fees', [ $payment_method, 'calculate_cart_fees' ] );
			}
		}
	}

	public function add_stripe_order_args( &$args, $order ) {
		$args['payment_method_options'] = [
			'us_bank_account' => [
				'verification_method'   => 'instant',
				'financial_connections' => [
					'permissions' => [ 'payment_method' ], //@todo - add balances in future release 'permissions' => array( 'payment_method', 'balances' )
				],
			],
		];
		// check if this was a Plaid bank token. If so, add the mandate
		if ( strpos( $order->get_meta( Constants::PAYMENT_METHOD_TOKEN ), 'ba_' ) !== false ) {
			if ( $this->is_processing_scheduled_payment() ) {
				$ip_address = $order->get_customer_ip_address();
				$user_agent = $order->get_customer_user_agent();
				if ( ! $ip_address ) {
					$ip_address = \WC_Geolocation::get_external_ip_address();
				}

				if ( ! $user_agent ) {
					$user_agent = 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' );
				}
				$args['mandate_data'] = [
					'customer_acceptance' => [
						'type'   => 'online',
						'online' => [
							'ip_address' => $ip_address,
							'user_agent' => $user_agent,
						],
					],
				];
			}
		}
	}

	/**
	 * @param           $intent
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function get_payment_intent_confirmation_args( $intent, $order ) {
		$ip_address = $order->get_customer_ip_address();
		$user_agent = $order->get_customer_user_agent();
		if ( ! $ip_address ) {
			$ip_address = \WC_Geolocation::get_external_ip_address();
		}

		if ( ! $user_agent ) {
			$user_agent = 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' );
		}

		return [
			'mandate_data' => [
				'customer_acceptance' => [
					'type'   => 'online',
					'online' => [
						'ip_address' => $ip_address,
						'user_agent' => $user_agent,
					],
				],
			],
		];
	}

	public function get_mandate_text() {
		return apply_filters(
			'sswps/ach_get_mandate_text', sprintf(
				/* translators: 1: button, 2: business name */
				__(
					'By clicking %1$s, you authorize %2$s to debit the bank account you select for any amount owed for charges arising from your use of %2$s services and/or purchase of products from %2$s, pursuant to %2$s website and terms, until this authorization is revoked. You may amend or cancel this authorization at any time by providing notice to %2$s with 30 (thirty) days notice.',
					'simple-secure-stripe'
				),
				 $this->order_button_text,
				$this->get_option( 'business_name' )
			),
			$this
		);
	}

	public function get_localized_params() {
		return array_merge_recursive(
			parent::get_localized_params(),
			[
				'fees_enabled' => $this->fees_enabled(),
			]
		);
	}

}
