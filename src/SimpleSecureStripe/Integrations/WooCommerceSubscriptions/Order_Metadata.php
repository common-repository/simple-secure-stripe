<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceSubscriptions;

use SimpleSecureWP\SimpleSecureStripe\Checker;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Stripe;
use SimpleSecureWP\SimpleSecureStripe\Gateways\Abstract_Gateway;
use SimpleSecureWP\SimpleSecureStripe\Tokens\Abstract_Token;
use WC_Order;

class Order_Metadata {

	/**
	 * @param WC_Order         $order
	 * @param Abstract_Gateway $payment_method
	 * @param Stripe\Charge    $charge
	 * @param Abstract_Token   $token
	 *
	 * @return void
	 * @throws \WC_Data_Exception
	 */
	public function save_order_metadata( $order, $payment_method, $charge = null, $token = null ) {
		// if WCS is active and there are subscriptions in the order, save meta data
		if ( ! wcs_stripe_active() || ! wcs_order_contains_subscription( $order ) ) {
			return;
		}

		if ( ! $charge ) {
			$this->save_zero_total_order_metadata( $order, $payment_method, $token );
		}

		foreach ( wcs_get_subscriptions_for_order( $order ) as $subscription ) {
			$subscription->set_transaction_id( $charge->id );
			$subscription->set_payment_method_title( $token->get_payment_method_title() );
			$subscription->update_meta_data( Constants::MODE, sswps_mode() );
			$subscription->update_meta_data( Constants::CHARGE_STATUS, $charge->status );
			$subscription->update_meta_data( Constants::PAYMENT_METHOD_TOKEN, $token->get_token() );
			$subscription->update_meta_data( Constants::CUSTOMER_ID, sswps_get_customer_id( $order->get_user_id() ) );
			if ( isset( $charge->payment_method_details->card->mandate ) ) {
				$subscription->update_meta_data( Constants::STRIPE_MANDATE, $charge->payment_method_details->card->mandate );
			} elseif ( $payment_method->is_mandate_required( $order ) ) {
				// load the token from the database so it's mandate can be added to the subscription
				$token = $payment_method->get_token( $token->get_token(), $token->get_user_id() );
				if ( $token ) {
					$subscription->update_meta_data( Constants::STRIPE_MANDATE, $token->get_meta( Constants::STRIPE_MANDATE ) );
				}
			}
			$subscription->save();
		}
	}

	/**
	 * @param WC_Order         $order
	 * @param Abstract_Gateway $payment_method
	 * @param Abstract_Token   $token
	 *
	 * @return void
	 */
	private function save_zero_total_order_metadata( $order, $payment_method, $token ) {
		if ( ! Checker::is_woocommerce_subscriptions_active() || ! wcs_order_contains_subscription( $order ) ) {
			return;
		}

		foreach ( wcs_get_subscriptions_for_order( $order ) as $subscription ) {
			$subscription->set_payment_method_title( $token->get_payment_method_title() );
			$subscription->update_meta_data( Constants::MODE, sswps_mode() );
			$subscription->update_meta_data( Constants::PAYMENT_METHOD_TOKEN, $token->get_token() );
			$subscription->update_meta_data( Constants::CUSTOMER_ID, sswps_get_customer_id( $order->get_user_id() ) );
			if ( $payment_method->is_mandate_required( $order ) ) {
				$subscription->update_meta_data( Constants::STRIPE_MANDATE, $order->get_meta( Constants::STRIPE_MANDATE ) );
			}
			$subscription->save();
		}
	}

}