<?php

namespace SimpleSecureWP\SimpleSecureStripe\Admin\Settings;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Util\ApiVersion;
use SimpleSecureWP\SimpleSecureStripe\StripeIntegration;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\REST;
use SimpleSecureWP\SimpleSecureStripe\Stripe;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Exception\ApiErrorException;
use SimpleSecureWP\SimpleSecureStripe\Stripe\WebhookEndpoint;
use SimpleSecureWP\SimpleSecureStripe\Utils\URL;

/**
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Classes
 *
 */
class API extends Abstract_Settings {

	public function __construct() {
		$this->id        = 'sswps_api';
		$this->tab_title = __( 'API Settings', 'simple-secure-stripe' );
		parent::__construct();
	}

	public function hooks() {
		parent::hooks();
		add_action( 'woocommerce_update_options_checkout_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	public function generate_hidden_html( $key, $data ) : string {
		$html = $this->generate_text_html( $key, $data );
		$html = str_replace( '<tr', '<tr style="display: none;"', $html );
		return $html;
	}

	/**
	 * Generates the HTML for the mode select box.
	 *
	 * This method is named so that it conforms to WooCommerce's naming convention for generating form fields.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Field key.
	 * @param mixed $data Field data.
	 *
	 * @return string
	 */
	public function generate_mode_select_html( $key, $data ) : string {
		$html = (string) $this->generate_select_html( $key, $data );

		$settings = get_option( $this->get_option_key(), null );
		$active_mode = $settings['mode'] ?? 'test';
		$mode_message_live = __( 'Site is in live mode.', 'simple-secure-stripe' );
		$mode_message_test = __( 'Site is in test mode.', 'simple-secure-stripe' );
		$mode_message      = $active_mode === 'live' ? $mode_message_live : $mode_message_test;
		$mode_message      = '<b class="sswps-mode__message--' . esc_attr( $active_mode ) . '">' . $mode_message . '</b>';

		$html = str_replace( '{{mode_message}}', $mode_message, $html );
		$html = str_replace( '<fieldset>', '<fieldset class="sswps-mode__' . esc_attr( $active_mode ) . '">', $html );

		return $html;
	}

	/**
	 * Generates the HTML for the Stripe Connect button.
	 *
	 * This method is named so that it conforms to WooCommerce's naming convention for generating form fields.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Field key.
	 * @param mixed $data Field data.
	 *
	 * @return string
	 */
	public function generate_stripe_connect_html( $key, $data ) : string {
		$field_key           = $this->get_field_key( $key );
		$data                = wp_parse_args(
			$data,
			[
				'class'       => '',
				'style'       => '',
				'description' => '',
				'desc_tip'    => false,
				'css'         => '',
				'active'      => false,
			]
		);

		if ( $data['active'] ) {
			$data['connect_url'] = add_query_arg(
				[
					'_stripe_connect_nonce' => wp_create_nonce( 'stripe-connect' ),
					'_sswps_disconnect'    => 1,
				],
				URL::wc_settings( 'sswps_api' )
			);
			$data['label'] = __( 'Disconnect from Stripe', 'simple-secure-stripe' );
			$data['class'] .= ' stripe-delete-connection';
		} else {
			$data['connect_url'] = App::get( StripeIntegration\OAuth::class )->get_connect_url();
		}
		ob_start();
		include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/stripe-connect.php';

		return (string) ob_get_clean();
	}

	public function init_form_fields() {
		$this->form_fields = [
			'title'                => [
				'type'  => 'title',
				'title' => __( 'API Settings', 'simple-secure-stripe' ),
				'description' => __(
					'When test mode is enabled you can manually enter your API keys or go through the connect process. Live mode requires that you click the Connect button.',
					'simple-secure-stripe'
				),
			],
			'stripe_connect'       => [
				'type'        => 'stripe_connect',
				'title'       => __( 'Stripe connection', 'simple-secure-stripe' ),
				'label'       => __( 'Connect to Stripe', 'simple-secure-stripe' ),
				'class'       => 'do-stripe-connect',
				'description' => __( 'We make it easy to connect Stripe to your site. Click the Connect button to go through our connect flow.', 'simple-secure-stripe' ),
			],
			'mode'                 => [
				'type'        => 'mode_select',
				'title'       => __( 'Mode', 'simple-secure-stripe' ),
				'class'       => 'wc-enhanced-select',
				'options'     => [
					'test' => __( 'Test', 'simple-secure-stripe' ),
					'live' => __( 'Live', 'simple-secure-stripe' ),
				],
				'default'     => 'test',
				'desc_tip'    => false,
				'description' => '{{mode_message}} ' . __(
					'The mode determines if you are processing test transactions or live transactions on your site. Test mode allows you to simulate payments so you can test your integration.',
					'simple-secure-stripe'
				),
				'custom_attributes' => [
					'data-hide-if-no-value' => [
						'account_id' => true,
					],
				],
			],
			'account_id'           => [
				'type'              => 'hidden',
				'title'             => __( 'Account ID', 'simple-secure-stripe' ),
				'text'              => '',
				'class'             => '',
				'default'           => '',
				'desc_tip'          => false,
			],
			'publishable_key_test' => [
				'title'             => __( 'Test publishable key', 'simple-secure-stripe' ),
				'type'              => 'text',
				'default'           => '',
				'desc_tip'          => false,
				'description'       => __( 'Your test publishable key is used for your checkout form. <b>This is required for test mode.</b>', 'simple-secure-stripe' ),
				'custom_attributes' => [
					'data-show-if' => [
						'mode' => 'test',
					],
					'data-hide-if-no-value' => [
						'account_id' => true,
					],
				],
			],
			'secret_key_test'      => [
				'title'             => __( 'Test secret key', 'simple-secure-stripe' ),
				'type'              => 'password',
				'default'           => '',
				'desc_tip'          => false,
				'description'       => __( 'Your secret key is used to authenticate requests to Stripe. <b>This is required for test mode.</b>', 'simple-secure-stripe' ),
				'custom_attributes' => [
					'data-show-if' => [
						'mode' => 'test',
					],
					'data-hide-if-no-value' => [
						'account_id' => true,
					],
				],
			],
			/*
			'webhook_button_test'  => [
				'type'              => 'stripe_button',
				'title'             => __( 'Create Webhook', 'simple-secure-stripe' ),
				'label'             => __( 'Create Webhook', 'simple-secure-stripe' ),
				'class'             => 'sswps-create-webhook test-mode button-secondary',
				'custom_attributes' => [
					'data-show-if' => [
						'mode' => 'test',
					],
				],
			],
			'webhook_button_live'  => [
				'type'              => 'stripe_button',
				'title'             => __( 'Create Webhook', 'simple-secure-stripe' ),
				'label'             => __( 'Create Webhook', 'simple-secure-stripe' ),
				'class'             => 'sswps-create-webhook live-mode button-secondary',
				'custom_attributes' => [
					'data-show-if' => [
						'mode' => 'live',
					],
				],
			],
			*/
			'webhook_created' => [
				'type' => 'checkbox',
				'title' => __( 'Webhook created', 'simple-secure-stripe' ),
				'text'  => __( 'I have created the webhook in my Stripe account.', 'simple-secure-stripe' ),
				'description' => sprintf(
					/* translators: 1: open anchor tag, 2: close anchor tag. */
					__(
						'Webhooks are a one-way message from Stripe to your site to alert it about payment activity. You must create one to enable the full integration with Stripe. Luckily, Stripe makes it pretty easy!<br/><br/>Check out our %1$swebhook setup guide%2$s for more information.',
						'simple-secure-stripe'
					),
					'<a href="https://sswp.io/j18sw" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'custom_attributes' => [
					'data-hide-if-no-value' => [
						'account_id' => true,
					],
				],
			],
			'webhook_url'          => [
				'type'        => 'paragraph',
				'title'       => __( 'Webhook URL', 'simple-secure-stripe' ),
				'class'       => 'sswps-webhook',
				'text'        => App::get( REST\Webhook::class )->rest_url( 'webhook' ),
				'desc_tip'    => true,
				'description' => __(
					'<strong>Important:</strong> the webhook url is called by Stripe when events occur in your account, like a source becomes chargeable. Use the Create Webhook button or add the webhook manually in your Stripe account.',
					'simple-secure-stripe'
				),
				'custom_attributes' => [
					'data-hide-if-no-value' => [
						'account_id' => true,
					],
				],
			],
			'webhook_secret'  => [
				'type'              => 'password',
				'title'             => __( 'Webhook secret', 'simple-secure-stripe' ),
				'description'       => sprintf(
					/* translators: 1: open anchor tag, 2: close anchor tag. */
					__(
						'The webhook secret is used to authenticate webhooks sent from Stripe. It ensures no 3rd party can send you events, pretending to be Stripe. %1$sWebhook guide%2$s',
						'simple-secure-stripe'
					),
					'<a target="_blank" href="https://simplesecurewp.com/knowledgebase/stripe/webhooks/" rel="noopener noreferrer">',
					'</a>'
				),
				'custom_attributes' => [
					'data-hide-if-no-value' => [
						'account_id' => true,
					],
				],
			],
			'debug_log'            => [
				'title'       => __( 'Debug log', 'simple-secure-stripe' ),
				'type'        => 'checkbox',
				'desc_tip'    => true,
				'default'     => 'yes',
				'description' => __( 'When enabled, the plugin logs important errors and info that can help you troubleshoot potential issues.', 'simple-secure-stripe' ),
			],
		];
		if ( $this->get_option( 'account_id' ) ) {
			$this->form_fields['account_id']['text']            = $this->get_option( 'account_id' );
			$this->form_fields['stripe_connect']['description'] = sprintf(
				'<span class="dashicons dashicons-yes stripe-connect-active"></span> ' .
				/* translators: your account ID */
				__(
					'Your Stripe account has been connected. Your account ID is %1$s',
					'simple-secure-stripe'
				),
				'<code>' . $this->get_option( 'account_id' ) . '</code>'
			);
			$this->form_fields['stripe_connect']['active']      = true;
		} else {
			unset( $this->form_fields['account_id'] );
		}

		$is_webhook_created = $this->get_option( 'webhook_created' );
		if ( empty( $is_webhook_created ) || $is_webhook_created === 'no' ) {
			$this->form_fields['webhook_created']['class'] = 'sswps-webhook-info--alert';
			$this->form_fields['webhook_created']['description'] = '<span class="sswps-webhook-info--alert">' . $this->form_fields['webhook_created']['description'] . '</span>';
		}

		$webhook_secret = $this->get_option( 'webhook_secret' );
		if ( empty( $webhook_secret ) ) {
			$this->form_fields['webhook_secret']['class'] = 'sswps-field--required';
		}

		$test_publishable_key = $this->get_option( 'publishable_key_test' );
		if ( empty( $test_publishable_key ) ) {
			$this->form_fields['publishable_key_test']['class'] = 'sswps-field--required';
		}

		$test_secret_key = $this->get_option( 'secret_key_test' );
		if ( empty( $test_secret_key ) ) {
			$this->form_fields['secret_key_test']['class'] = 'sswps-field--required';
		}
	}

	public function admin_options() {
		$nonce_value = sanitize_text_field( wp_unslash( sswps_get_request_var( '_stripe_connect_nonce' ) ) );

		// Check if user is being returned from Stripe Connect
		if ( ! wp_verify_nonce( $nonce_value, 'stripe-connect' ) ) {
			parent::admin_options();

			return;
		}

		$error = Request::get_sanitized_var( 'error' );
		$response = Request::get_sanitized_var( 'response' );
		$disconnect = Request::get_sanitized_var( '_sswps_disconnect' );

		// At this point the nonce has been validated.
		if ( isset( $error ) ) {
			$error = json_decode( base64_decode( wc_clean( $error ) ) );
			if ( property_exists( $error, 'message' ) ) {
				$message = $error->message;
			} elseif ( property_exists( $error, 'raw' ) ) {
				$message = $error->raw->message;
			} else {
				$message = __( 'Please try again.', 'simple-secure-stripe' );
			}
			sswps_log_error( sprintf( 'Error connecting to Stripe account. Reason: %s', $message ) );
			/* translators: error message returned by Stripe */
			$this->add_error( sprintf( __( 'We were not able to connect your Stripe account. Reason: %s', 'simple-secure-stripe' ), $message ) );
		} elseif ( ! empty( $response ) ) {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				?>
				<div class="error inline notice-error is-dismissible">
					<p><?php esc_html_e( 'Not authorized to perform this action. Required permission: manage_woocommerce', 'simple-secure-stripe' ) ?></p>
				</div>
				<?php
			} else {
				$response = json_decode( base64_decode( $response ) );

				if ( isset( $response->live ) ) {
					$this->settings['account_id']           = $response->live->stripe_user_id;
					$this->settings['refresh_token']        = $response->live->refresh_token;
					$this->settings['secret_key_live']      = $response->live->access_token;
					$this->settings['publishable_key_live'] = $response->live->publishable_key;
				} else {
					$this->settings['account_id']           = $response->test->stripe_user_id;
					$this->settings['refresh_token']        = $response->test->refresh_token;
					$this->settings['secret_key_live']      = $response->test->access_token;
					$this->settings['publishable_key_live'] = $response->test->publishable_key;
				}

				update_option( $this->get_option_key(), $this->settings );

				delete_option( 'sswps_connect_notice' );

				// create webhooks
				// Webhook creation is disabled until we can get permissions to enable it.
				// $this->create_webhook( sswps_mode() );

				/**
				 * @since 1.0.0
				 *
				 * @param array $response
				 * @param API   $object
				 */
				do_action( 'sswps/connect_settings', $response, $this );

				$this->init_form_fields();
				?>
				<div class="updated inline notice-success is-dismissible ">
					<p>
						<?php esc_html_e( 'Your Stripe account has been connected to your WooCommerce store. You may now accept payments in Live and Test mode.', 'simple-secure-stripe' ) ?>
					</p>
				</div>
				<?php
			}
		} elseif ( ! empty( $disconnect ) ) {
			App::get( StripeIntegration\OAuth::class )->disconnect_account();

			// save the token to the api settings
			$this->settings['account_id']    = null;
			$this->settings['refresh_token'] = null;

			$this->settings['secret_key_live']      = null;
			$this->settings['publishable_key_live'] = null;

			$this->settings['secret_key_test']      = null;
			$this->settings['publishable_key_test'] = null;

			update_option( $this->get_option_key(), $this->settings );
		}

		parent::admin_options();
	}

	public function localize_settings() {
		return parent::localize_settings(); // TODO: Change the autogenerated stub
	}

	public function delete_webhook_settings( $mode ) {
		unset( $this->settings["webhook_secret"], $this->settings["webhook_id}"] );
		update_option( $this->get_option_key(), $this->settings );
	}

	protected function get_webhook_events() {
		return [
			'charge.dispute.closed',
			'charge.dispute.created',
			'charge.failed',
			'charge.pending',
			'charge.refunded',
			'charge.succeeded',
			'payment_intent.requires_action',
			'payment_intent.succeeded',
			'review.closed',
			'review.opened',
			'source.chargeable',
		];
	}

	/**
	 * @since 1.0.0
	 *
	 * @param array  $events Webhook events.
	 *
	 * @param string $mode Stripe mode: live or test.
	 *
	 * @return WebhookEndpoint|\WP_Error
	 * @throws ApiErrorException
	 */
	public function create_webhook( $mode, $events = [] ) {
		$default_events = $this->get_webhook_events();

		if ( ! in_array( '*', $events ) ) {

			$events = array_merge( $default_events, $events );
			$events = array_unique( $events );
			$events = array_values( $events );

			/**
			 * @since 1.0.0
			 *
			 * @param array  $events Webhook events.
			 * @param string $mode Stripe mode: live or test.
			 */
			$events = (array) apply_filters( 'sswps/webhook_events', $events, $mode );
		}

		if ( empty( $events ) ) {
			$events = $default_events;
		}

		$webhook = StripeIntegration\Client::service( 'webhookEndpoints', $mode )->create( [
			'url'            => App::get( REST\API::class )->rest_url( 'webhook' ),
			'enabled_events' => $events,
			'connect'        => false,
			'api_version'    => ApiVersion::CURRENT,
			'description'    => __( 'Simple & Secure Stripe for WooCommerce', 'simple-secure-stripe' ),
		] );

		if ( is_wp_error( $webhook ) ) {
			return $webhook;
		}

		$this->settings["webhook_secret"] = $webhook['secret'];
		$this->settings["webhook_id"]     = $webhook['id'];
		update_option( $this->get_option_key(), $this->settings );

		return $webhook;
	}

	public function get_webhook_id( $mode ) {
		return $this->get_option( "webhook_id_{$mode}", null );
	}

	public function get_account_id( $mode = '' ) {
		if ( ! $mode ) {
			$mode = sswps_mode();
		}

		return $this->get_option( 'account_id' );
	}

	public function delete_account_settings() {
		delete_option( $this->get_option_key() );
	}

}
