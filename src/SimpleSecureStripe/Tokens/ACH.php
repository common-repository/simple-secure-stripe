<?php

namespace SimpleSecureWP\SimpleSecureStripe\Tokens;

/**
 *
 * @since   1.0.0
 * @package Stripe/Tokens
 * @author Simple & Secure WP
 *
 */
class ACH extends Abstract_Token {

	use Traits\Payment_Method;

	protected $type = 'Stripe_ACH';

	protected $stripe_data = [
		'bank_name'      => '',
		'routing_number' => '',
		'last4'          => '',
		'account_type'   => '',
	];

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Token::details_to_props()
	 */
	public function details_to_props( $details ) {
		$bank = [];

		if ( isset( $details['us_bank_account'] ) ) {
			$bank = $details['us_bank_account'];
		} elseif ( isset( $details['ach_debit'] ) ) {
			// Plaid used this property
			$bank = $details['ach_debit'];
		} elseif ( $details instanceof \SimpleSecureWP\SimpleSecureStripe\Stripe\BankAccount ) {
			$bank = $details;
		}

		if ( empty( $bank ) ) {
			return;
		}

		$this->set_brand( $bank['bank_name'] );
		$this->set_bank_name( $bank['bank_name'] );
		$this->set_last4( $bank['last4'] );
		$this->set_routing_number( $bank['routing_number'] );
		$this->set_account_type( $bank['account_type'] );
	}

	public function get_bank_name( $context = 'view' ) {
		return $this->get_prop( 'bank_name', $context );
	}

	public function get_routing_number( $context = 'view' ) {
		return $this->get_prop( 'routing_number', $context );
	}

	public function get_last4( $context = 'view' ) {
		return $this->get_prop( 'last4', $context );
	}

	public function get_account_type( $context = 'view' ) {
		return $this->get_prop( 'account_type', $context );
	}

	public function set_bank_name( $value ) {
		$this->set_prop( 'bank_name', $value );
	}

	public function set_routing_number( $value ) {
		$this->set_prop( 'routing_number', $value );
	}

	public function set_last4( $value ) {
		$this->set_prop( 'last4', $value );
	}

	public function set_account_type( $value ) {
		$this->set_prop( 'account_type', $value );
	}

	public function get_formats() {
		return apply_filters( 'sswps/get_token_formats', [
			'type_ending_in'    => [
				'label'   => __( 'Type Ending In', 'simple-secure-stripe' ),
				'example' => 'Chase ending in 3434',
				'format'  => __( '{bank_name} ending in {last4}', 'simple-secure-stripe' ),
			],
			'name_masked_last4' => [
				'label'   => __( 'Type Ending In', 'simple-secure-stripe' ),
				'example' => 'Chase **** 3434',
				'format'  => __( '{bank_name} **** {last4}', 'simple-secure-stripe' ),
			],
			'short_title'       => [
				'label'   => __( 'Gateway Title', 'simple-secure-stripe' ),
				'example' => $this->get_basic_payment_method_title(),
				'format'  => '{short_title}',
			],
		], $this );
	}

	public function get_html_classes() {
		return 'sswps-ach';
	}

	public function get_basic_payment_method_title() {
		return __( 'Bank Payment', 'simple-secure-stripe' );
	}

}
