<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceSubscriptions;

use SimpleSecureWP\SimpleSecureStripe\Constants;

class Retry_Manager {

	/**
	 * @var int|mixed
	 */
	private $retries = 0;

	/**
	 * @var int|mixed
	 */
	private $max_retries;

	public function __construct( $max_retries = 1 ) {
		$this->max_retries = $max_retries;
	}

	/**
	 * @param \WC_Order                            $order
	 * @param \SimpleSecureWP\SimpleSecureStripe\Gateway $client
	 * @param \WP_Error|Payment_Intent             $result
	 * @param array                                $params
	 *
	 * @return bool
	 */
	public function should_retry( $order, $client, $result, $params ) {
		$data = $result->get_error_data();

		if ( ! $this->has_retries() ) {
			return false;
		}

		if ( ! isset( $data['param'], $params['customer'], $params['payment_method'] ) ) {
			return false;
		}

		if ( 'payment_method' !== $data['param'] ) {
			return false;
		}

		// check if the payment method's customer doesn't match the customer associated with the subscription
		$payment_method = $client->paymentMethods->retrieve( $params['payment_method'] );

		if ( $payment_method->customer === $params['customer'] ) {
			return false;
		}

		$order->update_meta_data( Constants::CUSTOMER_ID, $payment_method->customer );
		$order->save();
		$subscription = wc_get_order( $order->get_meta( '_subscription_renewal' ) );
		if ( $subscription ) {
			$subscription->update_meta_data( Constants::CUSTOMER_ID, $payment_method->customer );
			$subscription->save();
		}
		sswps_log_info( sprintf( 'Retrying payment for renewal order %s. Reason: %s', $order->get_id(), $result->get_error_message() ) );
		$this->retries += 1;

		return true;
	}

	private function has_retries() {
		return $this->retries < $this->max_retries;
	}

}