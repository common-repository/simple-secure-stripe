<?php

namespace SimpleSecureWP\SimpleSecureStripe\API;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;

/**
 * @since 1.0.0
 */
class Controller extends Abstract_Controller {
	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( Messages::class, Messages::class );

		$this->hooks();
	}

	protected function hooks() {
		add_filter( 'sswps/api_get_wp_error', $this->container->callback( Messages::class, 'filter_error_message' ) );
		add_filter( 'sswps/payment_intent_args', [ $this, 'expand_payment_intent_properties' ] );

		if ( $this->container->get( Settings\Advanced::class )->is_fee_enabled() ) {
			add_filter( 'sswps/payment_intent_confirmation_args', [ $this, 'expand_balance_transaction' ] );
			add_filter( 'sswps/payment_intent_retrieve_args', [ $this, 'expand_balance_transaction' ] );
			add_filter( 'sswps/payment_intent_capture_args', [ $this, 'expand_balance_transaction' ] );
			add_filter( 'sswps/charge_order_args', [ $this, 'expand_balance_transaction_for_charge' ] );
		}
	}

	public function expand_balance_transaction( $args ) {
		if ( ! is_array( $args ) ) {
			$args = [];
		}
		$args['expand']   = isset( $args['expand'] ) ? $args['expand'] : [];
		$args['expand'][] = 'charges.data.balance_transaction';

		return $args;
	}

	public function expand_payment_intent_properties( $args ) {
		$args['expand']   = isset( $args['expand'] ) ? $args['expand'] : [];
		$args['expand'][] = 'payment_method';
		if ( $this->container->get( Settings\Advanced::class )->is_fee_enabled() ) {
			$args = $this->expand_balance_transaction( $args );
		}

 		return $args;
	}

	public function expand_balance_transaction_for_charge( $args ) {
		$args['expand']   = isset( $args['expand'] ) ? $args['expand'] : [];
		$args['expand'][] = 'balance_transaction';

		return $args;
	}

}