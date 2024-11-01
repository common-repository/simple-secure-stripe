<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit;

use SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit\Checkout\Compatibility\ExpressButtonController;
use SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit\Upsell\PaymentGateways;

class Main {

	public function __construct() {
		if ( $this->enabled() ) {
			new PaymentGateways( new AssetsApi() );
		}
		if ( $this->is_acp_enabled() ) {
			new ExpressButtonController( new AssetsApi() );
		}
	}

	private function enabled() {
		return function_exists( 'WFOCU_Core' );
	}

	private function is_acp_enabled() {
		return class_exists( 'WFACP_Core' );
	}

}