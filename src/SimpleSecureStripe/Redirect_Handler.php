<?php

namespace SimpleSecureWP\SimpleSecureStripe;

use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WC_Payment_Gateway;

/**
 *
 * @author Simple & Secure WP
 * @package Stripe/Classes
 *
 */
class Redirect_Handler {

	/**
	 * Check if this request is for a local payment redirect.
	 */
	public static function local_payment_redirect() {
		$local_payment   = Request::get_sanitized_var( '_sswps_local_payment' );
		$voucher_payment = Request::get_sanitized_var( Constants::VOUCHER_PAYMENT );
		$order_id        = Request::get_sanitized_var( 'order-id' );
		if ( ! empty( $local_payment ) ) {
			self::process_redirect();
		} elseif ( ! empty( $voucher_payment ) && ! empty( $order_id ) ) {
			self::process_voucher_redirect();
		}
	}

	/**
	 */
	public static function process_redirect() {
		$source = Request::get_sanitized_var( 'source' );
		$payment_intent = Request::get_sanitized_var( 'payment_intent' );
		if ( ! empty( $source ) ) {
			$result        = Gateway::load()->sources->retrieve( wc_clean( $source ) );
			$client_secret = Request::get_sanitized_var( 'client_secret', '' );
		} else {
			$result        = Gateway::load()->paymentIntents->retrieve( wc_clean( $payment_intent ) );
			$client_secret = Request::get_sanitized_var('payment_intent_client_secret', '' );
		}
		if ( ! hash_equals( $client_secret, $result->client_secret ) ) {
			wc_add_notice( __( 'This request is invalid. Please try again.', 'simple-secure-stripe' ), 'error' );
		} else {
			define( Constants::REDIRECT_HANDLER, true );
			$order_id = null;
			if ( isset( $result->metadata['order_id'] ) ) {
				$order_id = $result->metadata['order_id'];
			} else {
				$order_id = Request::get_sanitized_var( 'order_id' );
				$key      = Request::get_sanitized_var( 'key' );
				if ( isset( $key, $order_id ) ) {
					$order = wc_get_order( absint( $order_id ) );
					if ( $order && ! $order->key_is_valid( $key ) ) {
						sswps_log_info( 'Invalid order key provided while processing redirect.' );
					} else {
						$order_id = absint( $order_id );
					}
				}
			}
			$order = wc_get_order( sswps_filter_order_id( $order_id, $result ) );
			if ( ! $order ) {
				return;
			}

			/**
			 *
			 * @var Gateways\Abstract_Local_Payment $payment_method
			 */
			$payment_method = WC()->payment_gateways()->payment_gateways()[ $order->get_payment_method() ];
			$redirect       = $payment_method->get_return_url( $order );

			if ( in_array( $result->status, [ 'requires_action', 'pending' ] ) ) {
				if ( $result->status === 'pending' ) {
					$order->update_status( 'on-hold' );
				} else {
					return;
				}
			} elseif ( in_array( $result->status, [ 'requires_payment_method', 'failed' ] ) ) {
				wc_add_notice( __( 'Payment authorization failed. Please select another payment method.', 'simple-secure-stripe' ), 'error' );
				sswps_log_info( sprintf( 'User cancelled their payment and has been redirected to the checkout page. Payment Method: %s. Order ID: %s', $payment_method->id, $order->get_id() ) );
				if ( $result instanceof \SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentIntent ) {
					$order->update_meta_data( Constants::PAYMENT_INTENT, Utils\Misc::sanitize_intent( $result->toarray() ) );
				} else {
					$order->delete_meta_data( Constants::SOURCE_ID );
				}
				$order->update_status( 'pending' );

				return;
			} elseif ( 'chargeable' === $result->status ) {
				if ( ! $payment_method->has_order_lock( $order ) && ! $order->get_transaction_id() ) {
					$payment_method->set_order_lock( $order );
					$payment_method->set_new_source_token( $result->id );
					$result = $payment_method->process_payment( $order_id );
					// we don't release the order lock so there aren't conflicts with the source.chargeable webhook
					if ( $result['result'] === 'success' ) {
						$redirect = $result['redirect'];
					}
				}
			} elseif ( in_array( $result->status, [ 'succeeded', 'requires_capture' ] ) ) {
				if ( ! $payment_method->has_order_lock( $order ) ) {
					$payment_method->set_order_lock( $order );
					$result = $payment_method->process_payment( $order_id );
					if ( $result['result'] === 'success' ) {
						$redirect = $result['redirect'];
					}
				}
			} elseif ( $result->status === 'processing' && isset( $result->charges->data ) ) {
				if ( ! $payment_method->has_order_lock( $order ) ) {
					$payment_method->set_order_lock( $order );
					$payment_method->payment_object->payment_complete( $order, $result->charges->data[0] );
					Utils\Misc::delete_payment_intent_to_session();
					$payment_method->release_order_lock( $order );
				}
				// if this isn't the checkout page, then skip redirect
				if ( ! is_checkout() ) {
					return;
				}
			}
			wp_safe_redirect( $redirect );
			exit();
		}
	}

	public static function maybe_restore_cart() {
		global $wp;
		$sswps_product_checkout = Request::get_sanitized_var( 'sswps_product_checkout' );
		if ( isset( $wp->query_vars['order-received'] ) && ! empty( $sswps_product_checkout ) ) {
			add_action( 'woocommerce_cart_emptied', 'sswps_restore_cart_after_product_checkout' );
		}
	}

	private static function process_voucher_redirect() {
		$payment_method_key = sanitize_text_field( Request::get_sanitized_var( Constants::VOUCHER_PAYMENT, '' ) );
		$payment_methods    = WC()->payment_gateways()->payment_gateways();
		/**
		 * @var WC_Payment_Gateway $payment_method
		 */
		$payment_method  = $payment_methods[ $payment_method_key ];
		$order           = wc_get_order( absint( wc_clean( Request::get_sanitized_var( 'order-id' ) ) ) );
		$order_key       = wc_clean( wp_unslash( Request::get_sanitized_var( 'order-key', '' ) ) );
		if ( $order && hash_equals( $order->get_order_key(), $order_key ) ) {
			if ( method_exists( $payment_method, 'process_voucher_order_status' ) ) {
				$payment_method->process_voucher_order_status( $order );
				wp_safe_redirect( $payment_method->get_return_url( $order ) );
				exit();
			}
		}
	}

}
