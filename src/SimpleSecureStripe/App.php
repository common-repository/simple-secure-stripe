<?php

namespace SimpleSecureWP\SimpleSecureStripe;

class App {
	/**
	 * A reference to the singleton instance of the DI container
	 * the application uses as Service Locator.
	 *
	 * @var Container|null
	 */
	protected static $container;

	/**
	 * Returns the singleton instance of the DI container the application
	 * will use as Service Locator.
	 *
	 * @return Container The singleton instance of the Container used as Service Locator
	 *                   by the application.
	 */
	public static function container() : Container {
		if ( ! isset( static::$container ) ) {
			static::$container = new Container();
		}

		return static::$container;
	}

	/**
	 * Sets the container instance the Application should use as a Service Locator.
	 *
	 * If the Application already stores a reference to a Container instance, then
	 * this will be replaced by the new one.
	 *
	 * @param Container $container    A reference to the Container instance the Application
	 *                                should use as a Service Locator.
	 *
	 * @return void The method does not return any value.
	 */
	public static function set_container( Container $container ) {
		static::$container = $container;
	}

	/**
	 * Sets a variable on the container.
	 *
	 * @param string $key   The alias the container will use to reference the variable.
	 * @param mixed  $value The variable value.
	 *
	 * @return void The method does not return any value.
	 */
	public static function set_var( $key, $value ) {
		static::container()->set_var( $key, $value );
	}

	/**
	 * Binds an interface a class or a string slug to an implementation and will always return the same instance.
	 *
	 * @param string $id                            A class or interface fully qualified name or a string slug.
	 * @param mixed  $implementation                The implementation that should be bound to the alias(es); can be a
	 *                                              class name, an object or a closure.
	 *
	 * @return void This method does not return any value.
	 */
	public static function singleton( string $id, $implementation = null ) {
		static::container()->singleton( $id, $implementation );
	}

	/**
	 * Returns a variable stored in the container.
	 *
	 * If the variable is a binding then the binding will be resolved before returning it.
	 *
	 * @see Container::get()
	 *
	 * @param string     $key     The alias of the variable or binding to fetch.
	 * @param mixed|null $default A default value to return if the variable is not set in the container.
	 *
	 * @return mixed The variable value or the resolved binding.
	 */
	public static function get_var( string $key, $default = null ) {
		return static::container()->get_var( $key, $default );
	}

	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string $id A fully qualified class or interface name or an already built object.
	 *
	 * @return mixed The entry for an id.
	 */
	public static function get( $id ) {
		return static::container()->get( $id );
	}

	/**
	 * Test if the container can provide something for the given name.
	 *
	 * @param string $name Entry name or a class name.
	 *
	 * @throws \InvalidArgumentException The name parameter must be of type string.
	 * @return bool
	 */
	public static function has( $name ) {
		return static::container()->has( $name );
	}

	/**
	 * Registers a service provider.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The alias of the service provider to register.
	 */
	public static function register( $name ) {
		static::container()->register( $name );
	}

	/**
	 * Define an object or a value in the container.
	 *
	 * @param string $name Entry name
	 * @param mixed|DefinitionHelper $value Value, use definition helpers to define objects
	 */
	public static function bind( string $name, $value ) {
		static::container()->bind( $name, $value );
	}

	/**
	 * Define an object or a value in the container.
	 *
	 * @param string $name Entry name
	 * @param mixed|DefinitionHelper $value Value, use definition helpers to define objects
	 */
	public static function set( string $name, $value ) {
		static::bind( $name, $value );
	}

	/**
	 * Returns a lambda function suitable to use as a callback; when called the function will build the implementation
	 * bound to `$id` and return the value of a call to `$method` method with the call arguments.
	 *
	 * @param string|object $id               A fully-qualified class name, a bound slug or an object o call the
	 *                                        callback on.
	 * @param string        $method           The method that should be called on the resolved implementation with the
	 *                                        specified array arguments.
	 *
	 * @return mixed The called method return value.
	 */
	public static function callback( $id, $method ) {
		return static::container()->callback( $id, $method );
	}
}
