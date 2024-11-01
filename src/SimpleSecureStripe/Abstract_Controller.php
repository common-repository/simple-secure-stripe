<?php
namespace SimpleSecureWP\SimpleSecureStripe;

abstract class Abstract_Controller {
	/**
	 * @var Container
	 */
	protected Container $container;

	/**
	 * Has the register method been called?
	 *
	 * @var bool
	 */
	protected bool $has_registered = false;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Container $container
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Registers the bindings for the service provider.
	 *
	 * @since 1.0.0
	 */
	abstract public function register();

	/**
	 * Has the register method been called?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_registered() : bool {
		return $this->has_registered;
	}


	/**
	 * Marks the provider as registered.
	 *
	 * @since 1.0.0
	 */
	public function set_as_registered() {
		$this->has_registered = true;
	}
}