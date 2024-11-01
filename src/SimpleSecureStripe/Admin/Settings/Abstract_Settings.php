<?php

namespace SimpleSecureWP\SimpleSecureStripe\Admin\Settings;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Traits;
use WC_Settings_API;

/**
 *
 * @author Simple & Secure WP
 * @package Stripe/Abstract
 *
 */
abstract class Abstract_Settings extends WC_Settings_API {

	use Traits\Settings;

	public function __construct() {
		App::singleton( $this->id, $this );
		$this->init_form_fields();
		$this->init_settings();
		$this->hooks();
	}

	public function hooks() {
		add_action( 'sswps/localize_' . $this->id . '_settings', [ $this, 'localize_settings' ] );
	}

	public function localize_settings() {
		return $this->settings;
	}
}
