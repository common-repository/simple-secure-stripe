<?php
namespace SimpleSecureWP\SimpleSecureStripe\Utils;

class URL {
	/**
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function main() : string {
		return admin_url( 'admin.php?page=sswps-main' );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param string $section
	 *
	 * @return string
	 */
	public static function wc_settings( string $section ) : string {
		return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $section );
	}
}