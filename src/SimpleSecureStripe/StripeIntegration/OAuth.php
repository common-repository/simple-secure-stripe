<?php

namespace SimpleSecureWP\SimpleSecureStripe\StripeIntegration;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Stripe as SSStripe;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Stripe as StripeAPI;
use SimpleSecureWP\SimpleSecureStripe\Stripe\OAuth as StripeOAuth;
use SimpleSecureWP\SimpleSecureStripe\Stripe\StripeObject;
use SimpleSecureWP\SimpleSecureStripe\Utils\URL;

/**
 * Class OAuth. Handles connection to Stripe when the platform keys are needed.
 *
 * @since   5.3.0
 *
 * @package Stripe\StripeIntegration
 */
class OAuth extends Contracts\Abstract_OAuth {

	/**
	 * The API Path.
	 *
	 * @since 5.3.0
	 *
	 * @var string
	 */
	public $api_endpoint = 'stripe';

	/**
	 * De-authorize the current seller account in Stripe oAuth.
	 *
	 * @since 1.0.0
	 *
	 * @return StripeObject|null
	 */
	public function disconnect_account() {
		$mode     = sswps_mode();
		$settings = get_option( App::get( Settings\API::class )->get_option_key(), [] );
		$key      = $settings["publishable_key_{$mode}"] ?? '';
		if ( empty( $key ) ) {
			return null;
		}

		StripeAPI::setApiKey( $key );
		return StripeOAuth::deauthorize( [
			'client_id'      => App::get( SSStripe::class )->get_client_id(),
			'stripe_user_id' => App::get( Settings\API::class )->get_field_key( 'account_id' ),
		] );
	}

	/**
	 * Get the URL to connect to Stripe.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_connect_url() : string {
		return StripeOAuth::authorizeUrl( [
			'response_type'  => 'code',
			'client_id'      => App::get( SSStripe::class )->get_client_id(),
			'stripe_landing' => 'login',
			'always_prompt'  => 'true',
			'scope'          => 'read_write',
			'state'          => base64_encode(
				wp_json_encode(
					[
						'redirect'       => add_query_arg( '_stripe_connect_nonce', wp_create_nonce( 'stripe-connect' ), URL::wc_settings( 'sswps_api' ) ),
						'mode'           => Constants::LIVE,
						'client_id_mode' => defined( 'SIMPLESECUREWP_STRIPE_CLIENT_ID' ) ? 'test' : 'live',
					]
				)
			),
		] );
	}
}