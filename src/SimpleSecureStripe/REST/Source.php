<?php

namespace SimpleSecureWP\SimpleSecureStripe\REST;

use Exception;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @author Simple & Secure WP
 * @package Stripe/Controllers
 *
 */
class Source extends Abstract_REST {

	protected $namespace = 'source';

	public function register_routes() {
		register_rest_route( $this->rest_uri(), 'update', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'update_source' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'source_id'     => [ 'required' => true ],
				'client_secret' => [ 'required' => true ],
				'updates'       => [ 'required' => true ],
				'gateway_id'    => [ 'required', true ],
			],
		] );
		register_rest_route(
			$this->rest_uri(), 'order/source', [
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_order_source' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * @param WP_REST_Request $request
	 */
	public function update_source( $request ) {

		try {
			/**
			 * @var Gateways\Abstract_Gateway $payment_method
			 */
			$payment_method = WC()->payment_gateways()->payment_gateways()[ $request['payment_method'] ];

			// fetch the source and check client token and status
			$source = $payment_method->payment_object->get_gateway()->sources->retrieve( $request['source_id'] );

			if ( is_wp_error( $source ) ) {
				throw new Exception( __( 'Error updating source.', 'simple-secure-stripe' ) );
			}
			if ( $source->status !== 'chargeable' ) {
				if ( ! hash_equals( $source->client_secret, $request['client_secret'] ) ) {
					throw new Exception( __( 'You do not have permission to update this source.', 'simple-secure-stripe' ) );
				}
				//update the source
				$updates = $request['updates'];
				if ( WC()->cart ) {
					$updates['amount'] = Utils\Currency::add_number_precision( WC()->cart->get_total( 'raw' ), strtoupper( $source->currency ) );
				}
				$source = $payment_method->payment_object->get_gateway()->sources->update( $request['source_id'], $updates );
				if ( is_wp_error( $source ) ) {
					throw new Exception( __( 'Error updating source.', 'simple-secure-stripe' ) );
				}
			}

			return rest_ensure_response( [ 'source' => $source->toArray() ] );
		} catch ( Exception $e ) {
			return new WP_Error( 'source-error', $e->getMessage(), [ 'status' => 400 ] );
		}
	}

	/**
	 * Deletes a source from an order if the order exists.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function delete_order_source( $request ) {
		$order_id = WC()->session->get( 'order_awaiting_payment', null );
		if ( $order_id ) {
			$order = wc_get_order( $order_id );
			$order->delete_meta_data( Constants::SOURCE_ID );
			$order->save();
		}

		return rest_ensure_response( [ 'success' => true ] );
	}
}