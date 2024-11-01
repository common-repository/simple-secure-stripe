<?php

namespace SimpleSecureWP\SimpleSecureStripe\Tokens;

/**
 *
 * @author Simple & Secure WP
 * @package Stripe/Tokens
 *
 */
class Apple_Pay extends CC {

	protected $type = 'Stripe_ApplePay';

	public function get_basic_payment_method_title() {
		return __( 'Apple Pay', 'simple-secure-stripe' );
	}
}
