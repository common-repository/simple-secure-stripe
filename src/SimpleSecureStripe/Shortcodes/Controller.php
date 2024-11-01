<?php
namespace SimpleSecureWP\SimpleSecureStripe\Shortcodes;

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
		$this->container->singleton( Payment_Buttons::class, new Payment_Buttons() );
	}
}