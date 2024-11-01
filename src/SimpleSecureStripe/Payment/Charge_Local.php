<?php

namespace SimpleSecureWP\SimpleSecureStripe\Payment;

use Exception;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use WC_Order;
use WP_Error;

/**
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Classes
 *
 */
class Charge_Local extends Charge {

	/**
	 *
	 * @param WC_Order $order
	 */
	public function process_payment( $order ) {

		/**
		 * If there is no order lock, then this is not being processed via a webhook
		 */
		if ( ! $this->payment_method->has_order_lock( $order ) ) {
			try {
				if ( ( $source_id = $this->payment_method->get_new_source_token() ) ) {
					// source was created client side.
					$source = $this->gateway->mode( sswps_order_mode( $order ) )->sources->retrieve( $source_id );
					$this->save_order_data( $source_id, $order );

					// update the source's metadata with the order id
					if ( 'pending' === $source->status ) {
						$source = $this->gateway->mode( sswps_order_mode( $order ) )->sources->update( $source_id, $this->payment_method->get_update_source_args( $order ) );
					}
				} else {
					if ( $this->payment_method->use_saved_source() ) {
						$source_id = $this->payment_method->get_saved_source_id();
						$source    = $source = $this->gateway->mode( sswps_order_mode( $order ) )->sources->retrieve( $source_id );
					} else {
						// create the source
						$args                         = $this->payment_method->get_source_args( $order );
						$args['metadata']['order_id'] = $order->get_id();
						$args['metadata']['created']  = time();
						$source                       = $this->gateway->mode( sswps_order_mode( $order ) )->sources->create( $args );

					}
				}

				$this->save_order_data( $source->id, $order );

				/**
				 * If source is chargeable, then proceed with processing it.
				 */
				if ( $source->status === 'chargeable' ) {
					$this->payment_method->set_order_lock( $order );
					$this->payment_method->set_new_source_token( $source->id );

					return $this->process_payment( $order );
				}

				return (object) [
					'complete_payment' => false,
					'redirect'         => $this->payment_method->get_source_redirect_url( $source, $order ),
				];
			} catch ( Exception $e ) {
				return new WP_Error( 'source-error', $e->getMessage() );
			}
		} else {
			/**
			 * There is an order lock so this order is ready to be processed.
			 */
			return parent::process_payment( $order );
		}
	}

	/**
	 * @param string   $source_id
	 * @param WC_Order $order
	 */
	private function save_order_data( $source_id, $order ) {
		$order->update_meta_data( Constants::MODE, sswps_mode() );
		$order->update_meta_data( Constants::SOURCE_ID, $source_id );
		$order->save();
	}
}
