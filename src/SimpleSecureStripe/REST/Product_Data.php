<?php

namespace SimpleSecureWP\SimpleSecureStripe\REST;

use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Product_Gateway_Option;
use WP_REST_Request;
use WP_REST_Server;

/**
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Controllers
 *
 */
class Product_Data extends Abstract_REST {

	protected $namespace = 'product';

	public function register_routes() {
		register_rest_route(
			$this->rest_uri(),
			'gateway',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'toggle_gateway' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
			]
		);
		register_rest_route(
			$this->rest_uri(),
			'save',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
			]
		);
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 */
	public function toggle_gateway( $request ) {
		$product        = wc_get_product( $request->get_param( 'product_id' ) );
		$payment_method = WC()->payment_gateways()->payment_gateways()[ $request->get_param( 'gateway_id' ) ];

		$option = new Product_Gateway_Option( $product, $payment_method );
		$option->set_option( 'enabled', ! $option->enabled() );
		$option->save();

		return rest_ensure_response( [ 'enabled' => $option->enabled() ] );
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 */
	public function save( $request ) {
		$gateways         = $request->get_param( 'gateways' );
		$charge_types     = $request->get_param( 'charge_types' );
		$payment_gateways = WC()->payment_gateways()->payment_gateways();
		$product          = wc_get_product( $request->get_param( 'product_id' ) );
		$order            = [];
		$loop             = 0;
		foreach ( $gateways as $gateway ) {
			$order[ $gateway ] = $loop;
			$loop++;
		}
		$product->update_meta_data( Constants::PRODUCT_GATEWAY_ORDER, $order );

		foreach ( $charge_types as $type ) {
			$option = new Product_Gateway_Option( $product, $payment_gateways[ $type['gateway'] ] );
			$option->set_option( 'charge_type', $type['value'] );
			$option->save();
		}
		$product->update_meta_data( '_stripe_button_position', $request->get_param( 'position' ) );

		$product->save();

		return rest_ensure_response( [ 'order' => $order ] );
	}
}
