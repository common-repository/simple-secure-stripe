<?php

namespace SimpleSecureWP\SimpleSecureStripe\Admin;

use SimpleSecureWP\SimpleSecureStripe\Customer_Manager;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use SimpleSecureWP\SimpleSecureStripe\StripeIntegration\Client;
use SimpleSecureWP\SimpleSecureStripe\Tokens;
use WC_Payment_Tokens;
use WP_User;

/**
 *
 * @since   1.0.0
 * @package Stripe/Admin
 * @author Simple & Secure WP
 *
 */
class User_Edit {
	/**
	 *
	 * @param WP_User $user
	 */
	public function output( $user ) {
		// enquue scripts
		wp_enqueue_style( 'sswps-admin-style' );

		remove_filter( 'woocommerce_get_customer_payment_tokens', 'sswps_get_customer_payment_tokens' );
		// get payment methods for all environments.
		$tokens          = WC_Payment_Tokens::get_customer_tokens( $user->ID );
		$payment_methods = [
			'live' => [],
			'test' => [],
		];
		foreach ( $tokens as $token ) {
			if ( $token instanceof Tokens\Abstract_Token ) {
				if ( 'live' === $token->get_environment() ) {
					$payment_methods['live'][] = $token;
				} else {
					$payment_methods['test'][] = $token;
				}
			}
		}

		if ( current_user_can( 'manage_woocommerce' ) ) {
			include SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/admin-views/user-profile.php';
		}
	}

	/**
	 *
	 * @param int $user_id
	 */
	public function save( $user_id ) {
		$old_live_id = null;
		$old_test_id = null;

		// only users with "manage_woocommerce" can update the user's Stripe customer ID's.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$modes = [ 'test', 'live' ];
		$live_id = Request::get_sanitized_var( 'sswps_live_id' );
		$test_id = Request::get_sanitized_var( 'sswps_test_id' );
		if ( ! empty( $live_id ) ) {
			$old_live_id = sswps_get_customer_id( $user_id, 'live' );
			sswps_delete_customer( $user_id, 'live', true );
			sswps_save_customer( wc_clean( $live_id ), $user_id, 'live' );
		}
		if ( ! empty( $test_id ) ) {
			$old_test_id = sswps_get_customer_id( $user_id, 'test' );
			sswps_delete_customer( $user_id, 'test', true );
			sswps_save_customer( wc_clean( $test_id ), $user_id, 'test' );
		}

		$payment_methods = Request::get_sanitized_var( 'payment_methods' );

		// check if admin want's to delete any payment methods
		foreach ( $modes as $mode ) {
			$payment_method_actions = Request::get_sanitized_var( $mode . '_payment_method_actions' );
			if ( ! empty( $payment_method_actions ) ) {
				switch ( wc_clean( $payment_method_actions ) ) {
					case 'delete':
						if ( ! empty( $payment_methods ) && ! empty( $payment_methods[ $mode ] ) ) {
							$tokens = wc_clean( $payment_methods[ $mode ] );
							foreach ( $tokens as $identifer ) {
								[ $id, $pm ] = explode( ':', $identifer );
								WC_Payment_Tokens::delete( absint( $id ) );
								Client::service( 'paymentMethods', $mode )->detach( $pm );
								sswps_log_info( sprintf( 'Payment method %s detached within Stripe via WordPress Edit Profile page. Initiated by User ID: %s', $pm, get_current_user_id() ) );
							}
						}
						break;
				}
			}
		}

		$changes = [
			'live' => $old_live_id !== sswps_get_customer_id( $user_id, 'live' ),
			'test' => $old_test_id !== sswps_get_customer_id( $user_id, 'test' ),
		];

		// this will prevent the payment method from being deleted in Stripe. We only want to remove the tokens
		// from the WC tables.
		remove_action( 'woocommerce_payment_token_deleted', 'sswps_woocommerce_payment_token_deleted', 10 );

		// want results to return tokens for all modes
		remove_action( 'woocommerce_get_customer_payment_tokens', 'sswps_get_customer_payment_tokens' );

		// if the value has changed, then remove old payment methods and import new ones.
		foreach ( $changes as $mode => $change ) {
			if ( $change ) {
				// Delete all current payment methods in WC then save new ones.
				$tokens = WC_Payment_Tokens::get_customer_tokens( $user_id );
				foreach ( $tokens as $token ) {
					if ( $token instanceof Tokens\Abstract_Token ) {
						if ( $mode === $token->get_environment() ) {
							WC_Payment_Tokens::delete( $token->get_id() );
						}
					}
				}
				// import payment methods from Stripe.
				if ( ( $customer_id = sswps_get_customer_id( $user_id, $mode ) ) ) {
					Customer_Manager::sync_payment_methods( $customer_id, $user_id, $mode );
				}
			}
		}
	}

}
