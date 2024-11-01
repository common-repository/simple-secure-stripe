<?php

namespace SimpleSecureWP\SimpleSecureStripe\Tokens;

use SimpleSecureWP\SimpleSecureStripe\Constants;

/**
 * @author Simple & Secure WP
 * @package Stripe/Tokens
 *
 */
class CC extends Abstract_Token {

	use Traits\Payment_Method;

	protected $has_expiration = true;

	protected $type = 'Stripe_CC';

	protected $stripe_payment_type = 'payment_method';

	protected $stripe_data = [
		'brand'         => '',
		'exp_month'     => '',
		'exp_year'      => '',
		'last4'         => '',
		'masked_number' => '',
	];

	public function details_to_props( $details ) {
		$card = null;

		if ( isset( $details['type'] ) && $details['type'] === 'link' ) {
			$this->set_brand( 'link' );
			$this->set_format( 'short_title' );
		} else {
			if ( isset( $details['card'] ) ) {
				$card = $details['card'];
			}
			if ( $details instanceof \SimpleSecureWP\SimpleSecureStripe\Stripe\Card ) {
				$card = $details;
			}
			$this->set_brand( $card['brand'] );
			$this->set_last4( $card['last4'] );
			$this->set_exp_month( $card['exp_month'] );
			$this->set_exp_year( $card['exp_year'] );
			$this->set_masked_number( sprintf( '********%s', $card['last4'] ) );
			if ( ! empty( $card['mandate'] ) ) {
				$this->update_meta_data( Constants::STRIPE_MANDATE, $card['mandate'] );
			}
		}
	}

	public function get_last4( $context = 'view' ) {
		return $this->get_prop( 'last4', $context );
	}

	public function get_masked_number( $context = 'view' ) {
		return $this->get_prop( 'masked_number', $context );
	}

	public function set_last4( $last4 ) {
		$this->set_prop( 'last4', $last4 );
	}

	public function set_masked_number( $value ) {
		$this->set_prop( 'masked_number', $value );
	}

	public function get_exp_year( $context = 'view' ) {
		return $this->get_prop( 'exp_year', $context );
	}

	public function set_exp_year( $year ) {
		$this->set_prop( 'exp_year', $year );
	}

	public function get_exp_month( $context = 'view' ) {
		return $this->get_prop( 'exp_month', $context );
	}

	public function set_exp_month( $month ) {
		$this->set_prop( 'exp_month', str_pad( $month, 2, '0', STR_PAD_LEFT ) );
	}

	public function get_html_classes() {
		return sprintf( '%s', str_replace( ' ', '', strtolower( $this->get_prop( 'brand' ) ) ) );
	}

	public function get_card_type( $context = 'view' ) {
		return $this->get_brand( $context );
	}

	public function get_formats() {
		return apply_filters( 'sswps/get_token_formats', [
			'type_ending_in'          => [
				'label'   => __( 'Type Ending In', 'simple-secure-stripe' ),
				'example' => 'Visa ending in 1111',
				'format'  => __( '{brand} ending in {last4}', 'simple-secure-stripe' ),
			],
			'type_masked_number'      => [
				'label'   => __( 'Type Masked Number', 'simple-secure-stripe' ),
				'example' => 'Visa ********1111',
				'format'  => '{brand} {masked_number}',
			],
			'type_dash_masked_number' => [
				'label'   => __( 'Type Dash Masked Number', 'simple-secure-stripe' ),
				'example' => 'Visa - ********1111',
				'format'  => '{brand} - {masked_number}',
			],
			'type_last4'              => [
				'label'   => __( 'Type Last 4', 'simple-secure-stripe' ),
				'example' => 'Visa 1111',
				'format'  => '{brand} {last4}',
			],
			'type_dash_last4'         => [
				'label'   => __( 'Type Dash & Last 4', 'simple-secure-stripe' ),
				'example' => 'Visa - 1111',
				'format'  => '{brand} - {last4}',
			],
			'last4'                   => [
				'label'   => __( 'Last Four', 'simple-secure-stripe' ),
				'example' => '1111',
				'format'  => '{last4}',
			],
			'card_type'               => [
				'label'   => __( 'Card Type', 'simple-secure-stripe' ),
				'example' => 'Visa',
				'format'  => '{brand}',
			],
			'short_title'             => [
				'label'   => __( 'Gateway Title', 'simple-secure-stripe' ),
				'example' => $this->get_basic_payment_method_title(),
				'format'  => '{short_title}',
			],
		], $this );
	}

	public function get_basic_payment_method_title() {
		if ( strtolower( $this->get_prop( 'brand' ) ) === 'link' ) {
			return __( 'Link by Stripe', 'simple-secure-stripe' );
		}

		return __( 'Credit Card', 'simple-secure-stripe' );
	}

	public function get_payment_method_title( $format = '' ) {
		if ( strtolower( $this->get_prop( 'brand' ) ) === 'link' ) {
			return $this->get_basic_payment_method_title();
		}

		return parent::get_payment_method_title( $format );
	}

}
