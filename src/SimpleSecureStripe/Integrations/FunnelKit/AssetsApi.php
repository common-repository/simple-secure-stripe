<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit;

use SimpleSecureWP\SimpleSecureStripe\Plugin;

class AssetsApi {

	public function register_script( $handle, $relative_path, $deps = [] ) {
		wp_register_script( $handle, SIMPLESECUREWP_STRIPE_ASSETS . $relative_path, $deps, Plugin::VERSION, true );
	}

	public function enqueue_script( $handle, $relative_path, $deps = [] ) {
		$this->register_script( $handle, $relative_path, $deps );
		wp_enqueue_script( $handle );
	}

	public function register_style( $handle, $relative_path ) {
		wp_register_style( $handle, SIMPLESECUREWP_STRIPE_ASSETS . $relative_path );
	}

	public function enqueue_style( $handle, $relative_path ) {
		$this->register_style( $handle, $relative_path );
		wp_enqueue_style( $handle );
	}

	public function do_script_items( $handles ) {
		global $wp_scripts;
		if ( is_string( $handles ) ) {
			$handles = [ $handles ];
		}
		$wp_scripts->do_items( $handles );
	}

}