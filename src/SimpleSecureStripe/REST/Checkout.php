<?php

namespace SimpleSecureWP\SimpleSecureStripe\REST;

use SimpleSecureWP\SimpleSecureStripe\Constants;
use Exception;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Checker;
use SimpleSecureWP\SimpleSecureStripe\Product_Gateway_Option;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use SimpleSecureWP\SimpleSecureStripe\Traits;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WC_Order;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Controllers
 */
class Checkout extends Abstract_REST {

	use Traits\Frontend;

	protected $namespace = '';

	private $order_review = false;

	/**
	 *
	 * @var Gateways\Abstract_Gateway
	 */
	private $gateway = null;

	public function register_routes() {
		register_rest_route(
			$this->rest_uri(),
			'checkout',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'process_checkout' ],
				'permission_callback' => '__return_true',
			]
		);
		register_rest_route(
			$this->rest_uri(),
			'checkout/payment',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'process_payment' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'order_id'  => [ 'required' => true ],
					'order_key' => [ 'required' => true ],
				],
			]
		);
		register_rest_route(
			$this->rest_uri(),
			'order-pay',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'process_order_pay' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'order_id'       => [ 'required' => true ],
					'order_key'      => [ 'required' => true ],
					'payment_method' => [ 'required' => true ],
				],
			]
		);
	}

	/**
	 * Process the WC Order
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function process_checkout( $request ) {
		$this->actions();
		$checkout       = WC()->checkout();
		$payment_method = $request->get_param( 'payment_method' );
		/**
		 *
		 * @var Gateways\Abstract_Gateway $gateway
		 */
		$gateway = WC()->payment_gateways()->payment_gateways()[ $payment_method ];
		$this->gateway = $gateway;

		try {
			do_action( 'sswps/rest_process_checkout', $request, $gateway );
			if ( ! is_user_logged_in() ) {
				$this->create_customer( $request );
			}
			// set the checkout nonce so no exceptions are thrown.
			$_REQUEST['_wpnonce'] = $_POST['_wpnonce'] = wp_create_nonce( 'woocommerce-process_checkout' );

			if ( 'product' == $request->get_param( 'page_id' ) ) {
				$option = new Product_Gateway_Option( current( WC()->cart->get_cart() )['data'], $gateway );
				if ( $option->has_product() ) {
					$gateway->settings['charge_type'] = $option->get_option( 'charge_type' );
				}
			}
			$this->required_post_data();
			$checkout->process_checkout();
		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );
		}
		if ( wc_notice_count( 'error' ) > 0 ) {
			return $this->send_response( false );
		}

		return $this->send_response( true );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request
	 *
	 */
	public function process_payment( $request ) {
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		// Indicator so we know payment is being processed after a 3DS authentication.
		wc_maybe_define_constant( Constants::PROCESSING_PAYMENT, true );
		try {
			$order_id = absint( $request->get_param( 'order_id' ) );
			$order    = wc_get_order( $order_id );
			if ( ! $order ) {
				throw new Exception( __( 'Invalid order ID.', 'simple-secure-stripe' ) );
			}
			if ( ! $order->key_is_valid( $request->get_param( 'order_key' ) ) ) {
				throw new Exception( __( 'Invalid order key.', 'simple-secure-stripe' ) );
			}
			/**
			 * @var Gateways\Abstract_Gateway $payment_method
			 */
			$payment_method = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ];
			if ( empty( $_POST ) ) {
				$sanitized_post = Request::sanitize_deep( $_POST );
				$_POST          = array_merge( $sanitized_post, $request->get_json_params() );
			}
			$result = $payment_method->process_payment( $order_id );

			if ( isset( $result['result'] ) && 'success' === $result['result'] ) {
				return $this->send_response( true, $result );
			}

			return $this->send_response( false, $result );
		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );

			return $this->send_response( false );
		}
	}

	/**
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function process_order_pay( $request ) {
		global $wp;

		/**
		 * Only set when the order pay is being processed via Ajax.
		 */
		wc_maybe_define_constant( Constants::PROCESSING_ORDER_PAY, true );

		/**
		 * @var Gateways\Abstract_Gateway $payment_method
		 */
		$payment_method = WC()->payment_gateways()->payment_gateways()[ $request['payment_method'] ];
		$order_id       = absint( $request['order_id'] );
		$order_key      = $request['order_key'];
		$order          = wc_get_order( $order_id );
		try {
			if ( ! $order || ! hash_equals( $order_key, $order->get_order_key() ) ) {
				throw new Exception( __( 'You are not authorized to update this order.', 'simple-secure-stripe' ) );
			}
			$wp->set_query_var( 'order-pay', $order_id );
			$order->set_payment_method( $payment_method->id );
			if ( $payment_method->payment_object instanceof Payment_Intent ) {
				$payment_method->payment_object->set_update_payment_intent( true );
			}
			$response = [ 'success' => true, 'needs_confirmation' => false ];
			$result   = $payment_method->payment_object->process_payment( $order );
			if ( is_wp_error( $result ) ) {
				throw new Exception( $result->get_error_message() );
			}
			if ( ! $result->complete_payment ) {
				if ( Utils\Misc::redirect_url_has_hash( $result->redirect ) ) {
					$response['needs_confirmation'] = true;
					$response['data']               = Utils\Misc::parse_url_hash( $result->redirect );
				} else {
					$response['redirect'] = $result->redirect;
				}
			}

			return rest_ensure_response( $response );
		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );

			return new WP_Error( 'order-pay-error', $this->get_error_messages(), [ 'status' => 200 ] );
		}
	}


	/**
	 *
	 * @param WP_REST_Request $request
	 */
	private function create_customer( $request ) {
		$create = WC()->checkout()->is_registration_required();
		// create an account for the user if it's required for things like subscriptions.
		if ( Checker::is_woocommerce_subscriptions_active() && \WC_Subscriptions_Cart::cart_contains_subscription() ) {
			$create = true;
		}
		if ( $create ) {
			$password = wp_generate_password();
			$username = $email = $request->get_param( 'billing_email' );
			$result   = wc_create_new_customer( $email, $username, $password );
			if ( $result instanceof WP_Error ) {
				// for email exists errors you want customer to either login or use a different email address.
				throw new Exception( $result->get_error_message() );
			}

			// log the customer in
			wp_set_current_user( $result );
			wc_set_customer_auth_cookie( $result );

			// As we are now logged in, cart will need to refresh to receive updated nonces
			WC()->session->set( 'reload_checkout', true );
		}
	}

	private function send_response( $success, $defaults = [] ) {
		$reload = WC()->session->get( 'reload_checkout', false );
		$data   = wp_parse_args( $defaults, [
			'result'   => $success ? 'success' : 'failure',
			'messages' => $reload ? null : $this->get_error_messages(),
			'reload'   => $reload,
		] );
		unset( WC()->session->reload_checkout );

		return rest_ensure_response( $data );
	}

	public function validate_payment_method( $payment_method ) {
		$gateways = WC()->payment_gateways()->payment_gateways();

		return isset( $gateways[ $payment_method ] ) ? true : new WP_Error( 'validation-error', 'Please choose a valid payment method.' );
	}

	private function actions() {
		add_action( 'woocommerce_after_checkout_validation', [ $this, 'after_checkout_validation' ], 10, 2 );
		add_filter( 'woocommerce_checkout_posted_data', [ $this, 'filter_posted_data' ] );
	}

	/**
	 *
	 * @param WC_Order                  $order
	 * @param Gateways\Abstract_Gateway $gateway
	 */
	public function set_stashed_cart( $order, $gateway ) {
		sswps_restore_cart( WC()->cart );
	}

	/**
	 *
	 * @param array    $data
	 * @param WP_Error $errors
	 */
	public function after_checkout_validation( $data, $errors ) {
		if ( $errors->get_error_codes() ) {
			sswps_log_info( sprintf( __CLASS__ . '::checkout errors: %s', print_r( $errors->get_error_codes(), true ) ) );
			wc_add_notice(
				apply_filters(
					'sswps_after_checkout_validation_notice', __( 'Please review your order details then click Place Order.', 'simple-secure-stripe' ),
					$data,
					$errors
				),
				'notice'
			);
			wp_send_json(
				[
					'result'   => 'success',
					'redirect' => $this->get_order_review_url(),
					'reload'   => false,
				],
				200
			);
		}
	}

	private function required_post_data() {
		if ( WC()->cart->needs_shipping() ) {
			$_POST['ship_to_different_address'] = true;
		}
		if ( wc_get_page_id( 'terms' ) > 0 ) {
			$_POST['terms'] = 1;
		}
	}

	private function get_order_review_url() {
		return add_query_arg(
			[
				'_stripe_order_review' => rawurlencode(
					base64_encode(
						wp_json_encode( [
							'payment_method' => $this->gateway->id,
							'payment_nonce'  => $this->gateway->get_payment_source(),
						] )
					)
				),
			],
			wc_get_checkout_url()
		);
	}

	/**
	 *
	 * @param int      $order_id
	 * @param array    $posted_data
	 * @param WC_Order $order
	 */
	public function checkout_order_processed( $order_id, $posted_data, $order ) {
		if ( $this->order_review ) {
			wc_add_notice( __( 'Please review your order details then click Place Order.', 'simple-secure-stripe' ), 'notice' );
			wp_send_json(
				[
					'result'   => 'success',
					'redirect' => $this->get_order_review_url(),
				],
				200
			);
		}
	}

	public function post_payment_processes( $order, $gateway ) {
		sswps_restore_cart( WC()->cart );
		$data = WC()->session->get( 'sswps_cart', [] );
		unset( $data['product_cart'] );
		WC()->session->set( 'sswps_cart', $data );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function filter_posted_data( $data ) {
		if ( isset( $data['shipping_method'], $data['shipping_country'], $data['shipping_state'] ) ) {
			$data['shipping_state'] = sswps_filter_address_state( $data['shipping_state'], $data['shipping_country'] );
		}

		return $data;
	}

}
