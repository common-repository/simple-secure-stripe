<?php

namespace SimpleSecureWP\SimpleSecureStripe\Admin\Views;

class Welcome extends Abstract_Page {
	/**
	 * Builds the HTML for the welcome page.
	 *
	 * @return mixed|null
	 */
	public function render() {
		$args = [
			'user'      => wp_get_current_user(),
			'signed_up' => get_option( 'sswps_admin_signup', false ),
		];

		ob_start();

		$template = $this->template( 'welcome/page', $args, false );

		if ( ! empty( $template ) ) {
			echo $template;
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
		return apply_filters( 'sswps/welcome_html', $html, $args );
	}
}