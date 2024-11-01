<?php

namespace SimpleSecureWP\SimpleSecureStripe\Features;

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
		$this->container->register( BNPL\Controller::class );
		$this->container->register( Link\Controller::class );
	}
}