<?php
namespace SimpleSecureWP\SimpleSecureStripe\Admin\Settings;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\StripeIntegration\Client;

/**
 * Class Account
 *
 * @since 1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Classes
 */
class Account extends Abstract_Settings {

	public $id = 'sswps_stripe_account';

	private array $previous_settings = [];

	const DEFAULT_ACCOUNT_SETTINGS = array(
		'account_id'       => '',
		'country'          => '',
		'default_currency' => ''
	);

	public function hooks() {
		add_action( 'sswps/connect_settings', array( $this, 'connect_settings' ) );
		add_action( 'woocommerce_update_options_checkout_sswps_api', array( $this, 'pre_api_update' ), 5 );
		add_action( 'woocommerce_update_options_checkout_sswps_api', array( $this, 'post_api_update' ), 20 );
		add_action( 'sswps/api_connection_test_success', array( $this, 'connection_test_success' ) );
	}

	/**
	 * @param object $response
	 */
	public function connect_settings( $response ) {
		foreach ( $response as $mode => $data ) {
			$this->save_account_settings( $data->stripe_user_id, $mode );
		}
	}

	/**
	 * @param string $account_id
	 */
	public function save_account_settings( $account_id, $mode = null ) {
		static $account_cache = [];
		// fetch the account and store the account data.

		if ( $mode === null ) {
			$mode = sswps_mode();
		}

		if ( empty( $account_cache[ $mode ] ) ) {
			$account_cache[ $mode ] = Client::service( 'accounts', $mode )->retrieve( $account_id );
		}

		$account = $account_cache[ $mode ];

		if ( $mode === Constants::LIVE ) {
			$this->settings['account_id']       = $account->id;
			$this->settings['country']          = strtoupper( $account->country );
			$this->settings['default_currency'] = strtoupper( $account->default_currency );
		} else {
			App::get( API::class )->init_form_fields();
			$this->settings[ Constants::TEST ] = array(
				'account_id'       => $account->id,
				'country'          => strtoupper( $account->country ),
				'default_currency' => strtoupper( $account->default_currency )
			);
		}
		update_option( $this->get_option_key(), $this->settings, 'yes' );
	}

	public function pre_api_update() {
		$settings                = App::get( API::class );
		$this->previous_settings = array(
			'secret' => $settings->get_option( 'secret_key_test' )
		);
	}

	public function post_api_update() {
		$api_settings = App::get( API::class );
		$settings     = array(
			'secret' => $api_settings->get_option( 'secret_key_test' )
		);
		$is_valid     = array_filter( $settings ) == $settings;
		if ( ( ! isset( $this->settings['test'] ) || $settings != $this->previous_settings ) && $is_valid ) {
			$this->save_account_settings( null, 'test' );
		}
	}

	public function connection_test_success( $mode ) {
		if ( $mode === Constants::TEST ) {
			unset( $this->settings[ Constants::TEST ] );
			$this->post_api_update();
		}
	}

	public function get_account_country( $mode = 'live' ) {
		if ( $mode === Constants::LIVE ) {
			$country = $this->get_option( 'country' );
		} else {
			$settings = $this->get_option( 'test', self::DEFAULT_ACCOUNT_SETTINGS );
			$country  = $settings['country']; // @phpstan-ignore-line
		}

		return $country;
	}

	public function get_account_id( $mode = 'live' ) {
		if ( $mode === Constants::LIVE ) {
			$id = $this->get_option( 'account_id' );
		} else {
			$settings = $this->get_option( Constants::TEST, self::DEFAULT_ACCOUNT_SETTINGS );
			$id       = $settings['account_id']; // @phpstan-ignore-line
		}

		return $id;
	}

	public function delete_account_settings() {
		delete_option( $this->get_option_key() );
	}

}