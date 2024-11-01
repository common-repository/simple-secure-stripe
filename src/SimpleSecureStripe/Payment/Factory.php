<?php

namespace SimpleSecureWP\SimpleSecureStripe\Payment;

use Exception;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Gateways;

/**
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Classes
 *
 */
class Factory {

	private static $classes = [
		'charge'         => Charge::class,
		'payment_intent' => Intent::class,
		'local_charge'   => Charge_Local::class,
	];

	/**
	 *
	 * @param string                    $type
	 * @param Gateways\Abstract_Gateway $payment_method
	 * @param Gateway                   $gateway
	 */
	public static function load( $type, $payment_method, $gateway ) {
		$classes = apply_filters( 'sswps/payment_classes', self::$classes );
		if ( ! isset( $classes[ $type ] ) ) {
			throw new Exception( 'No class defined for type ' . $type );
		}
		$classname = $classes[ $type ];

		$args = func_get_args();

		if ( count( $args ) > 3 ) {
			$args     = array_slice( $args, 3 );
			$instance = new $classname( $payment_method, $gateway, ...$args );
		} else {
			$instance = new $classname( $payment_method, $gateway );
		}
		return $instance;
	}
}
