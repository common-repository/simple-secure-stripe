<?php

namespace SimpleSecureWP\SimpleSecureStripe;

use SimpleSecureWP\SimpleSecureStripe\StellarWP\ContainerContract\ContainerInterface;
use SimpleSecureWP\SimpleSecureStripe\DI\Container as PHPDIContainer;
use SimpleSecureWP\SimpleSecureStripe\DI\Definition;

class Container implements ContainerInterface {
	/**
	 * Lazy callbacks.
	 *
	 * @var array
	 */
	protected $callbacks = [];

	/**
	 * @var PHPDIContainer
	 */
	protected $container;

	/**
	 * Bound items that are singletons.
	 *
	 * The key is the bound object, the value is null until it has been resolved for the first time,
	 * at which point it will hold 'resolved' as the value.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $singletons = [];

	/**
	 * Container constructor.
	 */
	public function __construct() {
		$this->container = new PHPDIContainer();
	}

	/**
	 * @inheritDoc
	 */
	public function bind( string $name, $implementation = null ) {
		if (
			is_object( $implementation )
			|| is_callable( $implementation )
		) {
			$this->container->set( $name, $implementation );
			return;
		}

		$this->container->set( $name, new Definition\Helper\CreateDefinitionHelper( $implementation ) );
	}

	/**
	 * Returns a lambda function suitable to use as a callback; when called the function will build the implementation
	 * bound to `$name` and return the value of a call to `$method` method with the call arguments.
	 *
	 * @param string|object $name             A fully-qualified class name, a bound slug or an object o call the
	 *                                        callback on.
	 * @param string        $method           The method that should be called on the resolved implementation with the
	 *                                        specified array arguments.
	 *
	 * @return mixed The called method return value.
	 */
	public function callback( $name, $method ) {
		$callback_name_prefix = is_object( $name ) ? spl_object_hash( $name ) : $name;

		if ( ! is_string( $callback_name_prefix ) ) {
			$type = gettype( $name );
			throw new \Exception(
				"Callbacks can only be built on bound aliases, class names, or objects; '{$type}' is neither."
			);
		}

		if ( ! is_string( $method ) ) {
			throw new \Exception( "The callback's second argument must be a method name as a string." );
		}

		$callback_id = $callback_name_prefix . '::' . $method;

		if ( isset( $this->callbacks[ $callback_id ] ) ) {
			return $this->callbacks[ $callback_id ];
		}

		$closure = function( ...$args ) use ( $name, $method ) {
			$obj = $this->get( $name );
			return $obj->{$method}( ...$args );
		};

		$this->callbacks[ $callback_id ] = $closure;

		return $closure;
	}

	/**
	 * @inheritDoc
	 */
	public function get( string $name ) {
		if ( isset( $this->singletons[ $name ] ) && $this->singletons[ $name ] === false ) {
			$object = $this->container->get( $name );
			$this->singletons[ $name ] = 'resolved';
			$this->container->set( $name, $object );
			return $object;
		}

		return $this->container->get( $name );
	}

	/**
	 * Returns a variable stored in the container.
	 *
	 * If the variable is a binding then the binding will be resolved before returning it.
	 *
	 * @param string     $key     The alias of the variable or binding to fetch.
	 * @param mixed|null $default A default value to return if the variable is not set in the container.
	 *
	 * @return mixed The variable value or the resolved binding.
	 */
	public function get_var( string $key, $default = null ) {
		if ( $this->container->has( $key ) ) {
			return $this->container->get( $key );
		}

		return $default;
	}

	/**
	 * @inheritDoc
	 */
	public function has( string $name ) {
		return $this->container->has( $name );
	}

	/**
	 * Registers a service provider.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The alias of the service provider to register.
	 */
	public function register( string $name ) {
		/** @var Abstract_Controller $controller */
		$controller = new $name( $this );

		if ( ! $controller instanceof Abstract_Controller ) {
			throw new \Exception( 'Service providers must extend the Abstract_Controller class.' );
		}

		$this->singleton( $name, $controller );
		$controller->set_as_registered();
		$controller->register();
	}

	/**
	 * Sets a var.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_var( string $key, $value ) {
		$this->container->set( $key, $value );
	}

	/**
	 * @inheritDoc
	 */
	public function singleton( string $name, $implementation = null ) {
		if ( isset( $this->singletons[ $name ] ) ) {
			return;
		}

		$this->bind( $name, $implementation );
		$this->singletons[ $name ] = false;
	}
}
