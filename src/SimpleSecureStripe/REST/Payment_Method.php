<?php

namespace SimpleSecureWP\SimpleSecureStripe\REST;

use SimpleSecureWP\SimpleSecureStripe\Gateway;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 *
 * @since   1.0.0
 *
 * @author Simple & Secure WP
 * @package Stripe/Controllers
 */
class Payment_Method extends Abstract_REST {

	protected $namespace = 'payment-method';

	public function register_routes() {
		register_rest_route(
			$this->rest_uri(),
			'token',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'payment_method_from_token' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Creates a PaymentMethod from a Token.
	 * Use case for this controller would be if a token
	 * is provided on the client side, but PaymentIntent is desired instead of a Charge. The token must be converted to
	 * a PaymentMethod for use in a PaymentIntent.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response|mixed
	 */
	public function payment_method_from_token( $request ) {
		$result = Gateway::load()->paymentMethods->create(
			[
				'type' => 'card',
				'card' => [ 'token' => $request->get_param( 'token' ) ],
			]
		);

		return rest_ensure_response( [ 'payment_method' => $result->jsonSerialize() ] );
	}
}
