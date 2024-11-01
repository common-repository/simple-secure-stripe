<?php

namespace SimpleSecureWP\SimpleSecureStripe\REST;

use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use Exception;
use WC_Order;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Controller which handles Payment Intent related actions such as creation.
 *
 * @author Simple & Secure WP
 * @package Stripe/Controllers
 *
 */
class Payment_Intent extends Abstract_REST {

	protected $namespace = '';

	public function register_routes() {
		register_rest_route(
			$this->rest_uri(),
			'setup-intent',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => [
					$this,
					'create_setup_intent',
				],
				'args'                => [
					'payment_method' => [
						'required' => true,
					],
				],
			]
		);
		register_rest_route(
			$this->rest_uri(),
			'sync-payment-intent',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'sync_payment_intent' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'order_id'      => [ 'required' => true ],
					'client_secret' => [ 'required' => true ],
				],
			]
		);
		register_rest_route( $this->rest_uri(), 'payment-intent', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'create_payment_intent_from_cart' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'payment_method'    => [ 'required' => true ],
				'payment_method_id' => [ 'required' => true ],
			],
		] );
		register_rest_route( $this->rest_uri(), 'order/payment-intent', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'create_payment_intent_from_order' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'order_id'          => [ 'required' => true ],
				'order_key'         => [ 'required' => true ],
				'payment_method'    => [ 'required' => true ],
				'payment_method_id' => [ 'required' => true ],
			],
		] );
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 */
	public function create_setup_intent( $request ) {
		/**
		 * @var Gateways\Abstract_Gateway $payment_method
		 */
		$payment_method = WC()->payment_gateways()->payment_gateways()[ $request['payment_method'] ];
		$params         = [ 'usage' => 'off_session', 'payment_method_types' => [ $payment_method->get_payment_method_type() ] ];
		// @3.3.12 - check if 3DS is being forced
		if ( $payment_method->is_active( 'force_3d_secure' ) ) {
			$params['payment_method_options']['card']['request_three_d_secure'] = 'any';
		}
		$intent = $payment_method->payment_object->get_gateway()->setupIntents->create(
			apply_filters( 'sswps/create_setup_intent_params', $params, $payment_method, $request )
		);
		try {
			if ( is_wp_error( $intent ) ) {
				throw new Exception( $intent->get_error_message() );
			}

			if ( WC()->session ) {
				WC()->session->set( Constants::SETUP_INTENT_ID, $intent->id );
			}

			return rest_ensure_response( [ 'intent' => [ 'client_secret' => $intent->client_secret ] ] );
		} catch ( Exception $e ) {
			return new WP_Error(
				'payment-intent-error',
				/* translators: %s: error message. */
				sprintf( __( 'Error creating payment intent. Reason: %s', 'simple-secure-stripe' ), $e->getMessage() ),
				[
					'status' => 200,
				]
			);
		}
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 */
	public function sync_payment_intent( $request ) {
		try {
			$order = wc_get_order( absint( $request->get_param( 'order_id' ) ) );
			if ( ! $order ) {
				throw new Exception( __( 'Invalid order id provided', 'simple-secure-stripe' ) );
			}

			$intent = Gateway::load()->paymentIntents->retrieve( $order->get_meta( Constants::PAYMENT_INTENT_ID ) );

			if ( ! hash_equals( $intent->client_secret, $request->get_param( 'client_secret' ) ) ) {
				throw new Exception( __( 'You are not authorized to update this order.', 'simple-secure-stripe' ) );
			}

			$order->update_meta_data( Constants::PAYMENT_INTENT, Utils\Misc::sanitize_intent( $intent->toArray() ) );
			$order->save();

			return rest_ensure_response( [ 'success' => true ] );
		} catch ( Exception $e ) {
			return new WP_Error( 'payment-intent-error', $e->getMessage(), [ 'status' => 200 ] );
		}
	}

	/**
	 * @param WP_REST_Request $request
	 */
	public function create_payment_intent_from_cart( $request ) {
		try {
			$payment_intent = Utils\Misc::get_payment_intent_from_session();
			$order_id       = absint( WC()->session->get( 'order_awaiting_payment' ) );
			$order          = $order_id ? wc_get_order( $order_id ) : null;
			$result         = $this->create_payment_intent( $request, $payment_intent, $order );
			Utils\Misc::save_payment_intent_to_session( $result->payment_intent, $order );

			return rest_ensure_response( $result );
		} catch ( \Exception $e ) {
			return new WP_Error( 'payment-intent-error', $e->getMessage(), [ 'status' => 200 ] );
		}
	}

	/**
	 * @param WP_REST_Request $request
	 */
	public function create_payment_intent_from_order( $request ) {
		$order = wc_get_order( absint( $request['order_id'] ) );

		try {
			if ( ! $order || ! hash_equals( $order->get_order_key(), $request['order_key'] ) ) {
				throw new Exception( __( 'You are not authorized to update this order.', 'simple-secure-stripe' ) );
			}
			$payment_intent = $order->get_meta( Constants::PAYMENT_INTENT );
			if ( $payment_intent ) {
				$payment_intent = (object) $payment_intent;
			}

			$result = $this->create_payment_intent( $request, $payment_intent, $order );
			$order->update_meta_data( Constants::PAYMENT_INTENT, Utils\Misc::sanitize_intent( $result->payment_intent->toArray() ) );
			$order->save();

			return rest_ensure_response( $result );
		} catch ( Exception $e ) {
			return new WP_Error( 'payment-intent-error', $e->getMessage(), [ 'status' => 200 ] );
		}
	}

	/**
	 * @param WP_REST_Request $request
	 */
	private function create_payment_intent( $request, $payment_intent = null, $order = null ) {
		/**
		 * @var Gateways\CC $payment_method
		 */
		$payment_method = WC()->payment_gateways()->payment_gateways()[ $request['payment_method'] ];
		$params         = $this->get_create_payment_intent_params( $request, $payment_method, $order );
		if ( $payment_intent ) {
			$payment_intent = $payment_method->gateway->paymentIntents->retrieve( $payment_intent->id );
			if (
				in_array( $payment_intent->status, [ 'succeeded', 'requires_capture' ] )
				|| $params['confirmation_method'] !== $payment_intent->confirmation_method
			) {
				Utils\Misc::delete_payment_intent_to_session();

				return $this->create_payment_intent( $request );
			}
			unset( $params['confirmation_method'] );
			$payment_intent = $payment_method->gateway->paymentIntents->update( $payment_intent->id, $params );
		} else {
			$payment_intent = $payment_method->gateway->paymentIntents->create( $params );
		}

		$response     = [ 'payment_intent' => $payment_intent ];
		$installments = [];
		if ( $payment_intent->payment_method_options->offsetGet( 'card' )->installments->enabled ) {
			$installments = \SimpleSecureWP\SimpleSecureStripe\Features\Installments\Formatter::from_plans( $payment_intent->payment_method_options->offsetGet( 'card' )->installments->available_plans, $payment_intent->amount, $payment_intent->currency );
		}
		$response['installments_html'] = sswps_get_template_html( 'installment-plans.php', [ 'installments' => $installments ] );
		$response['installments']      = $installments;

		return (object) $response;
	}

	/**
	 * @param WP_REST_Request $request
	 * @param Gateways\CC     $payment_method
	 * @param null|WC_Order   $order
	 */
	private function get_create_payment_intent_params( $request, $payment_method, $order = null ) {
		$params = [
			'payment_method'         => $request['payment_method_id'],
			'confirmation_method'    => $payment_method->get_confirmation_method(),
			'payment_method_types'   => [ $payment_method->get_payment_method_type() ],
			'payment_method_options' => [ 'card' => [ 'installments' => [ 'enabled' => $payment_method->installments->is_available( $order ) ] ] ],
		];
		if ( $order ) {
			$params['amount']   = Utils\Currency::add_number_precision( $order->get_total( 'raw' ), $order->get_currency() );
			$params['currency'] = $order->get_currency();
			if ( $order->get_customer_id() && ( ( $customer_id = sswps_get_customer_id( $order->get_customer_id() ) ) ) ) {
				$params['customer'] = $customer_id;
			}
		} else {
			$currency           = get_woocommerce_currency();
			$total              = WC()->cart->get_total( 'raw' );
			$params['amount']   = Utils\Currency::add_number_precision( $total, $currency );
			$params['currency'] = $currency;
			if ( is_user_logged_in() && ( ( $customer_id = sswps_get_customer_id( get_current_user_id() ) ) ) ) {
				$params['customer'] = $customer_id;
			}
		}

		return $params;
	}

}
