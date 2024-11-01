<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets;
use SimpleSecureWP\SimpleSecureStripe\REST;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Source;
use SimpleSecureWP\SimpleSecureStripe\Tokens;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WC_Order;

/**
 * Local payment method classes should extend this abstract class
 *
 * @package Stripe/Abstract
 * @author Simple & Secure WP
 *
 */
abstract class Abstract_Local_Payment extends Abstract_Gateway {

	protected $tab_title = '';

	/**
	 * Currencies this gateway accepts
	 *
	 * @var array
	 */
	public $currencies = [];

	public $local_payment_type = '';

	public $countries = [];

	/**
	 * @since 1.0.0
	 * @var array
	 */
	public $limited_countries = [];

	protected $local_payment_description = '';

	public string $token_type = 'Stripe_Local';

	public function __construct() {
		$this->template_name = 'local-payment.php';
		parent::__construct();

		if ( ! isset( $this->form_fields['method_format'] ) ) {
			$this->settings['method_format'] = 'gateway_title';
		}
		if ( ! isset( $this->form_fields['charge_type'] ) ) {
			$this->settings['charge_type'] = 'capture';
		}

		$this->settings['order_status'] = 'default';
		$this->order_button_text        = $this->get_option( 'order_button_text' );

		$this->register_assets();
	}

	public function hooks() {
		parent::hooks();
		add_filter( 'sswps/local_gateway_tabs', [ $this, 'local_gateway_tab' ] );
		remove_filter( 'sswps/settings_nav_tabs', [ $this, 'admin_nav_tab' ] );
		add_filter( 'sswps/local_gateways_tab', [ $this, 'admin_nav_tab' ] );
	}

	/**
	 * Registers assets for local payments.
	 */
	public function register_assets() {
		Assets\Asset::register( 'sswps-local-payment', 'frontend/local-payment.js' )
			->add_to_group( 'sswps-local-payment' )
			->set_dependencies( [
				'sswps-stripe-external',
				'sswps-script',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->set_condition( static function () {
				return is_checkout();
			} );
	}

	/**
	 * @since 1.0.0
	 */
	public function enqueue_checkout_scripts() {
		$scripts = App::get( Assets\Assets::class );
		$scripts->enqueue_local_payment_scripts();
	}

	/**
	 *
	 * @param Source   $source
	 * @param WC_Order $order
	 */
	public function get_source_redirect_url( $source, $order ) {
		return $source->redirect->offsetGet( 'url' );
	}

	public function output_settings_nav() {
		include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/settings-local-payments-nav.php';
	}

	public function init_form_fields() {
		$this->form_fields = apply_filters( 'sswps/form_fields_' . $this->id, $this->get_local_payment_settings() );
	}

	public function init_supports() {
		$this->supports = [ 'tokenization', 'products', 'refunds' ];
	}

	/**
	 * Return an array of form fields for the gateway.
	 *
	 * @return array
	 */
	public function get_local_payment_settings() {
		return [
			'desc'               => [
				'type'        => 'description',
				'description' => [ $this, 'get_payment_description' ],
			],
			'enabled'            => [
				'title'       => __( 'Enabled', 'simple-secure-stripe' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'value'       => 'yes',
				'desc_tip'    => true,
				/* translators: %s - payment method */
				'description' => sprintf( __( 'If enabled, your site can accept %s payments through Stripe.', 'simple-secure-stripe' ), $this->get_method_title() ),
			],
			'general_settings'   => [
				'type'  => 'title',
				'title' => __( 'General Settings', 'simple-secure-stripe' ),
			],
			'title_text'         => [
				'type'        => 'text',
				'title'       => __( 'Title', 'simple-secure-stripe' ),
				'default'     => $this->get_method_title(),
				'desc_tip'    => true,
				'description' => sprintf( __( 'Title of the %s gateway' ), $this->get_method_title() ),
			],
			'description'        => [
				'title'       => __( 'Description', 'simple-secure-stripe' ),
				'type'        => 'text',
				'default'     => '',
				'description' => __( 'Leave blank if you don\'t want a description to show for the gateway.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'order_button_text'  => [
				'title'       => __( 'Order Button Text', 'simple-secure-stripe' ),
				'type'        => 'text',
				'default'     => $this->get_order_button_text( $this->method_title ),
				'description' => __( 'The text on the Place Order button that displays when the gateway is selected on the checkout page.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'allowed_countries'  => [
				'title'    => __( 'Selling location(s)', 'simple-secure-stripe' ),
				'desc'     => __( 'This option lets you limit which countries you are willing to sell to.', 'simple-secure-stripe' ),
				'default'  => 'specific',
				'type'     => 'select',
				'class'    => 'wc-enhanced-select sswps-allowed-countries',
				'css'      => 'min-width: 350px;',
				'desc_tip' => true,
				'options'  => [
					'all'        => __( 'Sell to all countries', 'simple-secure-stripe' ),
					'all_except' => __( 'Sell to all countries, except for&hellip;', 'simple-secure-stripe' ),
					'specific'   => __( 'Sell to specific countries', 'simple-secure-stripe' ),
				],
			],
			'except_countries'   => [
				'title'             => __( 'Sell to all countries, except for&hellip;', 'simple-secure-stripe' ),
				'type'              => 'multi_select_countries',
				'css'               => 'min-width: 350px;',
				'options'           => $this->limited_countries,
				'default'           => [],
				'desc_tip'          => true,
				'description'       => __( 'When the billing country matches one of these values, the payment method will be hidden on the checkout page.', 'simple-secure-stripe' ),
				'custom_attributes' => [ 'data-show-if' => [ 'allowed_countries' => 'all_except' ] ],
				'sanitize_callback' => function( $value ) {
					return is_array( $value ) ? $value : [];
				},
			],
			'specific_countries' => [
				'title'             => __( 'Sell to specific countries', 'simple-secure-stripe' ),
				'type'              => 'multi_select_countries',
				'css'               => 'min-width: 350px;',
				'options'           => $this->limited_countries,
				'default'           => $this->countries,
				'desc_tip'          => true,
				'description'       => __( 'When the billing country matches one of these values, the payment method will be shown on the checkout page.', 'simple-secure-stripe' ),
				'custom_attributes' => [ 'data-show-if' => [ 'allowed_countries' => 'specific' ] ],
				'sanitize_callback' => function( $value ) {
					return is_array( $value ) ? $value : [];
				},
			],
		];
	}

	public function get_localized_params() {
		return array_merge_recursive(
			parent::get_localized_params(),
			[
				'local_payment_type' => $this->local_payment_type,
				'return_url'         => add_query_arg(
					[
						'key'                   => wp_create_nonce( 'local-payment' ),
						'_sswps_local_payment' => $this->id,
					],
					wc_get_checkout_url()
				),
				'element_params'     => $this->get_element_params(),
				'routes'             => [
					'delete_order_source' => REST\API::get_endpoint( App::get( REST\Checkout::class )->rest_uri( 'order/source' ) ),
					'update_source'       => REST\API::get_endpoint( App::get( REST\Source::class )->rest_uri( 'update' ) ),
				],
			]
		);
	}

	public function get_element_params() {
		return [
			'style' => [
				'base'    => [
					'padding'       => '10px 12px',
					'color'         => '#32325d',
					'fontFamily'    => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
					'fontSmoothing' => 'antialiased',
					'fontSize'      => '16px',
					'::placeholder' => [ 'color' => '#aab7c4' ],
				],
				'invalid' => [ 'color' => '#fa755a' ],
			],
		];
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function get_source_args( $order ) {
		$args = [
			'type'                 => $this->local_payment_type,
			'amount'               => Utils\Currency::add_number_precision( $order->get_total( 'raw' ), $order->get_currency() ),
			'currency'             => $order->get_currency(),
			/* translators: %s - Order ID */
			'statement_descriptor' => sprintf( __( 'Order %s', 'simple-secure-stripe' ), $order->get_order_number() ),
			'owner'                => $this->get_source_owner_args( $order ),
			'redirect'             => [ 'return_url' => $this->get_local_payment_return_url( $order ) ],
		];

		/**
		 * @since 1.0.0
		 *
		 * @param array $args
		 *
		 */
		return apply_filters( 'sswps/get_source_args', $args );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param WC_Order $order
	 *
	 * @retun array
	 */
	public function get_update_source_args( $order ) {
		return [
			'owner'    => $this->get_source_owner_args( $order ),
			'metadata' => [
				'order_id' => $order->get_id(),
				'created'  => time(),
			],
		];
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	protected function get_source_owner_args( $order ) {
		$owner = [
			'name'    => $this->payment_object->get_name_from_order( $order, 'billing' ),
			'address' => [
				'city'        => $order->get_billing_city(),
				'country'     => $order->get_billing_country(),
				'line1'       => $order->get_billing_address_1(),
				'line2'       => $order->get_billing_address_2(),
				'postal_code' => $order->get_billing_postcode(),
				'state'       => $order->get_billing_state(),
			],
		];
		if ( ( $email = $order->get_billing_email() ) ) {
			$owner['email'] = $email;
		}
		if ( ( $phone = $order->get_billing_phone() ) ) {
			$owner['phone'] = $phone;
		}

		return $owner;
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public function get_local_payment_return_url( $order ) {
		global $wp;
		if ( isset( $wp->query_vars['order-pay'] ) ) {
			$url = $order->get_checkout_payment_url();
		} else {
			$url = wc_get_checkout_url();
		}

		return add_query_arg(
			[
				'key'                   => $order->get_order_key(),
				'order_id'              => $order->get_id(),
				'_sswps_local_payment' => $this->id,
			],
			$url
		);
	}

	public function is_local_payment_available() {
		global $wp;
		$_available = false;
		if ( isset( $wp->query_vars['order-pay'] ) ) {
			$order           = wc_get_order( absint( $wp->query_vars['order-pay'] ) );
			$currency        = $order->get_currency();
			$billing_country = $order->get_billing_country();
			$total           = $order->get_total( 'raw' );
		} else {
			$currency        = get_woocommerce_currency();
			$customer        = WC()->customer;
			$billing_country = $customer ? $customer->get_billing_country() : null;
			$total           = WC()->cart ? WC()->cart->get_total( 'raw' ) : 0;
			if ( ! $billing_country ) {
				$billing_country = WC()->countries->get_base_country();
			}
		}
		if ( in_array( $currency, $this->currencies ) ) {
			$type = $this->get_option( 'allowed_countries' );
			if ( 'all_except' === $type ) {
				$_available = ! in_array( $billing_country, $this->get_option( 'except_countries', [] ) );
			} elseif ( 'specific' === $type ) {
				$_available = in_array( $billing_country, $this->get_option( 'specific_countries', [] ) );
			} else {
				$_available = ! $this->limited_countries || in_array( $billing_country, $this->limited_countries );
			}
		}
		if ( $_available && method_exists( $this, 'validate_local_payment_available' ) ) {
			$_available = $this->validate_local_payment_available( $currency, $billing_country, $total );
		}

		/**
		 * @since 1.0.0
		 *
		 * @param array                  $_available
		 * @param Abstract_Local_Payment $object
		 */
		return apply_filters( 'sswps/local_payment_available', $_available, $this );
	}

	public function get_payment_token( $method_id, $method_details = [] ) {
		/**
		 *
		 * @var Tokens\Local $token
		 */
		$token = parent::get_payment_token( $method_id, $method_details );
		$token->set_gateway_title( $this->title );

		return $token;
	}

	/**
	 * Return a description for (for admin sections) describing the required currency & or billing country(s).
	 *
	 * @return string
	 */
	protected function get_payment_description() {
		$desc = '';
		if ( $this->currencies ) {
			/* translators: %s - list of currencies */
			$desc .= sprintf( __( 'Gateway will appear when store currency is <strong>%s</strong>', 'simple-secure-stripe' ), implode( ', ', $this->currencies ) );
		}
		if ( 'all_except' === $this->get_option( 'allowed_countries' ) ) {
			/* translators: %s - countries */
			$desc .= sprintf( __( ' & billing country is not <strong>%s</strong>', 'simple-secure-stripe' ), implode( ', ', $this->get_option( 'except_countries' ) ) );
		} elseif ( 'specific' === $this->get_option( 'allowed_countries' ) ) {
			/* translators: %s - countries */
			$desc .= sprintf( __( ' & billing country is <strong>%s</strong>', 'simple-secure-stripe' ), implode( ', ', $this->get_option( 'specific_countries' ) ) );
		} else {
			if ( $this->limited_countries ) {
				/* translators: %s - countries */
				$desc .= sprintf( __( ' & billing country is <strong>%s</strong>', 'simple-secure-stripe' ), implode( ', ', $this->limited_countries ) );
			}
		}

		return $desc;
	}

	/**
	 * Return a description of the payment method.
	 */
	public function get_local_payment_description() {
		return apply_filters( 'sswps/local_payment_description', $this->local_payment_description, $this );
	}

	/**
	 *
	 * @since 1.0.0
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function get_order_button_text( $text ) {
		/* translators: %s - payment method */
		return apply_filters( 'sswps/order_button_text', sprintf( __( 'Pay with %s', 'simple-secure-stripe' ), $text ), $this );
	}

	public function get_stripe_documentation_url() {
		return 'https://docs.paymentplugins.com/sswps/config/#/stripe_local_gateways';
	}

}
