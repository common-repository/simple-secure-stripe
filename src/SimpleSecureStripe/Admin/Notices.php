<?php

namespace SimpleSecureWP\SimpleSecureStripe\Admin;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

/**
 *
 * @since  1.0.0
 * @author Simple & Secure WP
 *
 */
class Notices {

	public function notices() {
		$messages = [];
		foreach ( $this->get_notices() as $key => $notice ) {
			if ( $notice['callback']() ) {
				$screen    = get_current_screen();
				$screen_id = $screen ? $screen->id : '';
				ob_start();
				echo '<div class="notice notice-info woocommerce-message"><p>' . $notice['message']() . '</p></div>';
				$message = ob_get_clean();
				if ( strstr( $screen_id, 'wc-settings' ) ) {
					$messages[] = $message;
				} else {
					echo $message;
				}
			}
		}
		// in WC 4.0 admin notices don't show on the WC Settings pages so adding this workaround.
		if ( $messages ) {
			wp_localize_script( 'sswps-admin-settings', 'sswps_admin_notices', $messages );
		}
	}

	public function get_notices() : array {
		return [];
	}
}
