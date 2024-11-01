<?php

namespace SimpleSecureWP\SimpleSecureStripe\REST;

use Exception;
use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\Arrays\Arr;
use SimpleSecureWP\SimpleSecureStripe\Stripe\WebhookEndpoint;
use SimpleSecureWP\SimpleSecureStripe\StripeIntegration\Client;
use WP_Error;
use WP_Filesystem_Base;
use WP_REST_Request;
use WP_REST_Server;

/**
 *
 * @package Stripe/Controllers
 * @author Simple & Secure WP
 *
 */
class Gateway_Settings extends Abstract_REST {

	protected $namespace = 'gateway-settings';

	public function register_routes() {
		register_rest_route(
			$this->rest_uri(), 'apple-domain', [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'register_apple_domain' ],
				'permission_callback' => [ $this, 'shop_manager_permission_check' ],
			]
		);
		register_rest_route(
			$this->rest_uri(), 'create-webhook',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_webhook' ],
				'permission_callback' => [ $this, 'shop_manager_permission_check' ],
			]
		);
		register_rest_route( $this->rest_uri(), 'delete-webhook', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'delete_webhook' ],
			'permission_callback' => [ $this, 'shop_manager_permission_check' ],
		] );
		register_rest_route(
			$this->rest_uri(), 'connection-test',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'connection_test' ],
				'permission_callback' => [ $this, 'shop_manager_permission_check' ],
			]
		);
		register_rest_route(
			$this->rest_uri(), 'delete-connection',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'delete_connection' ],
				'permission_callback' => [ $this, 'shop_manager_permission_check' ],
			]
		);
	}

	/**
	 * Register the site domain with Stripe for Apple Pay.
	 *
	 * @param WP_REST_Request $request
	 */
	public function register_apple_domain( $request ) {
		$gateway = Gateway::load();
		$document_root = Request::get_sanitized_server_var( 'DOCUMENT_ROOT' );

		// try to add domain association file.
		if ( ! empty( $document_root ) ) {
			$path = $document_root . DIRECTORY_SEPARATOR . '.well-known';
			$file = $path . DIRECTORY_SEPARATOR . 'apple-developer-merchantid-domain-association';
			if ( ! file_exists( $file ) ) {
				require_once( ABSPATH . '/wp-admin/includes/file.php' );
				if ( function_exists( 'WP_Filesystem' ) && ( WP_Filesystem() ) ) {
					/**
					 *
					 * @var WP_Filesystem_Base $wp_filesystem
					 */
					global $wp_filesystem;
					if ( ! $wp_filesystem->is_dir( $path ) ) {
						$wp_filesystem->mkdir( $path );
					}
					$contents = $wp_filesystem->get_contents( SIMPLESECUREWP_STRIPE_FILE_PATH . 'apple-developer-merchantid-domain-association' );
					$wp_filesystem->put_contents( $file, $contents, 0755 );
				}
			}
		}

		$server_name = Arr::get( $request, 'hostname', Request::get_sanitized_server_var( 'SERVER_NAME', '' ) );

		/**
		 * @since 1.0.0
		 */
		$server_name = apply_filters( 'sswps/apple_pay_domain', $server_name );
		if ( strstr( $server_name, 'www.' ) ) {
			$server_name_2 = str_replace( 'www.', '', $server_name );
		} else {
			$server_name_2 = 'www.' . $server_name;
		}
		$domains = [ $server_name, $server_name_2 ];
		try {
			$api_key = sswps_get_secret_key( 'live' );
			if ( empty( $api_key ) ) {
				throw new Exception(
					__(
						'You cannot register your domain until you have completed the Connect process on the API Settings page. A registered domain is not required when test mode is enabled.',
						'simple-secure-stripe'
					)
				);
			}
			// fetch the Apple domains
			$registered_domains = $gateway->applePayDomains->all( [ 'limit' => 50 ] );
			if ( $registered_domains ) {
				// loop through domains and delete if they match domain of site.
				foreach ( $registered_domains->data as $domain ) {
					if ( in_array( $domain->domain_name, $domains ) ) {
						$gateway->applePayDomains->delete( $domain->id );
					}
				}
			}
			$failures = 0;
			foreach ( $domains as $domain ) {
				$result = $gateway->applePayDomains->create( [ 'domain_name' => $domain ] );
			}
		} catch ( Exception $e ) {
			return new WP_Error( 'domain-error', $e->getMessage(), [ 'status' => 200 ] );
		}

		return rest_ensure_response(
			[
				'message' => sprintf(
					__(
						'Domain registered successfully. You can confirm in your Stripe Dashboard at https://dashboard.stripe.com/account/apple_pay.',
						'simple-secure-stripe'
					)
				),
			]
		);
	}

	/**
	 * Create a Stripe webhook for the site.
	 *
	 * @param WP_REST_Request $request
	 */
	public function create_webhook( $request ) {
		$url          = App::get( API::class )->webhook->rest_url( 'webhook' );
		$api_settings = App::get( Settings\API::class );
		$env          = $request->get_param( 'environment' );
		$events       = [];
		// first fetch all webhooks
		$secret_key = sswps_get_secret_key( $env );
		if ( empty( $secret_key ) ) {
			return new WP_Error(
				'webhook-error', __( 'You must configure your secret key before creating webhooks.', 'simple-secure-stripe' ),
				[
					'status' => 200,
				]
			);
		}
		$webhooks = Client::service( 'webhookEndpoints', $env )->all( [ 'limit' => 100 ] );
		// validate that the webhook hasn't already been created.
		foreach ( $webhooks->data as $webhook ) {
			/**
			 * @var WebhookEndpoint $webhook
			 */
			if ( $webhook->url === $url ) {
				if ( ! $api_settings->get_option( "webhook_secret", null ) ) {
					// get all of the events for this endpoint so they can be merged with the
					// new webhook that's created.
					$events = $webhook->enabled_events;
					Client::service( 'webhookEndpoints', $env )->delete( $webhook->id );
					$api_settings->delete_webhook_settings( $env );
				} else {
					return new WP_Error(
						'webhook-error',
						__( 'There is already a webhook configured for this site. If you want to delete the webhook, login to your Stripe Dashboard.', 'simple-secure-stripe' ),
						[
							'status' => 200,
						]
					);
				}
			}
		}

		$webhook = $api_settings->create_webhook( $env, $events );
		if ( is_wp_error( $webhook ) ) {
			return new WP_Error( $webhook->get_error_code(), $webhook->get_error_message(), [ 'status' => 200 ] );
		} else {
			return rest_ensure_response(
				[
					'message' => sprintf(
						/* translators: %s: Stripe environment - Live or Test */
						__(
							'Webhook created in Stripe for %s environment. You can test your webhook by logging in to the Stripe dashboard',
							'simple-secure-stripe'
						),
						'live' == $env ? _x( 'Live', 'Stripe environment', 'simple-secure-stripe' ) : _x( 'Test', 'Stripe environment', 'simple-secure-stripe' )
					),
					'secret'  => $webhook['secret'],
				]
			);
		}
	}

	/**
	 * @param \WP_REST_Request $request
	 */
	public function delete_webhook( $request ) {
		$api_settings = App::get( Settings\API::class );
		$mode         = $request['mode'];
		$webhook_id   = $api_settings->get_webhook_id( $mode );
		if ( $webhook_id ) {
			$client = Gateway::load( $mode );
			$result = $client->webhookEndpoints->delete( $webhook_id );
			$api_settings->delete_webhook_settings( $mode );
		}

		return rest_ensure_response( [ 'success' => true ] );
	}

	/**
	 * Perform a connection test
	 *
	 * @param WP_REST_Request $request
	 */
	public function connection_test( $request ) {
		$mode     = sswps_mode();
		$settings = App::get( Settings\API::class );
		$api_keys = null;

		// capture all output to prevent JSON parse output errors.
		ob_start();
		try {
			if ( $mode === 'test' ) {
				// if test mode and keys not empty, save them so connect test uses most recently entered keys.
				$api_keys = [ $request->get_param( 'secret_key' ), $request->get_param( 'publishable_key' ) ];

				if ( in_array( null, $api_keys ) ) {
					throw new Exception(
						sprintf(
							__(
								'You must enter your API keys or connect the plugin before performing a connection test.',
								'simple-secure-stripe'
							)
						)
					);
				}
				$settings->settings['publishable_key_test'] = $settings->validate_text_field(
					'publishable_key_test',
					$request->get_param( 'publishable_key' )
				);
				$settings->settings['secret_key_test']      = $settings->validate_password_field(
					'secret_key_test',
					$request->get_param( 'secret_key' )
				);
			}

			// test the secret key
			$response = Gateway::load( $mode )->customers->all( [ 'limit' => 1 ] );

			// test the publishable key
			$response = wp_remote_post(
				'https://api.stripe.com/v1/payment_methods',
				[
					'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
					'body'    => [
						'key'      => sswps_get_publishable_key( $mode ),
						'type'     => 'card',
						'card'     => [
							'number'    => '4242424242424242',
							'exp_month' => 12,
							'exp_year'  => 2030,
							'cvc'       => 314,
						],
						'metadata' => [
							'origin' => 'API Settings connection test',
						],
					],
				]
			);
			if ( is_wp_error( $response ) ) {
				/* translators: %s: Stripe connection mode */
				throw new Exception( sprintf( __( 'Mode: %s. Invalid publishable key. Please check your entry.', 'simple-secure-stripe' ), $mode ) );
			}
			if ( $response['response']['code'] == 401 ) {
				/* translators: %s: Stripe connection mode */
				throw new Exception( sprintf( __( 'Mode: %s. Invalid publishable key. Please check your entry.', 'simple-secure-stripe' ), $mode ) );
			}

			// if test mode and keys are good, save them
			if ( $api_keys ) {
				update_option( $settings->get_option_key(), $settings->settings, 'yes' );
				do_action( 'sswps/api_connection_test_success', $mode );
			}
			ob_get_clean();
		} catch ( Exception $e ) {
			return new WP_Error( 'connection-failure', $e->getMessage(), [ 'status' => 200 ] );
		}

		return rest_ensure_response( [
			/* translators: %s: Stripe connection mode */
			'message' => sprintf( __( 'Connection test to Stripe was successful. Mode: %s.', 'simple-secure-stripe' ), $mode ),
		] );
	}

	/**
	 * @param \WP_REST_Request $request
	 */
	public function delete_connection( $request ) {
		App::get( Settings\API::class )->delete_account_settings();
		App::get( Settings\Account::class )->delete_account_settings();

		return rest_ensure_response( [ 'success', true ] );
	}

	public function shop_manager_permission_check() {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		return new WP_Error(
			'permission-error', __( 'You do not have permissions to access this resource.', 'simple-secure-stripe' ),
			[ 'status' => 403 ]
		);
	}

}
