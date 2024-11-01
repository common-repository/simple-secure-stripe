<?php
namespace SimpleSecureWP\SimpleSecureStripe\Admin\Views;

use SimpleSecureWP\SimpleSecureStripe\App;

/**
 *
 * @author Simple & Secure WP
 *
 */
class Settings extends Abstract_Page {
	/**
	 * Has the settings section rendered already?
	 *
	 * @since 1.0.0
	 *
	 * @var array<string, bool>
	 */
	protected $rendered = [];

	/**
	 * Builds the HTML for the settings page.
	 *
	 * @return mixed|null
	 */
	public function render() {
		ob_start();

		$context = sswps_context();
		$section = $context->get( 'wc_settings_section' );
		$args    = [
			'sub_template' => $section,
		];

		if ( ! $section ) {
			return null;
		}

		if ( ! empty( $this->rendered[ $section ] ) ) {
			return null;
		}

		if ( App::has( $section ) ) {
			$args['settings']           = App::get( $section );
			$template                   = $this->template( 'settings/settings', $args, false );
			$this->rendered[ $section ] = true;

			if ( ! empty( $template ) ) {
				echo $template;
			}
		}

		$html = ob_get_clean();

		/**
		 * Filter the HTML for the welcome page.
		 *
		 * @since 1.0.0
		 *
		 * @param string $html The HTML for the welcome page.
		 * @param array  $args The arguments passed to the template.
		 */
		return apply_filters( 'sswps/settings_html', $html, $args );
	}

	/**
	 * Echoes the rendered HTML.
	 */
	public function output() {
		echo $this->render();
	}

	public function save() {
		global $current_section;
		if ( $current_section && ! did_action( 'woocommerce_update_options_checkout_' . $current_section ) ) {
			do_action( 'woocommerce_update_options_checkout_' . $current_section );
		}
	}

	public function admin_settings_tabs( $tabs ) {
		$tabs['sswps_affirm'] = __( 'Local Gateways', 'simple-secure-stripe' );

		return $tabs;
	}
}
