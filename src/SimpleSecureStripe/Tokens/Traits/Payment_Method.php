<?php

namespace SimpleSecureWP\SimpleSecureStripe\Tokens\Traits;

use SimpleSecureWP\SimpleSecureStripe\Gateway;

/**
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Trait
 *
 */
trait Payment_Method {

	public function save_payment_method() {
		return Gateway::load( sswps_mode() )->paymentMethods->attach( $this->get_token(), [ 'customer' => $this->get_customer_id() ] );
	}

	public function delete_from_stripe() {
		return Gateway::load( sswps_mode() )->paymentMethods->detach( $this->get_token() );
	}
}
