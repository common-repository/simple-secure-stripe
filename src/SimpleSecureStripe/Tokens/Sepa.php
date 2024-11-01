<?php

namespace SimpleSecureWP\SimpleSecureStripe\Tokens;

/**
 *
 * @since   1.0.0
 * @package Stripe/Tokens
 * @author Simple & Secure WP
 *
 */
class Sepa extends Local {

	use Traits\Payment_Method;

	protected $type = 'Stripe_Sepa';

	protected $stripe_data = [
		'bank_code'   => '',
		'last4'       => '',
		'mandate_url' => '',
		'mandate'     => '',
	];

	public function details_to_props( $details ) {
		if ( isset( $details['sepa_debit'] ) ) {
			$this->set_last4( $details['sepa_debit']['last4'] );
			$this->set_bank_code( $details['sepa_debit']['bank_code'] );
			$this->set_mandate( isset( $details['sepa_debit']['mandate'] ) ? $details['sepa_debit']['mandate'] : '' );
			$this->set_mandate_url( isset( $details['sepa_debit']['mandate_url'] ) ? $details['sepa_debit']['mandate_url'] : '' );
		}
	}

	public function set_last4( $value ) {
		$this->set_prop( 'last4', $value );
	}

	public function get_last4( $context = 'view' ) {
		return $this->get_prop( 'last4', $context );
	}

	public function set_bank_code( $value ) {
		$this->set_prop( 'bank_code', $value );
	}

	public function get_bank_code( $context = 'view' ) {
		return $this->get_prop( 'bank_code', $context );
	}

	public function set_mandate_url( $value ) {
		$this->set_prop( 'mandate_url', $value );
	}

	public function set_mandate( $value ) {
		$this->set_prop( 'mandate', $value );
	}

	public function get_mandate_url( $context = 'view' ) {
		return $this->get_prop( 'mandate_url', $context );
	}

	public function get_mandate( $context = '$view' ) {
		return $this->get_prop( 'mandate', $context );
	}

	public function get_brand( $context = 'view' ) {
		return __( 'SEPA', 'simple-secure-stripe' );
	}

	public function get_formats() {
		return wp_parse_args( [
			'type_ending_last4' => [
				'label'   => __( 'Gateway Title', 'simple-secure-stripe' ),
				'example' => __( 'Sepa ending in 0005', 'simple-secure-stripe' ),
				'format'  => __( '{brand} ending in {last4}', 'simple-secure-stripe' ),
			],
			'type_last4'        => [
				'label'   => __( 'Type Last 4', 'simple-secure-stripe' ),
				'example' => __( 'Sepa 0005', 'simple-secure-stripe' ),
				'format'  => '{brand} {last4}',
			],
		], parent::get_formats() );
	}

}