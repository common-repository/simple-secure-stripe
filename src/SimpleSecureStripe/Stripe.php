<?php
namespace SimpleSecureWP\SimpleSecureStripe;

class Stripe {
	/**
	 * @var string
	 */
	protected string $client_id = 'ca_NZe0YKUvso7Sfmel7yle5y50yeKfkfHq';

	/**
	 * Constructor!
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( defined( 'SIMPLESECUREWP_STRIPE_CLIENT_ID' ) && wp_get_environment_type() !== 'production' ) {
			$this->client_id = SIMPLESECUREWP_STRIPE_CLIENT_ID; // phpstan-ignore-line
		}
	}

	/**
	 * Returns the Stripe client ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_client_id() : string {
		return $this->client_id;
	}
}