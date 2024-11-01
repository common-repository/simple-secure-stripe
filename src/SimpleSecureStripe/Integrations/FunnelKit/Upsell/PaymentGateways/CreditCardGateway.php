<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit\Upsell\PaymentGateways;

class CreditCardGateway extends BasePaymentGateway {

	protected $key = 'sswps_cc';

	public function initialize() {
		add_filter( 'sswps/cc_show_save_source', [ $this, 'show_save_source' ] );
	}

	public function show_save_source( $bool ) {
		if ( $bool ) {
			$bool = ! $this->should_tokenize();
		}

		return $bool;
	}
}