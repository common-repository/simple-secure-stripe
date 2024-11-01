<?php

namespace SimpleSecureWP\SimpleSecureStripe\Admin;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;

/**
 *
 * @since   1.0.0
 * @package Stripe/Admin
 *
 */
class Menus {

	public function admin_menu() {
		add_menu_page( __( 'Stripe Gateway', 'simple-secure-stripe' ), __( 'Stripe Gateway', 'simple-secure-stripe' ), 'manage_woocommerce', 'sswps', null, null, '7.458' );
	}

	public function sub_menu() {
		add_submenu_page( 'woocommerce', __( '⇨ Stripe', 'simple-secure-stripe' ), __( '⇨ Stripe', 'simple-secure-stripe' ), 'manage_woocommerce', 'sswps-main', [ $this, 'main_page' ] );
	}

	public function main_page() {
		$section = Request::get_sanitized_var( 'section' );

		if ( isset( $section ) ) {
			$section = sanitize_text_field( $section );
			do_action( 'sswps/admin_section_' . $section );
		} else {
			echo App::get( Views\Welcome::class )->render();
		}
	}

}
