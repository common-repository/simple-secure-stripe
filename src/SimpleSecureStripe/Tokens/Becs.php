<?php

namespace SimpleSecureWP\SimpleSecureStripe\Tokens;

/**
 *
 * @since   1.0.0
 * @package Stripe/Tokens
 * @author Simple & Secure WP
 *
 */
class Becs extends Local {

	use Traits\Payment_Method;

	protected $type = 'Stripe_Becs';

	protected $stripe_data = [
		'bsb_number' => '',
		'last4'      => '',
		'mandate'    => '',
	];

	public function details_to_props( $details ) {
		if ( isset( $details['au_becs_debit'] ) ) {
			$this->set_last4( $details['au_becs_debit']['last4'] );
			$this->set_bsb_number( $details['au_becs_debit']['bsb_number'] );
			$this->set_mandate( $details['au_becs_debit']['mandate'] );
		}
	}

	public function set_last4( $value ) {
		$this->set_prop( 'last4', $value );
	}

	public function get_last4( $context = 'view' ) {
		return $this->get_prop( 'last4', $context );
	}

	public function set_bsb_number( $value ) {
		$this->set_prop( 'bsb_number', $value );
	}

	public function get_bsb_number( $context = 'view' ) {
		return $this->get_prop( 'bsb_number', $context );
	}

	public function set_mandate( $value ) {
		$this->set_prop( 'mandate', $value );
	}

	public function get_mandate( $context = 'view' ) {
		return $this->get_prop( 'mandate', $context );
	}

	public function get_brand( $context = 'view' ) {
		return __( 'BECS', 'simple-secure-stripe' );
	}

	public function get_formats() {
		return wp_parse_args( [
			'type_ending_last4' => [
				'label'   => __( 'Gateway Title', 'simple-secure-stripe' ),
				'example' => 'BECS ending in 0005',
				'format'  => __( '{brand} ending in {last4}', 'simple-secure-stripe' ),
			],
			'type_last4'        => [
				'label'   => __( 'Type Last 4', 'simple-secure-stripe' ),
				'example' => 'BECS 1111',
				'format'  => '{brand} {last4}',
			],
		], parent::get_formats() );
	}

}