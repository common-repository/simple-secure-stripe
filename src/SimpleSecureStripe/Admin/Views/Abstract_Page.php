<?php
namespace SimpleSecureWP\SimpleSecureStripe\Admin\Views;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Template;

abstract class Abstract_Page extends Template {
	/**
	 * @inheritDoc
	 */
	public $template_namespace = 'sswps';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->set_template_origin( Plugin::class );
		$this->set_template_folder( 'src/admin-views' );

		// Configures this templating class to extract variables.
		$this->set_template_context_extract( true );

		// Does not use public folders.
		$this->set_template_folder_lookup( false );
	}

	public static function make() {
		$context = sswps_context();
		$section = $context->get( 'wc_settings_section' );

		if ( ! $section ) {
			return null;
		}

		if ( App::has( $section ) ) {
			echo App::get( $section )->admin_options();
		}
	}
}