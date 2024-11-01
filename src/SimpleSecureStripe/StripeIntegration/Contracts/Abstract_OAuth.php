<?php

namespace SimpleSecureWP\SimpleSecureStripe\StripeIntegration\Contracts;

use SimpleSecureWP\SimpleSecureStripe\Utils\Arr;

/**
 * Abstract class to handle OAuth server connections
 *
 * @since 1.0.0
 */
abstract class Abstract_OAuth implements OAuth_Interface {

	/**
	 * Public OAuth server URL, used to authenticate accounts with gateway payment providers
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $api_base_url = 'https://oauth.simplesecurewp.com/commerce/v1';

	/**
	 * The API Path.
	 *
	 * @since 5.3.0
	 *
	 * @var string
	 */
	public $api_endpoint;

	/**
	 * Returns the gateway-specific endpoint to use
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_gateway_endpoint() {
		return $this->api_endpoint;
	}

	/**
	 * Returns the OAuth server URL to use.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function get_api_base_url() {

		if ( defined( 'SIMPLESECUREWP_STRIPE_OAUTH_DEV_URL' ) && SIMPLESECUREWP_STRIPE_OAUTH_DEV_URL ) {
			return SIMPLESECUREWP_STRIPE_OAUTH_DEV_URL;
		}

		return $this->api_base_url;
	}

	/**
	 * @inheritDoc
	 */
	public function get_api_url( $endpoint, array $query_args = [] ) {
		return add_query_arg( $query_args, "{$this->get_api_base_url()}/{$this->get_gateway_endpoint()}/{$endpoint}" );
	}

	/**
	 * @inheritDoc
	 */
	public function get( $endpoint, array $query_args ) {
		$url = $this->get_api_url( $endpoint, $query_args );

		$request = wp_remote_get( $url );

		if ( is_wp_error( $request ) ) {
			$this->log_error( 'OAuth request error:', $request->get_error_message(), $url );

			return null;
		}

		$body = wp_remote_retrieve_body( $request );
		$body = json_decode( $body, true );

		return $body;
	}

	/**
	 * @inheritDoc
	 */
	public function post( $endpoint, array $query_args = [], array $request_arguments = [] ) {
		$url = $this->get_api_url( $endpoint, $query_args );

		$default_arguments = [
			'body' => [],
		];

		foreach ( $default_arguments as $key => $default_argument ) {
			$request_arguments[ $key ] = array_merge( $default_argument, Arr::get( $request_arguments, $key, [] ) );
		}
		$request_arguments = array_filter( $request_arguments );
		$request           = wp_remote_post( $url, $request_arguments );

		if ( is_wp_error( $request ) ) {
			$this->log_error( 'OAuth request error:', $request->get_error_message(), $url );

			return null;
		}

		$body = wp_remote_retrieve_body( $request );
		$body = json_decode( $body, true );

		if ( ! is_array( $body ) ) {
			$this->log_error( 'OAuth unexpected response:', $body, $url );
			$this->log_error( 'Response:', print_r( $request, true ), '--->' );

			return null;
		}

		return $body;
	}

	/**
	 * @inheritDoc
	 */
	public function log_error( $type, $message, $url ) {
		$log = sprintf(
			'[%s] %s %s',
			$url,
			$type,
			$message
		);
		sswps_log_error( 'oauth-connection: ' . $log );
	}

}