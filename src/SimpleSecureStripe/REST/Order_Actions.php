<?php

namespace SimpleSecureWP\SimpleSecureStripe\REST;

use Exception;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Checker;
use SimpleSecureWP\SimpleSecureStripe\Traits;
use WC_Payment_Tokens;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 *
 * @author Simple & Secure WP
 * @package Stripe/Controllers
 *
 */
class Order_Actions extends Abstract_REST {

	use Traits\Frontend;

	protected $namespace = 'order~action';

	public function register_routes() {
		register_rest_route(
			$this->rest_uri(),
			'capture',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'capture' ],
				'permission_callback' => [ $this, 'order_actions_permission_check' ],
				'args'                => [
					'order_id' => [
						'required'          => true,
						'type'              => 'int',
						'validate_callback' => [ $this, 'validate_order_id' ],
					],
					'amount'   => [
						'required' => true,
						'type'     => 'float',
					],
				],
			]
		);
		register_rest_route(
			$this->rest_uri(),
			'void',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'void' ],
				'permission_callback' => [ $this, 'order_actions_permission_check' ],
				'args'                => [
					'order_id' => [
						'required'          => true,
						'type'              => 'number',
						'validate_callback' => [
							$this,
							'validate_order_id',
						],
					],
				],
			]
		);
		register_rest_route(
			$this->rest_uri(),
			'pay',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'process_payment' ],
				'permission_callback' => [ $this, 'order_actions_permission_check' ],
				'args'                => [
					'order_id' => [
						'required'          => true,
						'type'              => 'number',
						'validate_callback' => [
							$this,
							'validate_order_id',
						],
					],
				],
			]
		);
		register_rest_route(
			$this->rest_uri(),
			'customer-payment-methods',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'customer_payment_methods' ],
				'permission_callback' => [ $this, 'order_actions_permission_check' ],
				'args'                => [
					'customer_id' => [
						'required' => true,
						'type'     => 'number',
					],
				],
			]
		);
		register_rest_route(
			$this->rest_uri(),
			'charge-view',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'charge_view' ],
				'permission_callback' => [ $this, 'order_actions_permission_check' ],
				'args'                => [
					'order_id' => [
						'required' => true,
						'type'     => 'number',
					],
				],
			]
		);
	}

	/**
	 * Return true if the order_id is a valid post.
	 *
	 * @param int $order_id
	 */
	public function validate_order_id( $order_id ) {
		return null !== wc_get_order( $order_id );
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 */
	public function capture( $request ) {
		$order_id = $request->get_param( 'order_id' );
		$order    = wc_get_order( $order_id );
		$amount   = $request->get_param( 'amount' );
		if ( ! is_numeric( $amount ) ) {
			return new WP_Error(
				'invalid_data',
				__( 'Invalid amount entered.', 'simple-secure-stripe' ),
				[
					'success' => false,
					'status'  => 200,
				]
			);
		}
		try {
			/**
			 *
			 * @var Gateways\Abstract_Gateway $gateway
			 */
			$gateway = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ];
			$result  = $gateway->capture_charge( $amount, $order );
			if ( is_wp_error( $result ) ) {
				throw new Exception( $result->get_error_message() );
			}

			return rest_ensure_response( [] );
		} catch ( Exception $e ) {
			return new WP_Error( 'capture-error', $e->getMessage(), [ 'status' => 200 ] );
		}
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 */
	public function void( $request ) {
		$order_id = $request->get_param( 'order_id' );
		$order    = wc_get_order( $order_id );
		/**
		 * When the order's status is set to cancelled, the sswps_order_cancelled
		 * function is called, which voids the charge.
		 */
		$order->update_status( 'cancelled' );

		return rest_ensure_response( [] );
	}

	/**
	 * Process a payment as an admin.
	 *
	 * @param WP_REST_Request $request
	 */
	public function process_payment( $request ) {
		$order_id       = $request->get_param( 'order_id' );
		$payment_type   = $request->get_param( 'payment_type' );
		$order          = wc_get_order( $order_id );
		$use_token      = $payment_type === 'token';
		$token          = null;
		$payment_method = null;
		try {
			// perform some validations
			if ( $order->get_total( 'raw' ) == 0 ) {
				if ( ! Checker::is_woocommerce_subscriptions_active() ) {
					throw new Exception( __( 'Order total must be greater than zero.', 'simple-secure-stripe' ) );
				} else {
					if ( ! wcs_order_contains_subscription( $order ) ) {
						throw new Exception( __( 'Order total must be greater than zero.', 'simple-secure-stripe' ) );
					}
				}
			}
			// update the order's customer ID if it has changed.
			if ( $order->get_customer_id() != $request->get_param( 'customer_id' ) ) {
				$order->set_customer_id( $request->get_param( 'customer_id' ) );
			}

			if ( $order->get_transaction_id() ) {
				throw new Exception(
					sprintf(
						/* translators: 1: transaction ID, 2: payment method. */
						__( 'This order has already been processed. Transaction ID: %1$s. Payment method: %2$s', 'simple-secure-stripe' ),
						$order->get_transaction_id(),
						$order->get_payment_method_title()
					)
				);
			}
			if ( ! $use_token ) {
				// only credit card payments are allowed for one off payments as an admin.
				$payment_method = 'sswps_cc';
			} elseif ( $payment_type === 'token' ) {
				$token_id = intval( $request->get_param( 'payment_token_id' ) );
				$token    = WC_Payment_Tokens::get( $token_id );
				if ( $token->get_user_id() !== $order->get_customer_id() ) {
					throw new Exception( __( 'Order customer Id and payment method customer Id do not match.', 'simple-secure-stripe' ) );
				}
				$payment_method = $token->get_gateway_id();
			}
			/**
			 *
			 * @var Gateways\Abstract_Gateway $gateway
			 */
			$gateway = WC()->payment_gateways()->payment_gateways()[ $payment_method ];
			// temporarily set the charge type of the gateway to whatever the admin has selected.
			$gateway->settings['charge_type'] = $request->get_param( 'sswps_charge_type' );
			// set the payment gateway to the order.
			$order->set_payment_method( $gateway->id );
			$order->save();
			if ( ! $use_token ) {
				$_POST[ $gateway->token_key ] = $request->get_param( 'payment_nonce' );
			} else {
				$gateway->set_payment_method_token( $token->get_token() );
			}

			// set intent attribute off_session. Stripe requires confirm to be true to use off session.
			add_filter( 'sswps/payment_intent_args', function( $args ) {
				if ( isset( $args['setup_future_usage'] ) && $args['setup_future_usage'] === 'off_session' ) {
					$args['off_session'] = false;
				} else {
					$args['off_session'] = true;
				}
				$args['confirm'] = true;

				return $args;
			} );

			$result = $gateway->process_payment( $order_id );

			if ( isset( $result['result'] ) && $result['result'] === 'success' ) {
				return rest_ensure_response( [ 'success' => true ] );
			} else {
				// create a new order since updates to the order were made during the process_payment call.
				$order = wc_get_order( $order_id );
				$order->update_status( 'pending' );

				return new WP_Error(
					'order-error',
					$this->get_error_messages(),
					[
						'status'  => 200,
						'success' => false,
					]
				);
			}
		} catch ( Exception $e ) {
			return new WP_Error( 'order-error', '<div class="woocommerce-error">' . $e->getMessage() . '</div>', [ 'status' => 200 ] );
		}
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 */
	public function customer_payment_methods( $request ) {
		$customer_id = $request->get_param( 'customer_id' );
		$tokens      = [];
		foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
			if ( $gateway instanceof Gateways\Abstract_Gateway ) {
				$tokens = array_merge( $tokens, \WC_Payment_Tokens::get_customer_tokens( $customer_id, $gateway->id ) );
			}
		}

		return rest_ensure_response(
			[
				'payment_methods' => array_map(
					function( $payment_method ) {
						return $payment_method->get_data();
					},
					$tokens
				),
			]
		);
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 */
	public function charge_view( $request ) {
		$order = wc_get_order( absint( $request->get_param( 'order_id' ) ) );
		/**
		 *
		 * @var Gateways\Abstract_Gateway $payment_method
		 */
		$payment_method = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ];
		try {
			// fetch the charge so data is up to date.
			$charge = Gateway::load( sswps_order_mode( $order ) )->charges->retrieve( $order->get_transaction_id() );

			$order->update_meta_data( Constants::CHARGE_STATUS, $charge->status );
			$order->save();
			ob_start();
			include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/metaboxes/charge-data-subview.php';
			$html = ob_get_clean();

			return rest_ensure_response(
				[
					'data' => [
						'order_id'     => $order->get_id(),
						'order_number' => $order->get_order_number(),
						'order_total'  => $order->get_total( 'raw' ),
						'charge'       => $charge->jsonSerialize(),
						'html'         => $html,
					],
				]
			);
		} catch ( Exception $e ) {
			return new WP_Error( 'charge-error', $e->getMessage(), [ 'status' => 200 ] );
		}
	}

	/**
	 * @param $request
	 */
	public function order_actions_permission_check( $request ) {
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			return new WP_Error(
				'permission-error',
				__( 'You do not have permissions to access this resource.', 'simple-secure-stripe' ),
				[
					'status' => 403,
				]
			);
		}

		return true;
	}

}
