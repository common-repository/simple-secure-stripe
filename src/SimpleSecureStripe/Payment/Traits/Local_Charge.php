<?php

namespace SimpleSecureWP\SimpleSecureStripe\Payment\Traits;

use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Payment;

/**
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Trait
 */
trait Local_Charge {

	public function get_payment_object() {
		return Payment\Factory::load( 'local_charge', $this, Gateway::load() );
	}

}