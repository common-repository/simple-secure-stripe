<?php

namespace SimpleSecureWP\SimpleSecureStripe\Controllers;

use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateway;
use SimpleSecureWP\SimpleSecureStripe\Gateways;

class PaymentIntent {

	/**
	 * @var array The list of payment methods ID's that are compatible
	 */
	private $payment_method_ids;

	private static $instance;

	/**
	 * @param array $payment_method_ids
	 */
	public function __construct( $payment_method_ids ) {
		$this->payment_method_ids = $payment_method_ids;
		$this->initialize();
		self::$instance = $this;
	}

	public static function instance() {
		return self::$instance;
	}

	private function initialize() {
		add_action( 'woocommerce_before_pay_action', [ $this, 'set_order_pay_constants' ] );
		add_action( 'woocommerce_checkout_update_order_review', [ $this, 'update_order_review' ] );
		//add_filter( 'sswps/get_localize_script_data_sswps', [ $this, 'add_script_params' ], 10, 1 );
		//add_filter( 'sswps/blocks_general_data', [ $this, 'add_blocks_general_data' ] );
	}

	public function get_element_options() {
		$params = [
			'mode'                 => 'payment',
			'payment_method_types' => $this->get_payment_method_types(),
		];
		if ( $this->is_setup_intent_needed() ) {
			$params['mode'] = 'setup';
		} else {
			$params['paymentMethodCreation'] = 'manual';
		}

		return $params;
	}

	protected function is_payment_intent_required_for_frontend() {
		return count( $this->get_payment_method_types() ) > 0;
	}

	private function is_deferred_intent_creation() {
		return count( $this->get_payment_method_types() ) > 0;
	}

	private function get_payment_method_types() {
		$payment_method_types = [];
		$payment_gateways     = WC()->payment_gateways()->payment_gateways();
		foreach ( $this->payment_method_ids as $id ) {
			$payment_method = isset( $payment_gateways[ $id ] ) ? $payment_gateways[ $id ] : null;
			if ( $payment_method && $payment_method instanceof Gateways\Abstract_Gateway ) {
				if ( wc_string_to_bool( $payment_method->enabled ) && $payment_method->is_deferred_intent_creation() ) {
					$payment_method_types[] = $payment_method->get_payment_method_type();
				}
			}
		}

		return $payment_method_types;
	}

	private function is_setup_intent_needed() {
		return ( is_add_payment_method_page() || apply_filters( 'sswps/create_setup_intent', false ) ) && $this->is_payment_intent_required_for_frontend();
	}

	public function set_order_pay_constants() {
		wc_maybe_define_constant( Constants::WOOCOMMERCE_STRIPE_ORDER_PAY, true );
	}

	public function update_order_review() {
		if ( $this->is_deferred_intent_creation() ) {
			add_filter( 'woocommerce_update_order_review_fragments', [ $this, 'add_element_options_to_fragments' ] );
		}
	}

	public function add_element_options_to_fragments( $fragments ) {
		$fragments['.sswps-element-options'] = rawurlencode( base64_encode( wp_json_encode( $this->get_element_options() ) ) );

		return $fragments;
	}

	public function add_script_params( $data ) {
		$data['stripeParams']['betas'][] = 'elements_enable_deferred_intent_beta_1';

		return $data;
	}

	/**
	 * @param $data
	 *
	 * @todo remove once betas and headers are no longer needed.
	 */
	public function add_blocks_general_data( $data ) {
		$data['stripeParams']['betas'][] = 'elements_enable_deferred_intent_beta_1';

		return $data;
	}

}