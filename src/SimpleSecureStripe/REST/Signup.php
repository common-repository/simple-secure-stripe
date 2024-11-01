<?php

namespace SimpleSecureWP\SimpleSecureStripe\REST;

use WP_Error;
use WP_REST_Server;

class Signup extends Abstract_REST {

	protected $namespace = 'admin/signup';

	private $api_url = 'https://crm.paymentplugins.com/v1/contacts';

	public function register_routes() {
		register_rest_route(
			$this->rest_uri(), 'contact', [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'register_contact' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
				'args'                => [
					'firstname' => [
						'required',
						'validate_callback' => function( $value ) {
							return strlen( $value ) > 0;
						},
					],
					'email'     => [
						'required',
						'validate_callback' => function( $value ) {
							return strlen( $value ) > 0 && is_email( $value );
						},
					],
				],
			]
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 */
	public function register_contact( $request ) {
		$data   = [
			'email'      => $request['email'],
			'attributes' => [
				'firstname'      => $request['firstname'],
				'website'        => get_site_url(),
				'plugin'         => 'stripe',
				'account_active' => false,
			],
		];
		$result = wp_safe_remote_post( $this->api_url, [
			'method'      => 'POST',
			'timeout'     => 30,
			'httpversion' => 1,
			'blocking'    => true,
			'headers'     => [
				'Content-Type' => 'application/json',
			],
			'body'        => wp_json_encode( $data ),
			'cookies'     => [],
		] );
		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'contact-error', $result->get_error_message(), [ 'status' => 200 ] );
		}
		if ( wp_remote_retrieve_response_code( $result ) !== 200 ) {
			$body = json_decode( wp_remote_retrieve_body( $result ), true );

			return new WP_Error( 'contact-error', $body['message'], [ 'status' => 200 ] );
		}
		update_option( 'sswps_admin_signup', true, false );

		return rest_ensure_response( [ 'success' => true, 'message' => __( 'It\'s on its way! Please check your emails.', 'simple-secure-stripe' ) ] );
	}

}