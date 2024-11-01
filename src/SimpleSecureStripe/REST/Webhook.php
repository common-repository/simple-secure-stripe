<?php
namespace SimpleSecureWP\SimpleSecureStripe\REST;

use Exception;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 *
 * @author Simple & Secure WP
 * @package Stripe/Controllers
 *
 */
class Webhook extends Abstract_REST {

	protected $namespace = '';

	private $secret;

	public function register_routes() {
		register_rest_route(
			$this->rest_uri(),
			'webhook',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'webhook' ],
				'permission_callback' => '__return_true'
			]
		);

		register_rest_route(
			$this->rest_uri(),
			'webhook',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'handle_get_request' ],
				'permission_callback' => '__return_true'
			]
		);
	}

	/**
	 *
	 * @param WP_REST_Request $request
	 */
	public function webhook( $request ) {
		$payload         = $request->get_body();
		$json_payload    = json_decode( $payload, true );
		$webhook_id_live = App::get( Settings\API::class )->get_option( 'webhook_id_live' );
		$webhook_id_test = App::get( Settings\API::class )->get_option( 'webhook_id_test' );
		$header          = Request::get_sanitized_server_var( 'HTTP_STRIPE_SIGNATURE', '' );

		try {
			if ( ! $json_payload ) {
				throw new Exception( 'Invalid request payload.' );
			}

			$mode         = $json_payload['livemode'] == true ? 'live' : 'test';
			$webhook_id   = "webhook_id_${mode}";
			$this->secret = App::get( Settings\API::class )->get_option( 'webhook_secret' );

			// if the webhook ID exists and doesn't match the ID from the notification, then don't process. This will
			// happen if the Stripe account has multiple webhooks configured.
			if ( $$webhook_id && isset( $json_payload['data']['object']['metadata']['webhook_id'] ) && $$webhook_id !== $json_payload['data']['object']['metadata']['webhook_id'] ) {
				return rest_ensure_response( [] );
			}
			$event = \SimpleSecureWP\SimpleSecureStripe\Stripe\Webhook::constructEvent( $payload, $header, $this->secret, apply_filters( 'sswps/webhook_signature_tolerance', 600 ) );
			sswps_log_info( sprintf( 'Webhook notification received: Event: %s', $event->type ) );
			$type = $event->type;
			$type = str_replace( '.', '_', $type );

			// allow functionality to hook in to the event action
			do_action( 'sswps/webhook_' . $type, $event->data->offsetGet( 'object' ), $request, $event );

			return rest_ensure_response( apply_filters( 'sswps/webhook_response', [], $event, $request ) );
		} catch ( \SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\SignatureVerificationException $e ) {
			/* translators: %s: error message. */
			sswps_log_error( sprintf( __( 'Invalid signature received. Verify that your webhook secret is correct. Error: %s', 'simple-secure-stripe' ), $e->getMessage() ) );

			return $this->send_error_response( __( 'Invalid signature received. Verify that your webhook secret is correct.', 'simple-secure-stripe' ), 401 );
		} catch ( Exception $e ) {
			/* translators: 1: error message, 2: exception. */
			sswps_log_info( sprintf( __( 'Error processing webhook. Message: %1$s Exception: %2$s', 'simple-secure-stripe' ), $e->getMessage(), get_class( $e ) ) );

			return $this->send_error_response( $e->getMessage() );
		}
	}

	private function send_error_response( $message, $code = 400 ) {
		return new WP_Error( 'webhook-error', $message, array( 'status' => $code ) );
	}

	/**
	 * Handle GET requests to the webhook endpoint. This is to prevent users from testing the webhook using a browser.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function handle_get_request( $request ) {
		return rest_ensure_response( [
			'message' => __( 'Stripe sends webhook notifications via the http POST method. You cannot test the webhook using a browser.', 'simple-secure-stripe' ),
		] );
	}
}
