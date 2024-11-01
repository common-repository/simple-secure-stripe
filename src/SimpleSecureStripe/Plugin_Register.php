<?php
namespace SimpleSecureWP\SimpleSecureStripe;

use SimpleSecureWP\SimpleSecureStripe\Dependencies\Dependency;

/**
 * Base dependency register.
 *
 * Registers plugin dependencies.
 *
 * @since 1.0.0
 */
class Plugin_Register {
	/**
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected array $classes_req = [];

	/**
	 * @var array
	 */
	protected array $dependencies = [
		'parent-dependencies' => [],
		'co-dependencies'     => [],
		'addon-dependencies'  => [],
		'third-party'         => [],
	];

	public function __construct() {
		$this->dependencies['third-party']['WooCommerce'] = [
			'version'  => '3.0.0',
			'callback' => [ $this, 'validate_woocommerce' ]
		];

		$this->register_plugin();
	}

	/**
	 * Registers a plugin with dependencies.
	 *
	 * @since 1.0.0
	 */
	public function register_plugin() {
		App::get( Dependency::class )->register_plugin(
			Plugin::FILE,
			Plugin::class,
			Plugin::VERSION,
			$this->classes_req,
			$this->dependencies
		);
	}

	/**
	 * Validates if WooCommerce is the right version.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function validate_woocommerce() : bool {
		if ( ! defined( 'WC_PLUGIN_FILE' ) ) {
			return false;
		}

		if ( -1 === version_compare( WC()->version, '3.0.0' ) ) {
			return false;
		}

		return true;
	}
}