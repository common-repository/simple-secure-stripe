<?php

namespace SimpleSecureWP\SimpleSecureStripe\Tokens;

/**
 *
 * @author Simple & Secure WP
 * @package Stripe/Tokens
 *
 */
class Google_Pay extends CC {

	protected $type = 'Stripe_GooglePay';

	public function get_formats() {
		return [
				'gpay_name' => [
					'label'   => __( 'Gateway Name', 'simple-secure-stripe' ),
					'example' => 'Visa 1111 (Google Pay)',
					'format'  => '{brand} {last4} (Google Pay)',
				],
			] + parent::get_formats();
	}

	public function get_basic_payment_method_title() {
		return __( 'Google Pay', 'simple-secure-stripe' );
	}
}
