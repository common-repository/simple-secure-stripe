<?php

namespace SimpleSecureWP\SimpleSecureStripe\REST;

use Exception;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Traits;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Controller class that performs cart operations for client side requests.
 *
 * @author Simple & Secure WP
 * @package Stripe/Controllers
 *
 */
class Cart extends Abstract_REST {

	use Traits\Cart;
	use Traits\Frontend;

	protected $namespace = 'cart';

	public function register_routes() {
		register_rest_route(
			$this->rest_uri(),
			'shipping-method',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_shipping_method' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'shipping_method' => [ 'required' => true ],
					'payment_method'  => [ 'required' => true ],
				],
			]
		);
		register_rest_route(
			$this->rest_uri(),
			'shipping-address',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_shipping_address' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'payment_method' => [ 'required' => true ],
					'address'        => [ 'required' => true, 'validate_callback' => [ $this, 'validate_shipping_address' ] ],
				],
			]
		);
		register_rest_route(
			$this->rest_uri(),
			'add-to-cart',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'add_to_cart' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'product_id'     => [ 'required' => true ],
					'qty'            => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_quantity' ],
					],
					'payment_method' => [ 'required' => true ],
				],
			]
		);
		/**
		 *
		 * @since 1.0.0
		 */
		register_rest_route(
			$this->rest_uri(),
			'cart-calculation',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'cart_calculation' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'product_id'     => [ 'required' => true ],
					'qty'            => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_quantity' ],
					],
					'payment_method' => [ 'required' => true ],
				],
			]
		);
	}

	/**
	 *
	 * @param int             $qty
	 * @param WP_REST_Request $request
	 */
	public function validate_quantity( $qty, $request ) {
		if ( $qty == 0 ) {
			return $this->add_validation_error( new WP_Error( 'cart-error', __( 'Quantity must be greater than zero.', 'simple-secure-stripe' ) ) );
		}

		return true;
	}

	/**
	 * Update the shipping method chosen by the customer.
	 *
	 * @param WP_REST_Request $request
	 */
	public function update_shipping_method( $request ) {
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		$payment_method = $request->get_param( 'payment_method' );
		/**
		 *
		 * @var Gateways\Abstract_Gateway $gateway
		 */
		$gateway = WC()->payment_gateways()->payment_gateways()[ $payment_method ];

		sswps_update_shipping_methods( $this->get_shipping_method_from_request( $request ) );

		$this->add_ready_to_calc_shipping();

		// If this request is coming from product page, add the item to the cart.
		if ( 'product' == $request->get_param( 'page_id' ) ) {
			$this->empty_cart( WC()->cart );
			WC()->cart->add_to_cart( ...array_values( $this->get_add_to_cart_args( $request ) ) );
		}
		WC()->cart->calculate_totals();

		return rest_ensure_response(
			apply_filters(
				'sswps_update_shipping_method_response',
				[
					'data' => $gateway->get_update_shipping_method_response(
						[
							'newData'          => [
								'status'          => 'success',
								'total'           => [
									'amount'  => Utils\Currency::add_number_precision( WC()->cart->get_total( 'raw' ) ),
									'label'   => __( 'Total', 'simple-secure-stripe' ),
									'pending' => false,
								],
								'displayItems'    => $gateway->get_display_items(),
								'shippingOptions' => $gateway->get_formatted_shipping_methods(),
							],
							'shipping_methods' => WC()->session->get( 'chosen_shipping_methods', [] ),
						]
					),
				]
			)
		);
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 */
	public function update_shipping_address( $request ) {
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		$address        = $request->get_param( 'address' );
		$payment_method = $request->get_param( 'payment_method' );
		/**
		 *
		 * @var Gateways\Abstract_Gateway $gateway
		 */
		$gateway = WC()->payment_gateways()->payment_gateways()[ $payment_method ];
		try {
			sswps_update_customer_location( $address );

			$this->add_ready_to_calc_shipping();

			if ( 'product' == $request->get_param( 'page_id' ) ) {
				$this->empty_cart( WC()->cart );
				WC()->cart->add_to_cart( ...array_values( $this->get_add_to_cart_args( $request ) ) );
			}
			WC()->cart->calculate_totals();

			if ( ! $this->has_shipping_methods( $gateway->get_shipping_packages() ) ) {
				throw new Exception( 'No valid shipping methods.' );
			}

			$response = rest_ensure_response(
				apply_filters(
					'sswps_update_shipping_method_response',
					[
						'data' => $gateway->get_update_shipping_address_response(
							[
								'newData'         => [
									'status'          => 'success',
									'total'           => [
										'amount'  => Utils\Currency::add_number_precision( WC()->cart->get_total( 'raw' ) ),
										'label'   => __( 'Total', 'simple-secure-stripe' ),
										'pending' => false,
									],
									'displayItems'    => $gateway->get_display_items(),
									'shippingOptions' => $gateway->get_formatted_shipping_methods(),
								],
								'address'         => $address,
								'shipping_method' => WC()->session->get( 'chosen_shipping_methods', [] ),
							]
						),
					]
				)
			);
		} catch ( Exception $e ) {
			$response = new WP_Error(
				'address-error',
				$e->getMessage(),
				[
					'status'  => 200,
					'newData' => [ 'status' => 'invalid_shipping_address' ],
				]
			);
		}

		return $response;
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function add_to_cart( $request ) {
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		$payment_method = $request->get_param( 'payment_method' );
		/**
		 *
		 * @var Gateways\Abstract_Gateway $gateway
		 */
		$gateway   = WC()->payment_gateways()->payment_gateways()[ $payment_method ];
		$cart_args = $this->get_add_to_cart_args( $request );
		[ $product_id, $qty, $variation_id, $variation ] = array_values( $cart_args );

		$this->empty_cart( WC()->cart );

		if ( WC()->cart->add_to_cart( $product_id, $qty, $variation_id, $variation ) == false ) {
			return rest_ensure_response( new WP_Error( 'cart-error', $this->get_error_messages(), [ 'status' => 200 ] ) );
		} else {
			return rest_ensure_response(
				apply_filters(
					'sswps_add_to_cart_response',
					[
						'data' => $gateway->add_to_cart_response(
							[
								'total'           => wc_format_decimal( WC()->cart->get_total( 'raw' ), 2 ),
								'subtotal'        => wc_format_decimal( WC()->cart->get_subtotal(), 2 ),
								'totalCents'      => Utils\Currency::add_number_precision( WC()->cart->get_total( 'raw' ) ),
								'displayItems'    => $gateway->get_display_items( 'cart' ),
								'shippingOptions' => $gateway->get_formatted_shipping_methods(),
							]
						),
					],
					$gateway,
					$request
				)
			);
		}
	}

	/**
	 * Performs a cart calculation
	 *
	 * @param WP_REST_Request $request
	 */
	public function cart_calculation( $request ) {
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		$cart = clone WC()->cart;

		// clear cloned cart
		$this->empty_cart( $cart );

		$cart_args = $this->get_add_to_cart_args( $request );
		[ $product_id, $qty, $variation_id, $variation ] = array_values( $cart_args );

		if ( $cart->add_to_cart( $product_id, $qty, $variation_id, $variation ) ) {
			$cart->calculate_totals();

			$gateways = $this->get_supported_gateways();

			$response = rest_ensure_response(
				apply_filters(
					'sswps_cart_calculation_response',
					[
						'data' => array_reduce( $gateways, function ( $carry, $item ) use ( $cart ) {
							/**
							 *
							 * @var Gateways\Abstract_Gateway $item
							 */
							$carry[ $item->id ] = $item->add_to_cart_response(
								[
									'total'           => wc_format_decimal( $cart->get_total( 'raw' ), 2 ),
									'subtotal'        => wc_format_decimal( $cart->get_subtotal(), 2 ),
									'totalCents'      => Utils\Currency::add_number_precision( $cart->get_total( 'raw' ) ),
									'displayItems'    => $item->get_display_items( 'cart' ),
									'shippingOptions' => $item->get_formatted_shipping_methods(),
								]
							);

							return $carry;
						}, [] ),
					],
					$gateways,
					$request
				)
			);
		} else {
			$response = new WP_Error( 'cart-error', $this->get_error_messages(), [ 'status' => 200 ] );
		}

		wc_clear_notices();

		return $response;
	}

	protected function get_error_messages() {
		return $this->get_messages( 'error' );
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_REST::get_messages()
	 */
	protected function get_messages( $types = 'all' ) {
		$notices = wc_get_notices();
		$message = '';
		if ( $types !== 'all' ) {
			$types = (array) $types;
			foreach ( $notices as $type => $notice ) {
				if ( ! in_array( $type, $types ) ) {
					unset( $notices[ $type ] );
				}
			}
		}
		foreach ( $notices as $notice_types ) {
			if ( is_array( $notice_types ) ) {
				foreach ( $notice_types as $notice ) {
					$message .= sprintf( ' %s', $notice );
				}
			} else {
				$message .= sprintf( ' %s', $notice_types );
			}
		}

		return trim( $message );
	}

	/**
	 * Return true if the provided packages have shipping methods.
	 *
	 * @param array $packages
	 */
	private function has_shipping_methods( $packages ) {
		foreach ( $packages as $i => $package ) {
			if ( ! empty( $package['rates'] ) ) {
				return true;
			}
		}

		return false;
	}

}
