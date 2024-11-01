<?php

namespace SimpleSecureWP\SimpleSecureStripe\Tokens\Traits;

use SimpleSecureWP\SimpleSecureStripe\StripeIntegration\Client;

/**
 *
 * @since   1.0.0
 * @author  Simple & Secure WP
 * @package Stripe/Trait
 *
 */
trait Source {

	public function save_payment_method() {
		return Client::service( 'customers', sswps_mode() )->createSource( $this->get_customer_id(), [ 'source' => $this->get_token() ] );
	}

	public function delete_from_stripe() {
		return Client::service( 'sources', sswps_mode() )->detach( $this->get_customer_id(), $this->get_token() );
	}
}