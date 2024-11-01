<?php

namespace SimpleSecureWP\SimpleSecureStripe\Payment\Traits;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Customer_Manager;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentIntent;
use SimpleSecureWP\SimpleSecureStripe\Stripe\SetupIntent;
use WC_Order;
use WP_Error;

/**
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Trait
 */
trait Intent {

	public function get_payment_object() {
		return Payment\Factory::load( 'payment_intent', $this, Gateway::load() );
	}

	/**
	 *
	 * @param WC_Order $order
	 */
	public function get_confirmation_method( $order = null ) {
		return 'manual';
	}

	/**
	 *
	 * @param PaymentIntent|SetupIntent $intent
	 * @param WC_Order      $order
	 */
	public function get_payment_intent_checkout_url( $intent, $order, $type = 'intent' ) {
		global $wp;

		// rand is used to generate some random entropy so that window hash events are triggered.
		$args = [
			'type'          => $type,
			'client_secret' => $intent->client_secret,
			'order_id'      => $order->get_id(),
			'order_key'     => $order->get_order_key(),
			'gateway_id'    => $this->id,
			'status'        => $intent->status,
			'pm'            => $intent->payment_method,
			'entropy'       => rand( 0, 999999 ),
		];

		$save_source_key = Request::get_sanitized_var( $this->save_source_key );

		if ( ! empty( $wp->query_vars['order-pay'] ) ) {
			$args['save_method'] = ! empty( $save_source_key );
		}

		return sprintf( '#response=%s', rawurlencode( base64_encode( wp_json_encode( $args ) ) ) );
	}

	/**
	 * @param PaymentIntent $intent
	 * @param WC_Order      $order
	 */
	public function get_payment_intent_confirmation_args( $intent, $order ) {
		return [];
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array|void
	 */
	public function handle_setup_intent_for_order( $order ) {
		if ( defined( Constants::PROCESSING_PAYMENT ) ) {
			if ( $this->is_mandate_required( $order ) ) {
				$setup_intent = $this->gateway->setupIntents->retrieve( $order->get_meta( Constants::SETUP_INTENT_ID ) );
				if ( ! empty( $setup_intent->mandate ) ) {
					$order->update_meta_data( Constants::STRIPE_MANDATE, $setup_intent->mandate );
				}
				$this->payment_method_token = $setup_intent->payment_method;
			} else {
				$result = $this->save_payment_method( $this->get_new_source_token(), $order );
				if ( is_wp_error( $result ) ) {
					wc_add_notice( $result->get_error_message(), 'error' );

					return $this->get_order_error();
				}
			}
			// The setup intent ID is no longer needed so remove it from the order
			$order->delete_meta_data( Constants::SETUP_INTENT_ID );
		} else {
			$setup_intent        = $this->get_payment_intent_id();
			$save_payment_method = $this->should_save_payment_method( $order );
			// if setup intent exists then it was created client side.
			// attempt to save the payment method
			if ( $setup_intent && ( $save_payment_method || $this->is_mandate_required( $order ) ) ) {
				$payment_method_details = null;
				if ( $this->is_mandate_required( $order ) ) {
					// if a mandate was required, the payment method has already been attached.
					$setup_intent_obj       = $this->gateway->setupIntents->retrieve( $setup_intent, array( 'expand' => array( 'payment_method' ) ) );
					$payment_method_details = $setup_intent_obj->payment_method;
					$order->update_meta_data( Constants::STRIPE_MANDATE, $setup_intent_obj->mandate );
				}
				$result = $this->save_payment_method( $this->get_new_source_token(), $order, $payment_method_details );
				if ( is_wp_error( $result ) ) {
					wc_add_notice( $result->get_error_message(), 'error' );

					return $this->get_order_error();
				}
			} elseif ( ! $setup_intent && $save_payment_method ) {
				// A new payment method is being used but there's no setup intent provided
				// by client. Create one here
				$result = $this->does_order_require_action( $order, $this->get_new_source_token() );
				if ( is_wp_error( $result ) ) {
					wc_add_notice( sprintf( __( 'Error processing payment. Reason: %s', 'simple-secure-stripe' ), $result->get_error_message() ), 'error' );

					return $this->get_order_error();
				} elseif ( $result ) {
					return $result;
				} else {
					$this->save_payment_method( $this->get_new_source_token(), $order );
				}
			} else {
				$this->payment_method_token = $this->get_saved_source_id();
				if ( $this->is_mandate_required( $order ) ) {
					// update the setup-intent with the saved payment method info
					$order->update_meta_data( Constants::SETUP_INTENT_ID, WC()->session->get( Constants::SETUP_INTENT_ID ) );

					return $this->does_order_require_action( $order, $this->payment_method_token );
				}
			}
		}
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array|void
	 */
	public function process_zero_total_order( $order ) {
		$result = $this->handle_setup_intent_for_order( $order );
		if ( $result && isset( $result['result'] ) ) {
			return $result;
		}

		return $this->payment_object->process_zero_total_order( $order, $this );
	}

	/**
	 * @param WC_Order $order
	 */
	public function process_pre_order( $order ) {
		$token        = null;
		$setup_intent = $this->get_payment_intent_id();
		if ( defined( Constants::PROCESSING_PAYMENT ) ) {
			$token = $this->create_payment_method( $this->get_new_source_token(), $order->get_meta( Constants::CUSTOMER_ID ) );
		} else {
			if ( ! $this->use_saved_source() ) {
				if ( ! $order->get_customer_id() ) {
					$customer = App::get( Customer_Manager::class )->create_customer( WC()->customer );
					if ( is_wp_error( $customer ) ) {
						return wc_add_notice( $customer->get_error_message(), 'error' );
					}
					$order->update_meta_data( Constants::CUSTOMER_ID, $customer->id );
				} else {
					$order->update_meta_data( Constants::CUSTOMER_ID, sswps_get_customer_id( $order->get_customer_id() ) );
				}
				$order->save();
				if ( ! $setup_intent ) {
					$result = $this->does_order_require_action( $order, $this->get_new_source_token() );
					if ( $result ) {
						if ( is_wp_error( $result ) ) {
							wc_add_notice( $result->get_error_message(), 'error' );
							$result = $this->get_order_error();
						}

						return $result;
					}
				}
				$token = $this->create_payment_method( $this->get_new_source_token(), $order->get_meta( Constants::CUSTOMER_ID ) );
			} else {
				$this->payment_method_token = $this->get_saved_source_id();
			}
		}

		\WC_Pre_Orders_Order::mark_order_as_pre_ordered( $order );
		$this->save_zero_total_meta( $order, $token );
		$this->payment_object->destroy_session_data();

		return [
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		];
	}

	/**
	 * @param WC_Order $order
	 * @param string   $payment_method
	 *
	 * @return array|bool|WP_Error
	 */
	private function does_order_require_action( $order, $payment_method ) {
		if ( ( $intent_id = $order->get_meta( Constants::SETUP_INTENT_ID ) ) ) {
			$params = array_filter( [
				'payment_method'       => $payment_method,
				'payment_method_types' => [ $this->get_payment_method_type() ],
			] );
			$intent = $this->gateway->setupIntents->update( $intent_id, apply_filters( 'sswps/update_setup_intent_params', $params, $order ) );
		} else {
			$params = [
				'confirm'              => false,
				'usage'                => 'off_session',
				'metadata'             => [
					'gateway_id' => $this->id,
					'order_id'   => $order->get_id(),
				],
				'payment_method_types' => [ $this->get_payment_method_type() ],
			];
			if ( $payment_method ) {
				$params['payment_method'] = $payment_method;
				$params['confirm']        = true;
			}
			$this->add_stripe_order_args( $params, $order );
			$intent = $this->payment_object->get_gateway()->setupIntents->create( apply_filters( 'sswps/setup_intent_params', $params, $order, $this ) );
		}
		if ( is_wp_error( $intent ) ) {
			return $intent;
		}
		$order->update_meta_data( Constants::SETUP_INTENT_ID, $intent->id );
		if ( ! empty( $intent->mandate ) ) {
			$order->update_meta_data( Constants::STRIPE_MANDATE, $intent->mandate );
		}
		$order->save();

		if (
			in_array( $intent->status, [
				'requires_action',
				'requires_payment_method',
				'requires_source_action',
				'requires_source',
				'requires_confirmation',
			], true )
		) {
			return [
				'result'   => 'success',
				'redirect' => $this->get_payment_intent_checkout_url( $intent, $order, 'setup_intent' ),
			];
		} elseif ( $intent->status === 'succeeded' ) {
			$this->payment_method_token = $intent->payment_method;
			// The setup intent ID is no longer needed so remove it from the order
			$order->delete_meta_data( Constants::SETUP_INTENT_ID );

			return false;
		}

		return false;
	}

	/**
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_deferred_intent_creation() {
		return false;
	}

}
