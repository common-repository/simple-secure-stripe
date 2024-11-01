<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\CheckoutWC;

use SimpleSecureWP\SimpleSecureStripe\Plugin;

class Main {

	public function __construct() {
		if ( $this->is_active() ) {
			new AssetsController();
		}
	}

	private function is_active() {
		return defined( 'CFW_NAME' );
	}

}