<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks;

use Automattic\WooCommerce\Blocks\Registry\Container;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

class Package {

	public function init() {
		if ( $this->is_active() ) {
			$this->container()->get( Config::class );
		}
	}

	/**
	 *
	 * Loads the Blocks integration if WooCommerce Blocks is installed as a feature plugin.
	 */
	private function is_active() {
		if ( \class_exists( '\Automattic\WooCommerce\Blocks\Package' ) ) {
			if ( $this->is_core_plugin_build() ) {
				return true;
			}

			/* @phpstan-ignore-next-line */
			if ( \method_exists( '\Automattic\WooCommerce\Blocks\Package', 'feature' ) ) {
				$feature = \Automattic\WooCommerce\Blocks\Package::feature();
				if ( \method_exists( $feature, 'is_feature_plugin_build' ) ) {
					if ( $feature->is_feature_plugin_build() ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	private function is_core_plugin_build() {
		return \function_exists( 'WC' ) && \version_compare( '6.9.0', WC()->version, '<=' );
	}

	/**
	 * @return Container
	 */
	public function container() {
		static $container;
		if ( ! $container ) {
			$container = \Automattic\WooCommerce\Blocks\Package::container();
			$container->register( Config::class, function( $container ) {
				return new Config( Plugin::VERSION, $container, dirname( __DIR__ ) );
			} );
		}

		return $container;
	}


}