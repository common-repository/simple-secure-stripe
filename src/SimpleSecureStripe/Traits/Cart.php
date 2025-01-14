<?php

namespace SimpleSecureWP\SimpleSecureStripe\Traits;

use Exception;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use WP_REST_Request;

/**
 *
 * @since  1.0.0
 * @author Simple & Secure WP
 */
trait Cart {

	/**
	 * Return an array of arguments used to add a product to the cart.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request
	 */
	protected function get_add_to_cart_args( $request ) {
		$session_args = WC()->session ? WC()->session->get( Constants::CART_ARGS, [ 'product_id' => 0 ] ) : [ 'product_id' => 0 ];
		$args         = array(
			'product_id'   => $request->get_param( 'product_id' ),
			'qty'          => $request->get_param( 'qty' ),
			'variation_id' => $request->get_param( 'variation_id' )
		);
		$variation    = array();
		if ( $request->get_param( 'variation_id' ) ) {
			foreach ( $request->get_params() as $key => $value ) {
				if ( 'attribute_' === substr( $key, 0, 10 ) ) {
					$variation[ sanitize_title( wp_unslash( $key ) ) ] = wp_unslash( $value );
				}
			}
		}
		$args['variation'] = $variation;

		if ( isset( $session_args['product_id'] ) && $args['product_id'] === $session_args['product_id'] ) {
			array_walk( $args, function ( &$item, $key ) use ( $session_args ) {
				if ( ! $item && ! empty( $session_args[ $key ] ) ) {
					$item = $session_args[ $key ];
				}
			} );
		}
		WC()->session->set( Constants::CART_ARGS, $args );

		return $args;
	}

		/**
	 * Method that hooks in to the woocommerce_cart_ready_to_calc_shipping filter.
	 * Purpose is to ensure
	 * true is returned so shipping packages are calculated. Some 3rd party plugins and themes return false
	 * if the current page is the cart because they don't want to display the shipping calculator.
	 *
	 * @since 1.0.0
	 */
	public function add_ready_to_calc_shipping() {
		add_filter( 'woocommerce_cart_ready_to_calc_shipping', '__return_true', 1000 );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	private function get_shipping_method_from_request( $request ) {
		if ( ( $method = $request->get_param( 'shipping_method' ) ) ) {
			if ( ! preg_match( '/^(?P<index>[\w]+)\:(?P<id>.+)$/', $method, $shipping_method ) ) {
				throw new Exception( __( 'Invalid shipping method format. Expected: index:id', 'simple-secure-stripe' ) );
			}

			return [ $shipping_method['index'] => $shipping_method['id'] ];
		}

		return [];
	}

	/**
	 * @param array           $address
	 * @param WP_REST_Request $request
	 */
	public function validate_shipping_address( $address, $request ) {
		if ( isset( $address['state'], $address['country'] ) ) {
			$address['state']   = sswps_filter_address_state( $address['state'], $address['country'] );
			$request->set_param( 'address', $address );
		}

		return true;
	}
}
