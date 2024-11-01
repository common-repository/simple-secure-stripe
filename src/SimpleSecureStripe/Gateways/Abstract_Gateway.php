<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use Exception;
use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Customer_Manager;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Checker;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\REST;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Card;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Charge;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Source;
use SimpleSecureWP\SimpleSecureStripe\Tokens;
use SimpleSecureWP\SimpleSecureStripe\Tokens\Abstract_Token;
use SimpleSecureWP\SimpleSecureStripe\Traits;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WC_Cart;
use WC_Order;
use WC_Payment_Tokens;
use WC_Pre_Orders_Cart;
use WC_Pre_Orders_Product;
use WC_Product;
use WC_Shipping_Rate;
use WC_Subscription;
use WC_Subscriptions;
use WC_Subscriptions_Cart;
use WP_Error;

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

/**
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Abstract
 *
 */
abstract class Abstract_Gateway extends \WC_Payment_Gateway {

	use Payment\Traits\Intent;
	use Traits\Settings;

	/**
	 * Has the settings section rendered already?
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, bool>
	 */
	public static $has_rendered = [];

	/**
	 * @var string
	 */
	public $id;

	/**
	 *
	 * @var Payment\Abstract_Payment
	 */
	public $payment_object;

	/**
	 * @since 1.0.0
	 * @var bool
	 */
	protected bool $has_digital_wallet = false;

	/**
	 *
	 * @var string
	 */
	public string $token_key;

	/**
	 *
	 * @var string
	 */
	public string $saved_method_key;

	/**
	 *
	 * @var string
	 */
	public string $payment_type_key;

	/**
	 *
	 * @var string
	 */
	public string $payment_intent_key;

	/**
	 *
	 * @var string
	 */
	public string $save_source_key;

	/**
	 *
	 * @var string
	 */
	public string $template_name;

	/**
	 * @var array
	 */
	public $limited_countries;

	/**
	 * @var array
	 */
	public $currencies;

	/**
	 * @var string
	 */
	public $local_payment_type;

	/**
	 *
	 * @var bool
	 */
	protected bool $checkout_error = false;

	/**
	 * Used to create an instance of a WC_Payment_Token
	 *
	 * @var string
	 */
	protected string $token_type;

	/**
	 *
	 * @var Gateway
	 */
	public Gateway $gateway;

	/**
	 *
	 * @var WP_Error
	 */
	protected WP_Error $wp_error;

	/**
	 *
	 * @var ?string
	 */
	public ?string $payment_method_token = null;

	/**
	 * @var string
	 */
	protected string $payment_method_type = '';

	/**
	 * @var \WC_Payment_Token
	 */
	protected $payment_token_object;

	/**
	 *
	 * @var ?string
	 */
	protected ?string $new_source_token = null;

	/**
	 * Is the payment method synchronous or asynchronous
	 *
	 * @var bool
	 */
	public bool $synchronous = true;

	/**
	 *
	 * @var array
	 */
	protected array $post_payment_processes = [];

	/**
	 *
	 * @var bool
	 */
	public bool $processing_payment = false;

	/**
	 * @var WP_Error
	 */
	public WP_Error $last_payment_error;

	/**
	 * @var bool @since 1.0.0
	 */
	public bool $is_voucher_payment = false;

	/**
	 * @var bool
	 */
	protected $supports_save_payment_method = false;

	public function __construct() {
		$this->token_key          = $this->id . '_token_key';
		$this->saved_method_key   = $this->id . '_saved_method_key';
		$this->save_source_key    = $this->id . '_save_source_key';
		$this->payment_type_key   = $this->id . '_payment_type_key';
		$this->payment_intent_key = $this->id . '_payment_intent_key';
		$this->has_fields         = true;
		$this->init_form_fields();
		$this->init_settings();
		$this->title       = $this->get_option( 'title_text' );
		$this->description = $this->get_option( 'description' );
		$this->hooks();
		$this->init_supports();
		$this->gateway = Gateway::load();

		$this->payment_object = $this->get_payment_object();

		App::singleton( $this->id, $this );
	}

	/**
	 * Override admin_options for gateways and ensure that we only render the fields once.
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {
		global $current_section;

		if ( ! empty( self::$has_rendered[ $current_section ] ) ) {
			return;
		}
		self::$has_rendered[ $current_section ] = true;
		?>
		<h2><?php echo esc_html( $this->method_title ); ?></h2>
		<p>
			<?php echo esc_html( $this->method_description ); ?>
		</p>
		<?php
		echo '<table class="form-table">' . $this->generate_settings_html( $this->get_form_fields(), false ) . '</table>'; // WPCS: XSS ok.
	}

	public function hooks() {
		add_filter( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_filter( 'sswps/settings_nav_tabs', [ $this, 'admin_nav_tab' ] );
		add_action( 'woocommerce_stripe_settings_checkout_' . $this->id, [ $this, 'enqueue_admin_scripts' ] );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		add_filter( 'woocommerce_payment_methods_list_item', [ $this, 'payment_methods_list_item' ], 10, 2 );
		add_action( 'sswps/payment_token_deleted_' . $this->id, [ $this, 'delete_payment_method' ], 10, 2 );
		add_filter( 'woocommerce_subscription_payment_meta', [ $this, 'subscription_payment_meta' ], 10, 2 );
		add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, [ $this, 'scheduled_subscription_payment' ], 10, 2 );
		add_action( 'woocommerce_subscription_failing_payment_method_updated_' . $this->id, [ $this, 'update_failing_payment_method' ], 10, 2 );
		add_action( 'wc_pre_orders_process_pre_order_completion_payment_' . $this->id, [ $this, 'process_pre_order_payment' ] );

		/**
		 * @since 1.0.0
		 */
		add_filter( 'sswps/mini_cart_dependencies', [ $this, 'get_mini_cart_dependencies' ], 10, 2 );
	}

	/**
	 * Register assets for the gateway.
	 *
	 * @since 1.0.0
	 */
	public function register_assets() {
	}

	/**
	 * Gets the localize data for the gateway.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_localize_data() : array {
		return [];
	}

	public function init_supports() {
		$this->supports = [
			'tokenization',
			'products',
			'subscriptions',
			'add_payment_method',
			'subscription_cancellation',
			'multiple_subscriptions',
			'subscription_amount_changes',
			'subscription_date_changes',
			'default_credit_card_form',
			'refunds',
			'pre-orders',
			'subscription_payment_method_change_admin',
			'subscription_reactivation',
			'subscription_suspension',
			'subscription_payment_method_change_customer',
		];
	}

	public function init_form_fields() {
		$settings_file = SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/settings/gateways/' . str_replace(
				[ 'sswps_', '_' ],
				[ '', '-' ],
				$this->id
			) . '-settings.php';
		if ( ! file_exists( $settings_file ) ) {
			return;
		}

		$this->form_fields = include $settings_file;
		$this->form_fields = apply_filters( 'sswps/form_fields_' . $this->id, $this->form_fields );
	}

	public function get_payment_method_type() : string {
		return $this->payment_method_type;
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public function get_local_payment_return_url( $order ) {
		return '';
	}

	/**
	 * Return a description of the payment method.
	 */
	public function get_local_payment_description() {
		return '';
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function get_source_args( $order ) {
		return [];
	}

	/**
	 *
	 * @param Source   $source
	 * @param WC_Order $order
	 */
	public function get_source_redirect_url( $source, $order ) {
		return $source->redirect->offsetGet( 'url' );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param WC_Order $order
	 *
	 * @retun array
	 */
	public function get_update_source_args( $order ) {
		return [];
	}

	public function get_payment_method_formats() {
		$class_name = 'WC_Payment_Token_' . $this->token_type;
		$formats    = [];
		if ( class_exists( $class_name ) ) {
			/**
			 *
			 * @var Tokens\Abstract_Token
			 */
			$token = new $class_name();

			$formats = $token->get_formats();
		}

		return $formats;
	}

	public function enqueue_admin_scripts() {}

	public function payment_fields() {
		$this->enqueue_frontend_scripts();
		sswps_token_field( $this );
		sswps_payment_intent_field( $this );
		$this->output_display_items( 'checkout' );
		sswps_get_template(
			'checkout/stripe-payment-method.php',
			[
				'gateway' => $this,
				'tokens'  => is_add_payment_method_page() ? null
					: Utils\PaymentMethodUtils::sort_by_default( $this->get_tokens() ),
			]
		);
	}

	/**
	 * Output the product payment fields.
	 */
	public function product_fields() {
		global $product;
		$this->enqueue_frontend_scripts( 'product' );
		$this->output_display_items( 'product' );
		sswps_get_template(
			'product/' . $this->template_name,
			[
				'gateway' => $this,
				'product' => $product,
			]
		);
	}

	public function cart_fields() {
		$this->enqueue_frontend_scripts( 'cart' );
		$this->output_display_items( 'cart' );
		sswps_get_template( 'cart/' . $this->template_name, [ 'gateway' => $this ] );
	}

	public function mini_cart_fields() {
		$this->output_display_items( 'cart' );
		sswps_get_template( 'mini-cart/' . $this->template_name, [ 'gateway' => $this ] );
	}

	/**
	 * Enqueue scripts needed by the gateway on the frontend of the WC shop.
	 *
	 * @param string $page
	 */
	public function enqueue_frontend_scripts( $page = '' ) {
		global $wp;
		if ( $page ) {
			if ( 'product' === $page ) {
				$this->enqueue_product_scripts();
			} elseif ( 'cart' === $page ) {
				$this->enqueue_cart_scripts();
			} elseif ( 'checkout' === $page ) {
				$this->enqueue_checkout_scripts();
			} elseif ( 'mini_cart' === $page ) {
				$this->enqueue_mini_cart_scripts();
			} else {
				$this->enqueue_frontend_scripts();
			}
		} else {
			if ( is_add_payment_method_page() ) {
				$this->enqueue_add_payment_method_scripts();
			}
			if ( is_checkout() ) {
				$this->enqueue_checkout_scripts();
			}
			if ( is_cart() ) {
				$this->enqueue_cart_scripts();
			}
			if ( is_product() ) {
				$this->enqueue_product_scripts();
			}
		}

		$this->enqueue_payment_method_styles();
	}

	/**
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_payment_method_styles() {
		App::get( Assets\API::class )->enqueue( 'sswps-styles' );
	}

	/**
	 * Enqueue scripts needed by the gateway on the checkout page.
	 */
	public function enqueue_checkout_scripts() {
		App::get( Assets\API::class )->enqueue_group( 'sswps-local-payment' );
	}

	/**
	 * Enqueue scripts needed by the gateway on the add payment method page.
	 */
	public function enqueue_add_payment_method_scripts() {
		$this->enqueue_checkout_scripts();
	}

	/**
	 * Enqueue scripts needed by the gateway on the cart page.
	 */
	public function enqueue_cart_scripts() {
		App::get( Assets\API::class )->enqueue_group( 'sswps-local-payment-cart' );
	}

	/**
	 * Enqueue scripts needed by the gateway on the category page.
	 */
	public function enqueue_category_scripts() {
		App::get( Assets\API::class )->enqueue_group( 'sswps-local-payment-category' );
	}

	/**
	 * Enqueue scripts needed by the gateway on the product page.
	 */
	public function enqueue_product_scripts() {
		App::get( Assets\API::class )->enqueue_group( 'sswps-local-payment-product' );
	}

	/**
	 * @since 1.0.0
	 *
	 */
	public function enqueue_mini_cart_scripts() {
		App::get( Assets\API::class )->enqueue_group( 'sswps-local-payment-mini-cart' );

		$scripts = App::get( Assets\Assets::class );
		if ( ! wp_script_is( $scripts->get_handle( 'mini-cart' ) ) ) {
			$scripts->enqueue_script(
				'mini-cart',
				$scripts->assets_url( 'js/frontend/mini-cart.js' ),
				apply_filters( 'sswps/mini_cart_dependencies', [ 'sswps-script' ], $scripts )
			);
		}
		$scripts->localize_script( 'mini-cart', $this->get_localized_params(), 'wc_' . $this->id . '_mini_cart_params' );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Gateway::process_payment()
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $this->is_change_payment_method_request() && wcs_is_subscription( $order ) ) {
			return $this->process_change_payment_method_request( $order );
		}

		do_action( 'sswps/before_process_payment', $order, $this->id );

		if ( wc_notice_count( 'error' ) > 0 ) {
			return $this->get_order_error();
		}
		$this->processing_payment = true;

		if ( $this->order_contains_pre_order( $order ) && $this->pre_order_requires_tokenization( $order ) ) {
			return $this->process_pre_order( $order );
		}

		// if order total is zero, then save meta but don't process payment.
		if ( $order->get_total( 'raw' ) == 0 ) {
			return $this->process_zero_total_order( $order );
		}

		$result = $this->payment_object->process_payment( $order );

		if ( is_wp_error( $result ) ) {
			wc_add_notice( $this->is_active( 'generic_error' ) ? $this->get_generic_error( $result ) : $result->get_error_message(), 'error' );

			return $this->get_order_error( $result );
		}

		if ( $result->complete_payment ) {
			WC()->cart->empty_cart();
			$this->payment_object->payment_complete( $order, $result->charge );
			$this->trigger_post_payment_processes( $order, $this );

			return [
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			];
		} else {
			return [
				'result'   => 'success',
				'redirect' => $result->redirect,
			];
		}
	}

	/**
	 *
	 * @return array
	 */
	public function get_localized_params() {
		$data = [
			'gateway_id'            => $this->id,
			'api_key'               => sswps_get_publishable_key(),
			'saved_method_selector' => '[name="' . $this->saved_method_key . '"]',
			'token_selector'        => '[name="' . $this->token_key . '"]',
			'messages'              => [
				'terms'          => __( 'Please read and accept the terms and conditions to proceed with your order.', 'simple-secure-stripe' ),
				'required_field' => __( 'Please fill out all required fields.', 'simple-secure-stripe' ),
			],
			'routes'                => [
				'create_payment_intent'       => REST\API::get_endpoint( App::get( REST\Payment_Intent::class )->rest_uri( 'payment-intent' ) ),
				'order_create_payment_intent' => REST\API::get_endpoint( App::get( REST\Payment_Intent::class )->rest_uri( 'order/payment-intent' ) ),
				'setup_intent'                => REST\API::get_endpoint( App::get( REST\Payment_Intent::class )->rest_uri( 'setup-intent' ) ),
				'sync_intent'                 => REST\API::get_endpoint( App::get( REST\Payment_Intent::class )->rest_uri( 'sync-payment-intent' ) ),
				'add_to_cart'                 => REST\API::get_endpoint( App::get( REST\Cart::class )->rest_uri( 'add-to-cart' ) ),
				'cart_calculation'            => REST\API::get_endpoint( App::get( REST\Cart::class )->rest_uri( 'cart-calculation' ) ),
				'shipping_method'             => REST\API::get_endpoint( App::get( REST\Cart::class )->rest_uri( 'shipping-method' ) ),
				'shipping_address'            => REST\API::get_endpoint( App::get( REST\Cart::class )->rest_uri( 'shipping-address' ) ),
				'checkout'                    => REST\API::get_endpoint( App::get( REST\Checkout::class )->rest_uri( 'checkout' ) ),
				'checkout_payment'            => REST\API::get_endpoint( App::get( REST\Checkout::class )->rest_uri( 'checkout/payment' ) ),
				'order_pay'                   => REST\API::get_endpoint( App::get( REST\Checkout::class )->rest_uri( 'order-pay' ) ),
				'base_path'                   => REST\API::get_endpoint( '%s' ),
			],
			'rest_nonce'            => wp_create_nonce( 'wp_rest' ),
			'banner_enabled'        => $this->banner_checkout_enabled(),
			'currency'              => get_woocommerce_currency(),
			'total_label'           => __( 'Total', 'simple-secure-stripe' ),
			'country_code'          => wc_get_base_location()['country'],
			'user_id'               => get_current_user_id(),
			'description'           => $this->get_description(),
			'elementOptions'        => $this->get_element_options(),
		];
		global $wp;
		if ( isset( $wp->query_vars['order-pay'] ) ) {
			$data['order_id']  = absint( $wp->query_vars['order-pay'] );
			$data['order_key'] = Request::get_sanitized_var( 'key', '' );
		}

		return $data;
	}

	/**
	 * Save the Stripe data to the order.
	 *
	 * @param WC_Order $order
	 * @param Charge   $charge
	 */
	public function save_order_meta( $order, $charge ) {
		/**
		 *
		 * @var Tokens\Abstract_Token $token
		 */
		$token = $this->get_payment_token( $this->get_payment_method_from_charge( $charge ), $charge->payment_method_details );
		$order->set_transaction_id( $charge->id );
		$order->set_payment_method_title( $token->get_payment_method_title() );
		$order->update_meta_data( Constants::MODE, sswps_mode() );
		$order->update_meta_data( Constants::CHARGE_STATUS, $charge->status );
		$order->update_meta_data( Constants::PAYMENT_METHOD_TOKEN, $token->get_token() );

		/**
		 * @since 1.0.0
		 *
		 * @param WC_Order         $order
		 * @param Abstract_Gateway $object
		 * @param Charge           $charge
		 * @param \WC_Payment_Token $token
		 */
		do_action( 'sswps/save_order_meta', $order, $this, $charge, $token );

		$order->save();
	}

	/**
	 * Given a charge object, return the ID of the payment method used for the charge.
	 *
	 * @since 1.0.0
	 *
	 * @param Charge $charge
	 *
	 */
	public function get_payment_method_from_charge( $charge ) {
		return $this->payment_object->get_payment_method_from_charge( $charge );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Gateway::add_payment_method()
	 */
	public function add_payment_method() {
		$user_id = get_current_user_id();
		try {
			if ( ! is_user_logged_in() ) {
				throw new Exception( __( 'User must be logged in.', 'simple-secure-stripe' ) );
			}

			$customer_id = sswps_get_customer_id( $user_id );

			if ( empty( $customer_id ) ) {
				$customer_id = $this->create_customer( $user_id );
			}

			if ( $this->is_mandate_required() ) {
				$setup_intent = $this->gateway->setupIntents->retrieve( WC()->session->get( Constants::SETUP_INTENT_ID ), [
					'expand' => array( 'payment_method' )
				] );
				if ( is_wp_error( $setup_intent ) ) { // @phpstan-ignore-line - it might be a WP_Error
					throw new Exception( $setup_intent->get_error_message() );
				}
				$result = $this->get_payment_token( $setup_intent->payment_method->id, $setup_intent->payment_method );
				$result->set_customer_id( $customer_id );
				$result->update_meta_data( Constants::STRIPE_MANDATE, $setup_intent->mandate );
			} else {
				$result = $this->create_payment_method( $this->get_new_source_token(), $customer_id );
			}

			$result->set_user_id( $user_id );
			$result->save();
			WC_Payment_Tokens::set_users_default( $user_id, $result->get_id() );

			unset( WC()->session->{Constants::PAYMENT_INTENT}, WC()->session->{Constants::SETUP_INTENT_ID} );
			do_action( 'sswps/add_payment_method_success', $result );

			return [
				'result'   => 'success',
				'redirect' => wc_get_account_endpoint_url( 'payment-methods' ),
			];
		} catch ( Exception $e ) {
			/* translators: %s - error message */
			wc_add_notice( sprintf( __( 'Error saving payment method. Reason: %s', 'simple-secure-stripe' ), $e->getMessage() ), 'error' );

			return [ 'result' => 'error' ];
		}
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Gateway::process_refund()
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order  = wc_get_order( $order_id );
		$result = $this->payment_object->process_refund( $order, $amount );

		if ( ! is_wp_error( $result ) ) {
			$order->add_order_note(
				sprintf(
					__( 'Order refunded in Stripe. Amount: %s', 'simple-secure-stripe' ),
					wc_price(
						$amount,
						[
							'currency' => $order->get_currency(),
						]
					)
				)
			);
		}

		return $result;
	}

	/**
	 * @param WC_Order $order
	 */
	public function process_voucher_order_status( WC_Order $order ) {}

	/**
	 * Captures the charge for the Stripe order.
	 *
	 * @param float    $amount
	 * @param WC_Order $order
	 */
	public function capture_charge( $amount, $order ) {
		$result = $this->gateway->mode( sswps_order_mode( $order ) )->charges->retrieve( $order->get_transaction_id() );

		if ( ! $result->captured ) {
			$result = $this->payment_object->capture_charge( $amount, $order );

			remove_action( 'woocommerce_order_status_completed', 'sswps_order_status_completed' );
			Utils\Misc::add_balance_transaction_to_order( $result, $order, true );
			if ( isset( $result->refunds->data[0] ) ) {
				$balance_transaction = $this->gateway->balanceTransactions->retrieve( $result->refunds->data[0]->balance_transaction );
				Utils\Misc::update_balance_transaction( $balance_transaction, $order, true );
			}
			$order->payment_complete();
			$order->add_order_note(
				sprintf(
					/* translators: %s - amount */
					__( 'Order amount captured in Stripe. Amount: %s', 'simple-secure-stripe' ),
					wc_price( $amount, [ 'currency' => $order->get_currency(), ] )
				)
			);
		}

		return $result;
	}

	/**
	 * Void the Stripe charge.
	 *
	 * @param WC_Order $order
	 */
	public function void_charge( $order ) {
		// @3.1.1 - check added so errors aren't encountered if the order can't be voided
		if ( ! $this->payment_object->can_void_order( $order ) ) {
			return;
		}
		$result = $this->payment_object->void_charge( $order );

		if ( is_wp_error( $result ) ) {
			/* translators: %s - error reason */
			$order->add_order_note( sprintf( __( 'Error voiding charge. Reason: %s', 'simple-secure-stripe' ), $result->get_error_message() ) );
		} else {
			$order->add_order_note( __( 'Charge voided in Stripe.', 'simple-secure-stripe' ) );
		}
	}

	/**
	 * Return the \SimpleSecureWP\SimpleSecureStripe\Stripe\Charge object
	 *
	 * @param String $charge_id
	 * @param String $mode
	 *
	 * @return WP_Error|Charge
	 */
	public function retrieve_charge( $charge_id, $mode = '' ) {
		return $this->gateway->mode( $mode )->charges->retrieve( $charge_id );
	}

	/**
	 *
	 * @param string     $method_id
	 * @param Card|array $method_details
	 */
	public function get_payment_token( $method_id, $method_details = null ) {
		$class_name = '\\SimpleSecureWP\\SimpleSecureStripe\\Tokens\\' . str_replace( 'Stripe_', '', $this->token_type );
		if ( class_exists( $class_name ) ) {
			/**
			 *
			 * @var Tokens\Abstract_Token $token
			 */
			$token = new $class_name( '', $method_details );
			$token->set_token( $method_id );
			$token->set_gateway_id( $this->id );
			$token->set_format( $this->get_option( 'method_format' ) );
			$token->set_environment( sswps_mode() );
			if ( $method_details ) {
				$token->details_to_props( $method_details );
			}

			return $token;
		}
	}

	/**
	 * Return a failed order response.
	 *
	 * @param WP_Error $error
	 *
	 * @return array
	 */
	public function get_order_error( $error = null ) {
		sswps_set_checkout_error();
		$this->last_payment_error = $error;
		do_action( 'sswps/process_payment_error', $error, $this );

		return [ 'result' => Constants::FAILURE, 'redirect' => '' ];
	}

	/**
	 * Return the payment source the customer has chosen to use.
	 * This can be a saved source
	 * or a one time use source.
	 */
	public function get_payment_source() {
		if ( $this->use_saved_source() ) {
			return $this->get_saved_source_id();
		} else {
			if ( $this->payment_method_token ) {
				return $this->payment_method_token;
			}

			return $this->get_new_source_token();
		}
	}

	/**
	 * Returns the payment method the customer wants to use.
	 * This can be a saved payment method
	 * or a new payment method.
	 */
	public function get_payment_method_from_request() {
		return $this->get_payment_source();
	}

	public function get_payment_intent_id() {
		return wc_clean( Request::get_sanitized_var( $this->payment_intent_key, '' ) );
	}

	/**
	 * Return true of the customer is using a saved payment method.
	 */
	public function use_saved_source() {
		$payment_type_key = wc_clean( Request::get_sanitized_var( $this->payment_type_key, '' ) );
		$payment_token    = Request::get_sanitized_var( "wc-{$this->id}-payment-token" );
		return ( ! empty( $payment_type_key ) && $payment_type_key === 'saved' ) || $this->payment_method_token
			|| ( ! empty( $payment_token ) );
	}

	public function get_new_source_token() {
		$token = wc_clean( Request::get_sanitized_var( $this->token_key, '' ) );
		return null != $this->new_source_token ? $this->new_source_token : $token;
	}

	public function get_saved_source_id() {
		$token = Request::get_sanitized_var( "wc-{$this->id}-payment-token" );
		// Check if Blocks are being used
		if ( ! empty( $token ) ) {
			$token = WC_Payment_Tokens::get( wc_clean( $token ) );

			return $token->get_token();
		}

		$method_key = Request::get_sanitized_var( $this->saved_method_key );
		$type_key   = Request::get_sanitized_var( $this->payment_type_key );

		if ( ! empty( $method_key ) && ! empty( $type_key ) && 'saved' == $type_key ) {
			return wc_clean( $method_key );
		}

		return $this->payment_method_token;
	}

	/**
	 * Create a customer in the stripe gateway.
	 *
	 * @param int $user_id
	 *
	 * @throws Exception
	 */
	public function create_customer( $user_id ) {
		$customer = WC()->customer;
		$response = App::get( Customer_Manager::class )->create_customer( $customer );
		if ( ! is_wp_error( $response ) ) {
			sswps_save_customer( $response->id, $user_id );
		} else {
			throw new Exception( $response->get_error_message() );
		}

		return $response->id;
	}

	/**
	 * Creates a payment method in Stripe.
	 *
	 * @param string $id
	 *          payment method id
	 * @param string $customer_id
	 *          sswps customer ID
	 *
	 * @return Tokens\Abstract_Token|WP_Error
	 */
	public function create_payment_method( $id, $customer_id ) {
		$token = $this->get_payment_token( $id );
		$token->set_customer_id( $customer_id );

		$result = $token->save_payment_method();

		if ( is_wp_error( $result ) ) {
			return $result;
		} else {
			$token->set_token( $result->id );
			$token->details_to_props( $result );

			return $token;
		}
	}

	/**
	 *
	 * @param array     $item
	 * @param Tokens\CC $payment_token
	 */
	public function payment_methods_list_item( $item, $payment_token ) {
		if ( $payment_token->get_type() === $this->token_type && $this->id === $payment_token->get_gateway_id() ) {
			$item['method']['last4'] = $payment_token->get_last4();
			$item['method']['brand'] = ucfirst( $payment_token->get_brand() );
			if ( $payment_token->has_expiration() ) {
				$item['expires'] = sprintf( '%s / %s', $payment_token->get_exp_month(), $payment_token->get_exp_year() );
			} else {
				$item['expires'] = __( 'n/a', 'simple-secure-stripe' );
			}
			$item['sswps_method'] = true;
		}

		return $item;
	}

	/**
	 *
	 * @param string                $token_id
	 * @param Tokens\Abstract_Token $token
	 */
	public function delete_payment_method( $token_id, $token ) {
		$token->delete_from_stripe();
	}

	public function saved_payment_methods( $tokens = [] ) {
		sswps_get_template(
			'payment-methods.php',
			[
				'tokens'  => $tokens,
				'gateway' => $this,
			]
		);
	}

	public function get_new_method_label() {
		return __( 'New Card', 'simple-secure-stripe' );
	}

	public function get_saved_methods_label() {
		return __( 'Saved Cards', 'simple-secure-stripe' );
	}

	/**
	 * @since 3.3.42
	 * @return string
	 */
	public function get_save_payment_method_label() {
		return __( 'Save payment method', 'simple-secure-stripe' );
	}

	/**
	 * Return true if shipping is needed.
	 * Shipping is based on things like if the cart or product needs shipping.
	 *
	 * @return bool
	 */
	public function get_needs_shipping() {
		if ( is_checkout() || is_cart() ) {
			global $wp;
			if ( Checker::is_woocommerce_subscriptions_active() && \WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment ) {
				return false;
			}
			// return false if this is the order pay page. Gateways that have payment sheets don't need
			// to make any changes to the order.
			if ( ! empty( $wp->query_vars['order-pay'] ) ) {
				return false;
			}

			return WC()->cart->needs_shipping();
		}
		if ( is_product() ) {
			global $product;

			return is_a( $product, 'WC_Product' ) && $product->needs_shipping();
		}

		return false;
	}

	/**
	 * Return true of the payment method should be saved.
	 *
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	public function should_save_payment_method( $order ) {
		$bool = false;
		if ( ! $this->use_saved_source() && ! $this->is_processing_scheduled_payment() ) {
			if ( Checker::is_woocommerce_subscriptions_active() && $this->supports( 'subscriptions' ) ) {
				if ( wcs_order_contains_subscription( $order ) || wcs_order_contains_renewal( $order ) ) {
					$bool = true;
				}
			}

			$source_key = Request::get_sanitized_var( $this->save_source_key );
			if ( ! empty( $source_key ) ) {
				$bool = true;
			}
		}

		/**
		 * @since 1.0.0
		 *
		 * @param bool             $bool
		 * @param WC_Order         $order
		 * @param Abstract_Gateway $object
		 */
		return apply_filters( 'sswps/should_save_payment_method', $bool, $order, $this );
	}

	/**
	 * Returns true if the save payment method checkbox can be displayed.
	 *
	 * @return boolean
	 */
	public function show_save_source() {
		return false;
	}

	/**
	 * Returns a formatted array of items for display in the payment gateway's payment sheet.
	 *
	 * @param string $page
	 *
	 * @return array
	 */
	public function get_display_items( $page = 'checkout', $order = null ) {
		global $wp;
		$items = [];
		if ( in_array( $page, [ 'cart', 'checkout' ] ) ) {
			$items = $this->get_display_items_for_cart( WC()->cart );
		} elseif ( 'order_pay' === $page ) {
			$order = ! is_null( $order ) ? $order : wc_get_order( absint( $wp->query_vars['order-pay'] ) );
			$items = $this->get_display_items_for_order( $order );
		} elseif ( 'product' === $page ) {
			global $product;
			$items = [ $this->get_display_item_for_product( $product ) ];
		}

		/**
		 * @param array         $items
		 * @param WC_Order|null $order
		 * @param string        $page
		 */
		return apply_filters( 'sswps/get_display_items', $items, $order, $page );
	}

	/**
	 * Return true if product page checkout is enabled for this gateway
	 *
	 * @return bool
	 */
	public function product_checkout_enabled() {
		return in_array( 'product', $this->get_option( 'payment_sections', [] ) );
	}

	/**
	 * Return true if cart page checkout is enabled for this gateway
	 *
	 * @return bool
	 */
	public function cart_checkout_enabled() {
		return in_array( 'cart', $this->get_option( 'payment_sections', [] ) );
	}

	/**
	 * Return true if mini-cart checkout is enabled for this gateway
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function mini_cart_enabled() {
		return in_array( 'mini_cart', $this->get_option( 'payment_sections', [] ) );
	}

	/**
	 * Return true if checkout page banner is enabled for this gateway
	 *
	 * @return bool
	 */
	public function banner_checkout_enabled() {
		global $wp;

		return empty( $wp->query_vars['order-pay'] ) && $this->supports( 'sswps_banner_checkout' ) && in_array( 'checkout_banner', $this->get_option( 'payment_sections', [] ) );
	}

	/**
	 * Decorate the response with data specific to the gateway.
	 *
	 * @param array $data
	 */
	public function add_to_cart_response( $data ) {
		return $data;
	}

	/**
	 * Decorate the update shipping method reponse with data.
	 *
	 * @param array $data
	 */
	public function get_update_shipping_method_response( $data ) {
		return $data;
	}

	/**
	 * Decorate the update shipping address respond with data.
	 *
	 * @param array $data
	 */
	public function get_update_shipping_address_response( $data ) {
		return apply_filters( 'sswps/update_shipping_address_response', $data );
	}

	/**
	 * Save the customer's payment method.
	 * If the payment method has already been saved to the customer
	 * then simply return true.
	 *
	 * @param string   $id
	 * @param WC_Order $order
	 * @param Card|array|null $payment_details
	 *
	 * @return WP_Error|bool
	 */
	public function save_payment_method( $id, $order, $payment_details = null ) {
		$mode = sswps_order_mode( $order );
		$user_id = $order->get_customer_id();
		$customer_id = sswps_get_customer_id( $user_id, $mode );
		if ( ! $customer_id ) {
			$response = App::get( Customer_Manager::class )->create_customer( new \WC_Customer( $user_id, ! $user_id ), $mode );
			if ( ! is_wp_error( $response ) ) {
				$payment_details = null;
				$customer_id     = $response->id;
				if ( $user_id ) {
					sswps_save_customer( $customer_id, $user_id, $mode );
				} else {
					$order->update_meta_data( Constants::CUSTOMER_ID, $customer_id );
				}
			}
		}

		if ( $payment_details ) {
			$token = $this->get_payment_token( $id, $payment_details );
			$token->set_customer_id( $customer_id );
		} else {
			$token = $this->create_payment_method( $id, $customer_id );
		}

		$token->set_user_id( $user_id );
		if ( $user_id && strtolower( $token->get_brand() ) !== 'link' ) {
			$token->save();
		}

		// set token value so it can be used for other processes.
		$this->payment_token_object = $token;
		$this->payment_method_token = $token->get_token();

		return true;
	}

	/**
	 *
	 * @param string $token_id
	 * @param int    $user_id
	 *
	 * @return Abstract_Token|null
	 */
	public function get_token( $token_id, $user_id ) {
		$tokens = WC_Payment_Tokens::get_tokens( [ 'user_id' => $user_id, 'gateway_id' => $this->id, 'limit' => 20 ] );
		foreach ( $tokens as $token ) {
			if ( $token_id === $token->get_token() ) {
				return $token; // @phpstan-ignore-line
			}
		}

		return null;
	}

	/**
	 *
	 * @param array            $payment_meta
	 * @param WC_Subscription $subscription
	 */
	public function subscription_payment_meta( $payment_meta, $subscription ) {
		$payment_meta[ $this->id ] = [
			'post_meta' => [
				Constants::PAYMENT_METHOD_TOKEN => [
					'value' => $this->get_order_meta_data( Constants::PAYMENT_METHOD_TOKEN, $subscription ),
					'label' => __( 'Payment Method Token', 'simple-secure-stripe' ),
				],
				Constants::CUSTOMER_ID          => [
					'value' => $this->get_order_meta_data( Constants::CUSTOMER_ID, $subscription ),
					'label' => __( 'Stripe Customer ID', 'simple-secure-stripe' ),
				],
			],
		];

		return $payment_meta;
	}

	/**
	 *
	 * @param float    $amount
	 * @param WC_Order $order
	 */
	public function scheduled_subscription_payment( $amount, $order ) {
		$this->processing_payment = true;

		$result = $this->payment_object->scheduled_subscription_payment( $amount, $order );

		if ( is_wp_error( $result ) ) {
			$order->update_status( 'failed' );
			/* translators: %s - Failure reason */
			$order->add_order_note( sprintf( __( 'Recurring payment for order failed. Reason: %s', 'simple-secure-stripe' ), $result->get_error_message() ) );

			return;
		}

		if ( $result->charge ) {
			$this->save_order_meta( $order, $result->charge );
		}

		// set the payment method token that was used to process the renewal order.
		$this->payment_method_token = $order->get_meta( Constants::PAYMENT_METHOD_TOKEN );

		if ( $result->complete_payment ) {
			if ( $result->charge ) {
				if ( $result->charge->captured ) {
					if ( $result->charge->status === 'pending' ) {
						// pending status means this is an asynchronous payment method.
						$order->update_status( apply_filters( 'sswps/renewal_pending_order_status', 'on-hold', $order, $this, $result->charge ), __( 'Renewal payment initiated in Stripe. Waiting for the payment to clear.', 'simple-secure-stripe' ) );
					} else {
						Utils\Misc::add_balance_transaction_to_order( $result->charge, $order );
						$order->payment_complete( $result->charge->id );
						/* translators: %s - payment method */
						$order->add_order_note( sprintf( __( 'Recurring payment captured in Stripe. Payment method: %s', 'simple-secure-stripe' ), $order->get_payment_method_title() ) );
					}
				} else {
					$order->update_status( 'on-hold' );
				}
			} else {
				$order->update_status(
					apply_filters( 'sswps/authorized_renewal_order_status', 'on-hold', $order, $this ),
					/* translators: %s - payment method */
					sprintf( __( 'Recurring payment authorized in Stripe. Payment method: %s', 'simple-secure-stripe' ), $order->get_payment_method_title() )
				);
			}
		} else {
			/* translators: %s - payment method */
			$order->update_status( 'pending', sprintf( __( 'Customer must manually complete payment for payment method %s', 'simple-secure-stripe' ), $order->get_payment_method_title() ) );
		}
	}

	/**
	 * Return true if this request is to change the payment method of a WC Subscription.
	 *
	 * @return bool
	 */
	public function is_change_payment_method_request() {
		return Checker::is_woocommerce_subscriptions_active() && did_action( 'woocommerce_subscriptions_pre_update_payment_method' );
	}

	/**
	 * Sets the ID of a payment token.
	 *
	 * @param string $id
	 */
	public function set_payment_method_token( $id ) {
		$this->payment_method_token = $id;
	}

	public function set_new_source_token( $token ) {
		$this->new_source_token = $token;
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	public function process_zero_total_order( $order ) {
		// save payment method if necessary
		if ( ! defined( Constants::PROCESSING_PAYMENT ) && $this->should_save_payment_method( $order ) ) {
			$result = $this->save_payment_method( $this->get_new_source_token(), $order );
			if ( is_wp_error( $result ) ) {
				wc_add_notice( $result->get_error_message(), 'error' );

				return $this->get_order_error();
			}
		} else {
			$this->payment_method_token = $this->get_saved_source_id();

			return $this->payment_object->process_zero_total_order( $order, $this );
		}

		return $this->payment_object->process_zero_total_order( $order, $this );
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function process_pre_order( $order ) {
		$token = null;
		// maybe save payment method
		if ( ! $this->use_saved_source() ) {
			// if user not logged in, create a Stripe customer that won't be assigned to a user.
			if ( ! $order->get_customer_id() ) {
				$customer = App::get( Customer_Manager::class )->create_customer( WC()->customer );
				if ( is_wp_error( $customer ) ) {
					wc_add_notice( $customer->get_error_message(), 'error' );
				}
				$order->update_meta_data( Constants::CUSTOMER_ID, $customer->id );
				$result = $token = $this->create_payment_method( $this->get_new_source_token(), $customer->id );
			} else {
				$result = $this->save_payment_method( $this->get_new_source_token(), $order );
			}
			if ( is_wp_error( $result ) ) {
				wc_add_notice( $result->get_error_message(), 'error' );

				return $this->get_order_error();
			}
		} else {
			$this->payment_method_token = $this->get_saved_source_id();
		}
		\WC_Pre_Orders_Order::mark_order_as_pre_ordered( $order );
		$this->save_zero_total_meta( $order, $token );

		return [
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		];
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	public function save_zero_total_meta( $order, $token = null ) {
		if ( $this->payment_token_object ) {
			$token = $this->payment_token_object;
		} else {
			$token = ! $token ? $this->get_token( $this->payment_method_token, $order->get_user_id() ) : $token;
		}
		$order->set_payment_method_title( $token->get_payment_method_title( $this->get_option( 'method_format' ) ) );
		$order->update_meta_data( Constants::MODE, sswps_mode() );
		$order->update_meta_data( Constants::PAYMENT_METHOD_TOKEN, $token->get_token() );
		if ( $order->get_customer_id() ) {
			$order->update_meta_data( Constants::CUSTOMER_ID, sswps_get_customer_id( $order->get_user_id() ) );
		}

		/**
		 * @since 1.0.0
		 *
		 * @param WC_Order         $order
		 * @param Abstract_Gateway $object
		 * @param null             $charge
		 * @param \WC_Payment_Token $token
		 *
		 */
		do_action( 'sswps/save_order_meta', $order, $this, null, $token );

		$order->save();
	}

	/**
	 * Pre orders can't be mixed with regular products.
	 *
	 * @param WC_Order $order
	 */
	protected function order_contains_pre_order( $order ) {
		return sswps_pre_orders_active() && \WC_Pre_Orders_Order::order_contains_pre_order( $order );
	}

	/**
	 *
	 * @param WC_Order $order
	 *
	 * @return boolean
	 */
	protected function pre_order_requires_tokenization( $order ) {
		return \WC_Pre_Orders_Order::order_requires_payment_tokenization( $order );
	}

	/**
	 * Sets a lock on the order.
	 * Default behavior is a 2 minute lock.
	 *
	 * @param WC_Order|int $order
	 */
	public function set_order_lock( $order ) {
		$order_id = ( is_object( $order ) ? $order->get_id() : $order );
		set_transient( 'sswps_lock_order_' . $order_id, $order_id, apply_filters( 'sswps/set_order_lock', 2 * MINUTE_IN_SECONDS ) );
	}

	/**
	 * Removes the lock on the order
	 *
	 * @param WC_Order|int $order
	 */
	public function release_order_lock( $order ) {
		delete_transient( 'sswps_lock_order_' . ( is_object( $order ) ? $order->get_id() : $order ) );
	}

	/**
	 * Returns true of the order has been locked.
	 * If the lock exists and is greater than current time
	 * method returns true;
	 *
	 * @param WC_Order|int $order
	 */
	public function has_order_lock( $order ) {
		$lock = get_transient( 'stripe_lock_order_' . ( is_object( $order ) ? $order->get_id() : $order ) );

		return $lock !== false;
	}

	public function set_post_payment_process( $callback ) {
		$this->post_payment_processes[] = $callback;
	}

	/**
	 *
	 * @param WC_Order         $order
	 * @param Abstract_Gateway $gateway
	 */
	public function trigger_post_payment_processes( $order, $gateway ) {
		foreach ( $this->post_payment_processes as $callback ) {
			call_user_func_array( $callback, func_get_args() );
		}
	}

	public function validate_payment_sections_field( $key, $value ) {
		if ( empty( $value ) ) {
			$value = [];
		}

		return $value;
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	public function process_pre_order_payment( $order ) {
		$this->processing_payment = true;

		$result = $this->payment_object->process_pre_order_payment( $order );

		if ( is_wp_error( $result ) ) {
			$order->update_status( 'failed' );
			/* translators: %s - error reason */
			$order->add_order_note( sprintf( __( 'Pre-order payment for order failed. Reason: %s', 'simple-secure-stripe' ), $result->get_error_message() ) );
		} else {
			if ( $result->complete_payment ) {
				$this->save_order_meta( $order, $result->charge );

				if ( $result->charge->captured ) {
					if ( $result->charge->status === 'pending' ) {
						$order->update_status( apply_filters( 'sswps/pending_preorder_order_status', 'on-hold', $order, $this ), __( 'Pre-order payment initiated in Stripe. Waiting for the payment to clear.', 'simple-secure-stripe' ) );
					} else {
						Utils\Misc::add_balance_transaction_to_order( $result->charge, $order );
						$order->payment_complete( $result->charge->id );
						/* translators: %s - payment method */
						$order->add_order_note( sprintf( __( 'Pre-order payment captured in Stripe. Payment method: %s', 'simple-secure-stripe' ), $order->get_payment_method_title() ) );
					}
				} else {
					$order->update_status(
						apply_filters( 'sswps/authorized_preorder_order_status', 'on-hold', $order, $this ),
						/* translators: %s - payment method */
						sprintf( __( 'Pre-order payment authorized in Stripe. Payment method: %s', 'simple-secure-stripe' ), $order->get_payment_method_title() )
					);
				}
			} else {
				/* translators: %s - payment method */
				$order->update_status( 'pending', sprintf( __( 'Customer must manually complete payment for payment method %s', 'simple-secure-stripe' ), $order->get_payment_method_title() ) );
			}
		}
	}

	/**
	 * Given a meta key, see if there is a value for that key in another plugin.
	 * This acts as a lazy conversion
	 * method for merchants that have switched to our plugin from other plugins.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Order $order
	 * @param string   $context
	 *
	 * @param string   $meta_key
	 */
	public function get_order_meta_data( $meta_key, $order, $context = 'view' ) {
		$value = $order->get_meta( $meta_key, true, $context );
		// value is empty so check metadata from other plugins
		if ( empty( $value ) ) {
			$keys = [];
			switch ( $meta_key ) {
				case Constants::PAYMENT_METHOD_TOKEN:
					$keys = [ Constants::SOURCE_ID ];
					break;
				case Constants::CUSTOMER_ID:
					$keys = [ Constants::STRIPE_CUSTOMER_ID ];
					break;
				case Constants::PAYMENT_INTENT_ID:
					$keys = [ Constants::STRIPE_INTENT_ID ];
			}
			if ( $keys ) {
				$meta_data = $order->get_meta_data();
				if ( $meta_data ) {
					$keys       = array_intersect( wp_list_pluck( $meta_data, 'key' ), $keys );
					$array_keys = array_keys( $keys );
					if ( ! empty( $array_keys ) ) {
						$value = $meta_data[ current( $array_keys ) ]->value;
						$order->update_meta_data( $meta_key, $value );
						$order->save();
					}
				}
			}
		}

		return $value;
	}

	/**
	 * Gateways can override this method to add attributes to the Stripe object before it's
	 * sent to Stripe.
	 *
	 * @param array    $args
	 * @param WC_Order $order
	 */
	public function add_stripe_order_args( &$args, $order ) {}

	/**
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Error $result
	 *
	 */
	public function get_generic_error( $result = null ) {
		$messages = sswps_get_error_messages();
		if ( isset( $messages["{$this->id}_generic"] ) ) {
			return $messages["{$this->id}_generic"];
		}

		return null != $result ? $result->get_error_message() : __( 'Cannot process payment', 'simple-secure-stripe' );
	}

	/**
	 *
	 * @since 1.0.0
	 */
	public function get_payment_section_description() {
		return sprintf(
			/* translators: %1$s - payment method */
			__(
				'Increase your conversion rate by offering %1$s on your Product and Cart pages, or at the top of the checkout page. <br/><strong>Note:</strong> you can control which products display %1$s by going to the product edit page.',
				'simple-secure-stripe'
			),
			$this->get_method_title()
		);
	}

	/**
	 * Outputs fields required by Google Pay to render the payment wallet.
	 *
	 * @param string $page
	 * @param array  $data
	 */
	public function output_display_items( $page = 'checkout', $data = [] ) {
		global $wp;
		$order = null;
		$data  = wp_parse_args( $data, [
			'items'            => $this->has_digital_wallet ? $this->get_display_items( $page ) : [],
			'shipping_options' => $this->has_digital_wallet ? $this->get_formatted_shipping_methods() : [],
			'total'            => wc_format_decimal( WC()->cart->get_total( 'raw' ), 2 ),
			'total_cents'      => Utils\Currency::add_number_precision( WC()->cart->get_total( 'raw' ), get_woocommerce_currency() ),
			'currency'         => get_woocommerce_currency(),
			'installments'     => [ 'enabled' => $this->is_installment_available() ],
		] );
		if ( in_array( $page, [ 'checkout', 'cart' ] ) ) {
			if ( ! empty( $wp->query_vars['order-pay'] ) ) {
				$order                  = wc_get_order( absint( $wp->query_vars['order-pay'] ) );
				$page                   = 'order_pay';
				$data['needs_shipping'] = false;
				$data['items']          = $this->has_digital_wallet ? $this->get_display_items( $page, $order ) : [];
				$data['total']          = $order->get_total( 'raw' );
				$data['total_cents']    = Utils\Currency::add_number_precision( $order->get_total( 'raw' ), $order->get_currency() );
				$data['currency']       = $order->get_currency();
				$data['pre_order']      = $this->order_contains_pre_order( $order );
				$data['order']          = [ 'id' => $order->get_id(), 'key' => $order->get_order_key() ];
			} else {
				$data['needs_shipping'] = WC()->cart->needs_shipping();
				if ( 'checkout' === $page && is_cart() ) {
					$page = 'cart';
				} elseif ( is_add_payment_method_page() ) {
					$page = 'add_payment_method';
				}
			}
		} elseif ( 'product' === $page ) {
			global $product;
			$price                  = wc_get_price_to_display( $product );
			$data['needs_shipping'] = $product->needs_shipping();
			$data['product']        = [
				'id'          => $product->get_id(),
				'price'       => $price,
				'price_cents' => Utils\Currency::add_number_precision( $price, get_woocommerce_currency() ),
				'variation'   => false,
			];
		}
		/**
		 * @since 1.0.0
		 *
		 * @param array            $data
		 * @param string           $page
		 * @param Abstract_Gateway $object
		 */
		$data = wp_json_encode( apply_filters( 'sswps/output_display_items', $data, $page, $this ) );
		$data = function_exists( 'wc_esc_json' ) ? wc_esc_json( $data ) : _wp_specialchars( $data, ENT_QUOTES, 'UTF-8', true );
		printf( '<input type="hidden" class="%1$s" data-gateway="%2$s"/>', "woocommerce_{$this->id}_gateway_data {$page}-page", $data );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param       $scripts
	 *
	 * @param array $deps
	 */
	public function get_mini_cart_dependencies( $deps, $scripts ) {
		return $deps;
	}

	/**
	 * @since 1.0.0
	 * @return array
	 */
	public function get_shipping_packages() {
		$packages = WC()->shipping()->get_packages();
		if ( empty( $packages ) && Checker::is_woocommerce_subscriptions_active() && \WC_Subscriptions_Cart::cart_contains_free_trial() ) {
			// there is a subscription with a free trial in the cart. Shipping packages will be in the recurring cart.
			\WC_Subscriptions_Cart::set_calculation_type( 'recurring_total' );
			$count = 0;
			if ( isset( WC()->cart->recurring_carts ) ) {
				foreach ( WC()->cart->recurring_carts as $recurring_cart_key => $recurring_cart ) {
					foreach ( $recurring_cart->get_shipping_packages() as $i => $base_package ) {
						if ( version_compare( WC_Subscriptions::$version, '5.1.2', '<' ) ) {
							$packages[ $recurring_cart_key . '_' . $count ] = \WC_Subscriptions_Cart::get_calculated_shipping_for_package( $base_package );
							continue;
						}

						$packages[ $recurring_cart_key . '_' . $count ] = WC()->shipping()->calculate_shipping_for_package( $base_package );
					}
					$count++;
				}
			}
			\WC_Subscriptions_Cart::set_calculation_type( 'none' );
		}

		return $packages;
	}

	/**
	 * @since 1.0.0
	 *
	 * @param array   $items
	 *
	 * @param WC_Cart $cart
	 *
	 * @return array
	 */
	public function get_display_items_for_cart( $cart, $items = [] ) {
		$incl_tax = sswps_display_prices_including_tax();
		foreach ( $cart->get_cart() as $cart_item ) {
			/**
			 *
			 * @var WC_Product $product
			 */
			$product = $cart_item['data'];
			$qty     = $cart_item['quantity'];
			$label   = $qty > 1 ? sprintf( '%s X %s', $product->get_name(), $qty ) : $product->get_name();
			$price   = $incl_tax ? wc_get_price_including_tax( $product, [ 'qty' => $qty ] ) : wc_get_price_excluding_tax( $product, [ 'qty' => $qty ] );
			$items[] = $this->get_display_item_for_cart( $price, $label, 'product', $cart_item, $cart );
		}
		if ( $cart->needs_shipping() ) {
			$price   = $incl_tax ? $cart->get_shipping_total() + $cart->get_shipping_tax() : $cart->get_shipping_total();
			$items[] = $this->get_display_item_for_cart( $price, __( 'Shipping', 'simple-secure-stripe' ), 'shipping' );
		}
		foreach ( $cart->get_fees() as $fee ) {
			$price   = $incl_tax ? $fee->total + $fee->tax : $fee->total;
			$items[] = $this->get_display_item_for_cart( $price, $fee->name, 'fee', $fee, $cart );
		}
		if ( 0 < $cart->get_cart_discount_total() ) {
			$price   = -1 * abs( $incl_tax ? $cart->get_cart_discount_total() + $cart->get_cart_discount_tax_total() : $cart->get_cart_discount_total() );
			$items[] = $this->get_display_item_for_cart( $price, __( 'Discount', 'simple-secure-stripe' ), 'discount', $cart );
		}
		if ( ! $incl_tax && wc_tax_enabled() ) {
			$items[] = $this->get_display_item_for_cart( $cart->get_taxes_total(), __( 'Tax', 'simple-secure-stripe' ), 'tax', $cart );
		}

		return $items;
	}

	/**
	 * @since 1.0.0
	 *
	 * @param array    $items
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	protected function get_display_items_for_order( $order, $items = [] ) {
		foreach ( $order->get_items() as $item ) {
			$qty     = $item->get_quantity();
			$label   = $qty > 1 ? sprintf( '%s X %s', $item->get_name(), $qty ) : $item->get_name();
			$items[] = $this->get_display_item_for_order( $item->get_subtotal(), $label, $order, 'item', $item );
		}
		if ( 0 < $order->get_shipping_total() ) {
			$items[] = $this->get_display_item_for_order( $order->get_shipping_total(), __( 'Shipping', 'simple-secure-stripe' ), $order, 'shipping' );
		}
		if ( 0 < $order->get_total_discount() ) {
			$items[] = $this->get_display_item_for_order( -1 * $order->get_total_discount(), __( 'Discount', 'simple-secure-stripe' ), $order, 'discount' );
		}
		if ( 0 < (float) $order->get_fees() ) {
			$fee_total = 0;
			foreach ( $order->get_fees() as $fee ) {
				$fee_total += (float) $fee->get_total( 'raw' );
			}
			$items[] = $this->get_display_item_for_order( $fee_total, __( 'Fees', 'simple-secure-stripe' ), $order, 'fee' );
		}
		if ( 0 < $order->get_total_tax() ) {
			$items[] = $this->get_display_item_for_order( $order->get_total_tax(), __( 'Tax', 'simple-secure-stripe' ), $order, 'tax' );
		}

		return $items;
	}

	/**
	 * @since 1.0.0
	 *
	 * @param string $label
	 * @param string $type
	 * @param mixed  ...$args
	 *
	 * @param float  $price
	 *
	 * @return array
	 */
	protected function get_display_item_for_cart( $price, $label, $type, ...$args ) {
		return [
			'label'   => $label,
			'pending' => false,
			'amount'  => Utils\Currency::add_number_precision( $price ),
		];
	}

	/**
	 * @param float    $price
	 * @param string   $label
	 * @param WC_Order $order
	 * @param string   $type
	 * @param mixed    ...$args
	 */
	protected function get_display_item_for_order( $price, $label, $order, $type, ...$args ) {
		return [
			'label'   => $label,
			'pending' => false,
			'amount'  => Utils\Currency::add_number_precision( $price, $order->get_currency() ),
		];
	}

	/**
	 * @since 1.0.0
	 *
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	protected function get_display_item_for_product( $product ) {
		return [
			'label'   => esc_attr( $product->get_name() ),
			'pending' => true,
			'amount'  => Utils\Currency::add_number_precision( $product->get_price() ),
		];
	}

	/**
	 * @since 1.0.0
	 *
	 * @param       $sort
	 *
	 * @param array $methods
	 *
	 * @return array
	 */
	public function get_formatted_shipping_methods( $methods = [] ) {
		if ( Checker::is_woocommerce_subscriptions_active() && \WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment ) {
			return $methods;
		} else {
			$methods        = [];
			$chosen_methods = [];
			$packages       = $this->get_shipping_packages();
			$incl_tax       = sswps_display_prices_including_tax();
			foreach ( WC()->session->get( 'chosen_shipping_methods', [] ) as $i => $id ) {
				$chosen_methods[] = $this->get_shipping_method_id( $id, $i );
			}
			foreach ( $packages as $i => $package ) {
				foreach ( $package['rates'] as $rate ) {
					$price     = $incl_tax ? $rate->cost + $rate->get_shipping_tax() : $rate->cost;
					$methods[] = $this->get_formatted_shipping_method( $price, $rate, $i, $package, $incl_tax );
				}
			}

			/**
			 * Sort shipping methods so the selected method is first in the array.
			 */
			usort( $methods, function( $method ) use ( $chosen_methods ) {
				foreach ( $chosen_methods as $id ) {
					if ( in_array( $id, $method, true ) ) {
						return -1;
					}
				}

				return 1;
			} );
		}

		/**
		 * @since 1.0.0
		 *
		 * @param array            $methods
		 * @param Abstract_Gateway $object
		 *
		 */
		return apply_filters( 'sswps/get_formatted_shipping_methods', $methods, $this );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param WC_Shipping_Rate $rate
	 * @param string           $i
	 * @param array            $package
	 * @param bool             $incl_tax
	 *
	 * @param float            $price
	 *
	 * @return array
	 */
	public function get_formatted_shipping_method( $price, $rate, $i, $package, $incl_tax ) {
		$method = [
			'id'     => $this->get_shipping_method_id( $rate->get_id(), $i ),
			'label'  => $this->get_formatted_shipping_label( $price, $rate, $incl_tax ),
			'detail' => '',
			'amount' => Utils\Currency::add_number_precision( $price ),
		];
		if ( $incl_tax ) {
			if ( $rate->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
				$method['detail'] = WC()->countries->inc_tax_or_vat();
			}
		} else {
			if ( $rate->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
				$method['detail'] = WC()->countries->ex_tax_or_vat();
			}
		}

		return $method;
	}

	/**
	 * @param string $id
	 * @param string $index
	 *
	 * @return mixed
	 */
	protected function get_shipping_method_id( $id, $index ) {
		return sprintf( '%s:%s', $index, $id );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param WC_Shipping_Rate $rate
	 * @param bool             $incl_tax
	 *
	 * @param float            $price
	 */
	protected function get_formatted_shipping_label( $price, $rate, $incl_tax ) {
		return sprintf( '%s', esc_attr( $rate->get_label() ) );
	}

	/**
	 * Returns true if a scheduled subscription payment is being processed.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	protected function is_processing_scheduled_payment() {
		return doing_action( 'woocommerce_scheduled_subscription_payment_' . $this->id );
	}

	public function get_transaction_url( $order ) {
		if ( sswps_order_mode( $order ) === 'test' ) {
			$this->view_transaction_url = 'https://dashboard.stripe.com/test/payments/%s';
		} else {
			$this->view_transaction_url = 'https://dashboard.stripe.com/payments/%s';
		}

		return parent::get_transaction_url( $order );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param WC_Subscription $subscription
	 *
	 * @return array
	 */
	protected function process_change_payment_method_request( $subscription ) {
		if ( ! $this->use_saved_source() ) {
			$result = $this->save_payment_method( $this->get_new_source_token(), $subscription );
			if ( is_wp_error( $result ) ) {
				/* translators: %s - error reason */
				wc_add_notice( sprintf( __( 'Error saving payment method for subscription. Reason: %s', 'simple-secure-stripe' ), $result->get_error_message() ), 'error' );

				return [ 'result' => 'error' ];
			}
		} else {
			$this->payment_method_token = $this->get_saved_source_id();
		}
		$token = $this->get_token( $this->payment_method_token, $subscription->get_user_id() );
		// update the meta data needed by the gateway to process a subscription payment.
		$subscription->update_meta_data( Constants::PAYMENT_METHOD_TOKEN, $this->payment_method_token );
		$subscription->update_meta_data( Constants::CUSTOMER_ID, $token->get_customer_id() );
		if ( $token ) {
			$subscription->set_payment_method_title( $token->get_payment_method_title() );
		}
		$subscription->save();

		return [ 'result' => 'success', 'redirect' => wc_get_page_permalink( 'myaccount' ) ];
	}

	/**
	 * @param WC_Subscription $subscription
	 * @param WC_Order         $order
	 */
	public function update_failing_payment_method( $subscription, $order ) {
		if ( ( $token = $this->get_token( $order->get_meta( Constants::PAYMENT_METHOD_TOKEN ), $order->get_customer_id() ) ) ) {
			$subscription->update_meta_data( Constants::PAYMENT_METHOD_TOKEN, $token->get_token() );
			$subscription->update_meta_data( Constants::CUSTOMER_ID, $token->get_customer_id() );
			$subscription->set_payment_method_title( $token->get_payment_method_title( $this->get_option( 'method_format' ) ) );
			$subscription->save();
		}
	}

	/**
	 * @since 1.0.0
	 *
	 * @param array $options
	 *
	 * @return mixed|void
	 */
	public function get_element_options( $options = [] ) {
		$options = array_merge( [ 'locale' => sswps_get_site_locale() ], $options );

		return apply_filters( 'sswps/get_element_options', $options, $this );
	}

	public function get_afterpay_message_options() {
		return [];
	}

	public function get_mandate_text() {
		return '';
	}

	public function get_element_params() {
		return [];
	}

	public function get_supported_locales() {
		return [];
	}

	public function is_installment_available() {
		return false;
	}

	public function get_button_height() {
		return '';
	}

	public function get_payment_button_locale() {
		return '';
	}

	public function get_required_parameters() {
		return [];
	}

	public function get_card_form_options() {
		return [];
	}

	public function get_card_custom_field_options() {
		return [];
	}

	public function is_custom_form_active() {
		return false;
	}

	public function is_payment_element_active() {
		return false;
	}

	public function postal_enabled() {
		return false;
	}

	public function get_custom_form() {
		return '';
	}

	/**
	 * @since 3.3.42
	 * @return bool|mixed|void|null
	 */
	public function show_save_payment_method_html() {
		if ( ! $this->supports_save_payment_method ) {
			return false;
		}

		$page = sswps_get_current_page();

		if ( 'checkout' === $page ) {
			if ( Checker::is_woocommerce_subscriptions_active() ) {
				if ( WC_Subscriptions_Cart::cart_contains_subscription() ) {
					return false;
				}
				if ( wcs_cart_contains_renewal() ) {
					return false;
				}
			}
			// @since 3.1.5
			if ( sswps_pre_orders_active() && WC_Pre_Orders_Cart::cart_contains_pre_order() ) {
				return ! WC_Pre_Orders_Product::product_is_charged_upon_release( WC_Pre_Orders_Cart::get_pre_order_product() );
			}

			return apply_filters( "wc_{$this->id}_show_save_source", $this->is_active( 'save_card_enabled' ) );
		} elseif ( in_array( $page, array( 'add_payment_method', 'change_payment_method' ) ) ) {
			return false;
		} elseif ( 'order_pay' === $page ) {
			return is_user_logged_in() && $this->is_active( 'save_card_enabled' );
		}

		return false;
	}

	public function is_mandate_required( $order = null ) {
		$mode = ! $order ? sswps_mode() : sswps_order_mode( $order );

		return App::get( Settings\Account::class )->get_account_country( $mode ) === 'IN';
	}
}
