<?php

namespace SimpleSecureWP\SimpleSecureStripe\Tokens;

/**
 *
 * @since   1.0.0
 * @package Stripe/Tokens
 * @author Simple & Secure WP
 *
 */
class Local extends Abstract_Token {

	use Traits\Source;

	protected $type = 'Stripe_Local';

	protected $stripe_data = [ 'gateway_title' => '' ];

	public function details_to_props( $details ) {}

	public function set_gateway_title( $value ) {
		$this->set_prop( 'gateway_title', $value );
	}

	public function get_gateway_title( $context = 'view' ) {
		return $this->get_prop( 'gateway_title', $context );
	}

	public function get_formats() {
		return apply_filters( 'sswps/get_local_token_formats', [
			'gateway_title' => [
				'label'   => __( 'Gateway Title', 'simple-secure-stripe' ),
				'example' => $this->get_brand(),
				'format'  => '{gateway_title}',
			],
		], $this );
	}

	public function get_html_classes() {
		return $this->get_gateway_id();
	}

}
