<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\GermanMarket;

use SimpleSecureWP\SimpleSecureStripe\Assets\API as AssetAPI;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

class Package {

	public function __construct() {
		add_action( 'woocommerce_init', [ $this, 'initialize' ] );
	}

	public function initialize() {
		if ( $this->is_enabled() ) {
			$assets = new AssetAPI(
				dirname( __DIR__ ),
				trailingslashit( plugin_dir_url( __DIR__ ) )
			);
			add_action( 'wp_enqueue_scripts', function() use ( $assets ) {
				if ( wc_post_content_has_shortcode( 'woocommerce_de_check' ) ) {
					$assets->register_style( 'sswps-german-market', 'integrations/GermanMarket/germanmarket-styles.css' );
					wp_enqueue_style( 'sswps-german-market' );
				}
			} );
		}
	}

	private function is_enabled() {
		return class_exists( 'Woocommerce_German_Market' );
	}

}