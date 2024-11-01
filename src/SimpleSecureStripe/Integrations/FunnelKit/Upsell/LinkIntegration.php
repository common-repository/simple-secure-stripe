<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit\Upsell;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Features\Link\Link;
use WC_Order;

class LinkIntegration {

	public function __construct() {
		add_filter( 'sswps/funnelkit_upsell_create_payment_intent', [ $this, 'add_payment_intent_params' ], 10, 3 );
	}

	/**
	 * @param array              $params
	 * @param WC_Order          $order
	 * @param Gateway $client
	 *
	 * @return array
	 */
	public function add_payment_intent_params( $params, $order, $client ) {
		if ( $order->get_payment_method() === 'sswps_cc' ) {
			if ( App::get( Link::class )->is_active() ) {
				$payment_intent = $client->mode( $order )->paymentIntents->retrieve( $order->get_meta( Constants::PAYMENT_INTENT_ID ) );
				$params['payment_method_types'] = array_values( array_unique( array_merge( $params['payment_method_types'], $payment_intent->payment_method_types ) ) );
			}
		}

		return $params;
	}

}