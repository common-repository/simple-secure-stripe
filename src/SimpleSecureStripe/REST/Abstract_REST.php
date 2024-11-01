<?php

namespace SimpleSecureWP\SimpleSecureStripe\REST;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use WP_Error;
use WP_REST_Request;

/**
 *
 * @author Simple & Secure WP
 * @package Stripe/Abstract
 *
 */
abstract class Abstract_REST {

	protected $namespace = '';

	/**
	 *
	 * @var WP_Error
	 */
	protected $error = null;

	/**
	 *
	 * @param string $route
	 */
	protected function register_authenticated_route( $route ) {
		$routes                  = get_option( 'sswps_authenticated_routes', [] );
		$route                   = '/' . trim( $route, '/' );
		$routes[ md5( $route ) ] = $route;
		update_option( 'sswps_authenticated_routes', $routes );
	}

	/**
	 * Register all routes that the controller uses.
	 */
	abstract public function register_routes();

	public function rest_uri( $uri = '' ) {
		$rest_uri = App::get( API::class )->rest_uri() . ( ! empty( $this->namespace ) ? $this->namespace : '' );
		if ( $uri ) {
			$rest_uri = trailingslashit( $rest_uri ) . $uri;
		}
		return trim( $rest_uri, '/' );
	}

	public function rest_url( $uri = '' ) {
		$rest_url = App::get( API::class )->rest_url() . ( ! empty( $this->namespace ) ? trailingslashit( $this->namespace ) : '' );
		if ( $uri ) {
			$rest_url = trailingslashit( $rest_url ) . $uri;
		}
		return $rest_url;
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 */
	public function admin_permission_check( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return new WP_Error( 'permission-error', __( 'You do not have permissions to access this resource.', 'simple-secure-stripe' ), [ 'status' => 403 ] );
		}

		return true;
	}

	protected function get_error_messages() {
		return $this->get_messages( 'error' );
	}

	protected function get_messages( $types = 'all' ) {
		$notices = wc_get_notices();
		if ( $types !== 'all' ) {
			$types = (array) $types;
			foreach ( $notices as $type => $notice ) {
				if ( ! in_array( $type, $types ) ) {
					unset( $notices[ $type ] );
				}
			}
		}
		wc_set_notices( $notices );
		ob_start();
		$messages = wc_print_notices();
		return ob_get_clean();
	}

	/**
	 * Allows a status code of 200 to be returned even if there is a validation error.
	 *
	 * @param WP_Error $error
	 */
	protected function add_validation_error( $error ) {
		$data = $error->get_error_data();
		if ( ! is_array( $data ) ) {
			$data = [];
		}
		$error->add_data( array_merge( $data, [ 'status' => 200 ] ) );
		$this->error = $error;
		add_filter(
			'rest_request_before_callbacks',
			function( $response ) {
				return $this->error ? $this->error : $response;
			}
		);
		return $error;
	}
}
