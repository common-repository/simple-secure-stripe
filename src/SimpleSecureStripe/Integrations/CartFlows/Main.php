<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\CartFlows;

class Main {

	public static function init() {
		if ( self::cartflows_enabled() ) {
			new PaymentsApi();
			new RoutesApi();
			add_action( 'sswps/after_get_checkout_fields', function () {
				try {
					// @3.3.27 - Added to ensure Cartflows doesn't duplicate the billing email field. The
					// checkout fields need to be reset to ensure the woocommerce_checkout_fields filter runs
					$reflection_class = new \ReflectionClass( '\WC_Checkout' );
					$prop             = $reflection_class->getProperty( 'fields' );
					$prop->setAccessible( true );
					$prop->setValue( WC()->checkout(), null );
				} catch ( \ReflectionException $e ) {
				}
			} );
		}
	}

	public static function cartflows_enabled() {
		return defined( 'CARTFLOWS_FILE' );
	}

	public static function cartflows_pro_enabled() {
		return defined( 'CARTFLOWS_PRO_FILE' );
	}

}