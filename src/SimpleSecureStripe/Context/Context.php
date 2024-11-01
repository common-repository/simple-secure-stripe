<?php
namespace SimpleSecureWP\SimpleSecureStripe\Context;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Utils\Arr;
use WP;
use WP_Query;

/**
 * Class Context
 *
 * @since 1.0.0
 */
class Context {

	/**
	 * The value that will be used to indicate no value was found in any location while trying to read it.
	 *
	 * @since 1.0.0
	 */
	const NOT_FOUND = '__not_found__';

	/**
	 * The key to locate a context value as the value of a request variable.
	 *
	 * @since 1.0.0
	 */
	const REQUEST_VAR = 'request_var';

	/**
	 * The key to locate a context value as the value of an option.
	 *
	 * @since 1.0.0
	 */
	const OPTION = 'option';

	/**
	 * The key to locate a context value as the value of a transient.
	 *
	 * @since 1.0.0
	 */
	const TRANSIENT = 'transient';

	/**
	 * The key to locate a context value as the value of the main query (global `$wp_query`) query var.
	 *
	 * @since 1.0.0
	 */
	const QUERY_VAR = 'query_var';

	/**
	 * The key to locate a context value as the value of the main query (global `$wp_query`) property.
	 *
	 * @since 1.0.0
	 */
	const QUERY_PROP = 'query_prop';

	/**
	 * The key to locate a context value as the value of the main query (global `$wp_query`) method return value.
	 *
	 * @since 1.0.0
	 */
	const QUERY_METHOD = 'query_method';

	/**
	 * The key to locate a context value as the value of a constant.
	 *
	 * @since 1.0.0
	 */
	const CONSTANT = 'constant';

	/**
	 * The key to locate a context value as a static class property.
	 *
	 * @since 1.0.0
	 */
	const STATIC_PROP = 'static_prop';

	/**
	 * The key to locate a context value as property of an object.
	 *
	 * @since 1.0.0
	 */
	const PROP = 'prop';

	/**
	 * The key to locate a context value as result running a static class method.
	 *
	 * @since 1.0.0
	 */
	const STATIC_METHOD = 'static_method';

	/**
	 * The key to locate a context value as result running a method on an object.
	 *
	 * @since 1.0.0
	 */
	const METHOD = 'method';

	/**
	 * The key to locate a context value as result running a callback function (e.g. a callable, a closure).
	 *
	 * @since 1.0.0
	 */
	const FUNC = 'func';

	/**
	 * The key to locate a context value as result of reading a global value.
	 *
	 * @since 1.0.0
	 */
	const GLOBAL_VAR = 'global_var';

	/**
	 * The key to locate a context value as result of an `apply_filters` call.
	 *
	 * @since 1.0.0
	 */
	const FILTER = 'filter';

	/**
	 * The key to locate a context value among the values parsed by `WP::parse_request`.
	 *
	 * @since 1.0.0
	 */
	const WP_PARSED = 'wp_parsed';

	/**
	 * The key to locate a context value among the values in the query mached by `WP::parse_request`.
	 *
	 * @since 1.0.0
	 */
	const WP_MATCHED_QUERY = 'wp_matched_query';

	/**
	 * The key to indicate a location should be read by applying a callback to the value of another context location.
	 *
	 * @since 1.0.0
	 */
	const LOCATION_FUNC = 'location_func';

	/*
	 *
	 * An array defining the properties the context will be able to read and (dangerously) write.
	 *
	 * This is the configuration that should be modified to add/remove/modify values and locations
	 * provided by the global context.
	 * Each entry has the shape [ <key> => [ 'read' => <read_locations>, 'write' => <write_locations> ] ].
	 * The key is used to identify the property that will be accessible with the `get` and
	 * 'dangerously_set_global_context' method, e.g. `$context->get( 'event_display', 'list' );`.
	 * The locations is a list of locations the context will search, top to bottom, left to right, to find a value that's
	 * not empty or the default one, here's a list of supported lookup locations:
	 *
	 * request_var - look into $_GET, $_POST, $_PUT, $_DELETE, $_REQUEST.
	 * query_var - get the value from the main WP_Query object query vars.
	 * query_prop - get the value from a property of the main WP_Query object.
	 * option - get the value from a database option.
	 * transient - get the value from a transient.
	 * constant - get the value from a constant, can also be a class constant with <class>::<const>.
	 * global_var - get the value from a global variable
	 * static_prop - get the value from a class static property, format: `array( $class, $prop )`.
	 * prop - get the value from a App::container() container binding, format `array( $binding, $prop )`.
	 * static_method - get the value from a class static method.
	 * method - get the value calling a method on a App::container() container binding.
	 * func - get the value from a function or a closure.
	 * filter - get the value by applying a filter.
	 * location_func - get the value by applying a callback to the value of a location.
	 *
	 * For each location additional arguments can be specified:
	 * orm_arg - if `false` then the location will never produce an ORM argument, if provided the ORM arg produced bye the
	 * location will have this name.
	 * orm_transform - if provided the value of the location will be obtained by passing it as an argument to a callable.
	 *
	 * As the Context locations increase in number it would be impractical to define them inline here.
	 * The locations will be loaded by the `Context::populate_locations` method from the `Context/locations.php`
	 * file.
	 *
	 * @var array
	 */
	protected static $locations = [];

	/**
	 * A utility static property keeping track of write locations that
	 * will be defined as associative arrays.
	 *
	 * @var array
	 */
	protected static $associative_locations = [
		self::TRANSIENT,
		self::METHOD,
		self::STATIC_METHOD,
		self::PROP,
		self::STATIC_PROP,
	];

	/**
	 * Whether the static dynamic locations were set or not.
	 *
	 * @var bool
	 */
	protected static $did_populate_locations = false;

	/**
	 * A list of override locations to read and write from.
	 *
	 * This list has the same format and options as the static `$locations` property
	 * but allows a context instance to override, or add, read and write locations.
	 *
	 * @var array
	 */
	protected $override_locations = [];

	/**
	 * Whether the context of the current HTTP request is an AJAX one or not.
	 *
	 * @var bool
	 */
	protected $doing_ajax;

	/**
	 * Whether the context of the current HTTP request is a Cron one or not.
	 *
	 * @var bool
	 */
	protected $doing_cron;

	/**
	 * A request-based array cache to store the values fetched by the context.
	 *
	 * @var array
	 */
	protected $request_cache = [];

	/**
	 * Whether this context should use the default locations or not.
	 * This flag property is set to `false` when a context is obtained using
	 * the `set_locations` method; it will otherwise be set to `true`.
	 *
	 * @var bool
	 */
	protected $use_default_locations = true;

	/**
	 * An instance of the post state handler.
	 *
	 * @since 1.0.0
	 *
	 * @var Post_Request_Type
	 */
	protected Post_Request_Type $post_state;

	/**
	 * Context constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Post_Request_Type|null $post_state An instance of the post state handler.
	 */
	public function __construct( Post_Request_Type $post_state = null ) {
		$this->post_state = $post_state ?: App::get( Post_Request_Type::class );
	}

	/**
	 * Whether we are currently creating a new post, a post of post type(s) or not.
	 *
	 * @since 1.0.0
	 *
	 * @param null $post_type The optional post type to check.
	 *
	 * @return bool Whether we are currently creating a new post, a post of post type(s) or not.
	 */
	public function is_new_post( $post_type = null ) {
		return $this->post_state->is_new_post( $post_type );
	}

	/**
	 * Whether we are currently editing a post(s), post type(s) or not.
	 *
	 * @since 1.0.0
	 *
	 * @param null|array|string|int $post_or_type A post ID, post type, an array of post types or post IDs, `null`
	 *                                            to just make sure we are currently editing a post.
	 *
	 * @return bool
	 */
	public function is_editing_post( $post_or_type = null ): bool {
		return $this->post_state->is_editing_post( $post_or_type );
	}

	/**
	 * Helper function to indicate whether the current execution context is AJAX.
	 *
	 * This method exists to allow us test code that behaves differently depending on the execution
	 * context.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean
	 */
	public function doing_ajax() {
		return function_exists( 'wp_doing_ajax' )
			? wp_doing_ajax()
			: defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	/**
	 * Checks whether the context of the current HTTP request is a Cron one or not.
	 *
	 * @since 1.0.0
	 * @since 1.0.0
	 *
	 * @return bool Whether the context of the current HTTP request is a Cron one or not.
	 */
	public function doing_cron() {
		return function_exists( 'wp_doing_cron' )
			? wp_doing_cron()
			: defined( 'DOING_CRON' ) && DOING_CRON;
	}

	/**
	 * Gets a value reading it from the location(s) defined in the `Context::$props
	 *
	 * @since 1.0.0
	 *
	 * @param string     $key     The key of the variable to fetch.
	 * @param mixed|null $default The default value to return if not found.
	 * @param bool $force Whether to force the re-fetch of the value from the context or
	 *                    not; defaults to `false`.
	 *
	 * @return mixed The value from the first location that can provide it or the default
	 *               value if not found.
	 */
	public function get( $key, $default = null, $force = false ) {
		/**
		 * Filters the value of a context variable skipping all of its logic.
		 *
		 * @since 1.0.0
		 *
		 * @param  mixed   $value    The value for the key before it's fetched from the context.
		 * @param  string  $key      The key of the value to fetch from the context.
		 * @param  mixed   $default  The default value that should be returned if the value is
		 *                           not set in the context.
		 * @param  bool    $force    Whether to force the re-fetch of the value from the context or
		 *                           not; defaults to `false`.
		 */
		$value = apply_filters( "sswps/context_pre_{$key}", null, $key, $default, $force );
		if ( null !== $value ) {
			return $value;
		}

		$value     = $default;
		$locations = $this->get_locations();
		$found     = false;

		if ( ! $force && isset( $this->request_cache[ $key ] ) ) {
			$value = $this->request_cache[ $key ];
		} elseif ( ! empty( $locations[ $key ]['read'] ) ) {
			foreach ( $locations[ $key ]['read'] as $location => $keys ) {
				$the_value = $this->$location( (array) $keys, $default );

				if ( $default !== $the_value && static::NOT_FOUND !== $the_value ) {
					$found = true;
					$value = $the_value;
					break;
				}
			}
		}

		/**
		 * Filters the value fetched from the context for a key.
		 *
		 * Useful for testing and local override.
		 *
		 * @since 1.0.0
		 *
		 * @param  mixed  $value  The value as fetched from the context.
		 */
		$value = apply_filters( "sswps/context_{$key}", $value );

		// Only cache if the value was found.
		if ( $found ) {
			$this->request_cache[ $key ] = $value;
		}

		return $value;
	}

	/**
	 * Alters the context.
	 *
	 * Due to its immutable nature setting values on the context will NOT modify the
	 * context but return a modified clone.
	 * If you need to modify the global context update the location(s) it should read from
	 * and call the `refresh` method.
	 * Example: `$widget_context = sswps_context()->alter( $widget_args );`.
	 *
	 * @since 1.0.0
	 *
	 * @param array $values An associative array of key-value pairs to modify the context.
	 *
	 * @return Context A clone, with modified, values, of the context the method was called on.
	 */
	public function alter( array $values  ) {
		$clone = clone $this;

		$clone->request_cache = array_merge( $clone->request_cache, $values );

		return $clone;
	}

	/**
	 * Clears the context cache forcing a re-fetch of the variables from the context.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key An optional specific key to refresh, if passed only this key
	 *                    will be refreshed.
	 */
	public function refresh( $key = null ) {
		if ( null !== $key ) {
			unset( $this->request_cache[ $key ] );
		} else {
			$this->request_cache = [];
		}
	}

	/**
	 * Returns the read and write locations set on the context.
	 *
	 * @since 1.0.0
	 *
	 * @return array An array of read and write location in the shape of the `Context::$locations` one,
	 *               `[ <location> => [ 'read' => <read_locations>, 'write' => <write_locations> ] ]`.
	 */
	public function get_locations() {
		$this->populate_locations();

		$locations = $this->use_default_locations
			? array_merge( self::$locations, $this->override_locations )
			: $this->override_locations;

		if ( $this->use_default_locations ) {
			/**
			 * Filters the locations registered in the Context.
			 *
			 * @since 1.0.0
			 *
			 * @param array $locations            An array of read and write location in the shape of the `Context::$locations` one,
			 *                                   `[ <location> => [ 'read' => <read_locations>, 'write' => <write_locations> ] ]`.
			 * @param Context $context Current instance of the context.
			 */
			$locations = apply_filters( 'sswps/context_locations', $locations, $this );
		}

		return $locations;
	}

	/**
	 * Reads the value from one or more $_REQUEST vars.
	 *
	 * @since 1.0.0
	 *
	 * @param array $request_vars The list of request vars to lookup, in order.
	 * @param mixed $default The default value to return.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	protected function request_var( array $request_vars, $default ) {
		$value = $default;

		foreach ( $request_vars as $request_var ) {
			$the_value = sswps_get_request_var( $request_var, self::NOT_FOUND );
			if ( $the_value !== self::NOT_FOUND ) {
				$value = $the_value;
				break;
			}
		}

		return $value;
	}

	/**
	 * Reads the value from one or more global WP_Query object query variables.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query_vars The list of query vars to look up, in order.
	 * @param mixed $default The default value to return.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	protected function query_var( array $query_vars, $default ) {
		$value = $default;

		global $wp_query;

		if ( ! $wp_query instanceof \WP_Query ) {
			return $value;
		}

		foreach ( $query_vars as $query_var ) {
			$the_value = $wp_query->get( $query_var, self::NOT_FOUND );
			if ( $the_value !== self::NOT_FOUND ) {
				$value = $the_value;
				break;
			}
		}

		return $value;
	}

	/**
	 * Reads the value from one or more global WP_Query object properties.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query_props The list of properties to look up, in order.
	 * @param mixed $default The default value to return.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	protected function query_prop( array $query_props, $default ) {
		$value = $default;

		global $wp_query;
		foreach ( $query_props as $query_prop ) {
			$the_value = isset( $wp_query->{$query_prop} ) ? $wp_query->{$query_prop} : self::NOT_FOUND;
			if ( $the_value !== self::NOT_FOUND ) {
				$value = $the_value;
				break;
			}
		}

		return $value;
	}

	/**
	 * Reads the value from one or more options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options The list of options to lookup, in order.
	 * @param mixed $default The default value to return.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	protected function option( array $options, $default ) {
		$value = $default;

		foreach ( $options as $option_name ) {
			$the_value = get_option( $option_name, self::NOT_FOUND );
			if ( $the_value !== self::NOT_FOUND ) {
				$value = $the_value;
				break;
			}
		}

		return $value;
	}

	/**
	 * Reads the value from one or more transients.
	 *
	 * @since 1.0.0
	 *
	 * @param array $transients The list of transients to lookup, in order.
	 * @param mixed $default The default value to return.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	protected function transient( array $transients, $default ) {
		$value = $default;

		foreach ( $transients as $transient ) {
			$the_value = get_transient( $transient );
			if ( false !== $the_value ) {
				$value = $the_value;
				/*
				 * This will fail when the value is actually `false`.
				 */
				break;
			}
		}

		return $value;
	}

	/**
	 * Reads the value from one or more constants.
	 *
	 * @since 1.0.0
	 *
	 * @param array $constants The list of constants to lookup, in order.
	 * @param mixed $default The default value to return.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	protected function constant( array $constants, $default ) {
		$value = $default;

		foreach ( $constants as $constant ) {
			$the_value = defined( $constant ) ? constant( $constant ) : self::NOT_FOUND;
			if ( $the_value !== self::NOT_FOUND ) {
				$value = $the_value;
				break;
			}
		}

		return $value;
	}

	/**
	 * Reads the value from one or more global variable.
	 *
	 * @since 1.0.0
	 *
	 * @param array $global_vars The list of global variables to look up, in order.
	 * @param mixed $default The default value to return.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	protected function global_var( array $global_vars, $default ) {
		$value = $default;

		foreach ( $global_vars as $var ) {
			$the_value = isset( $GLOBALS[ $var ] ) ? $GLOBALS[ $var ] : self::NOT_FOUND;
			if ( $the_value !== self::NOT_FOUND ) {
				$value = $the_value;
				break;
			}
		}

		return $value;
	}

	/**
	 * Reads the value from one or more class static properties.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes_and_props An associative array in the shape [ <class> => <prop> ].
	 * @param mixed $default The default value to return.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	protected function static_prop( array $classes_and_props, $default ) {
		$value = $default;

		foreach ( $classes_and_props as $class => $prop ) {
			if ( class_exists( $class ) ) {
				// PHP 5.2 compat, on PHP 5.3+ $class::$$prop
				$vars      = get_class_vars( $class );
				$the_value = isset( $vars[ $prop ] ) ? $vars[ $prop ] : self::NOT_FOUND;

				if ( $the_value !== self::NOT_FOUND ) {
					$value = $the_value;
					break;
				}
			}
		}

		return $value;
	}

	/**
	 * Reads the value from one or more properties of implementations bound in the `App::container()` container.
	 *
	 * @since 1.0.0
	 *
	 * @param array $bindings_and_props An associative array in the shape [ <binding> => <prop> ].
	 * @param mixed $default The default value to return.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	protected function prop( array $bindings_and_props, $default ) {
		$value = $default;

		foreach ( $bindings_and_props as $binding => $prop ) {
			$the_value = App::has( $binding ) && property_exists( App::get( $binding ), $prop )
				? App::get( $binding )->{$prop}
				: self::NOT_FOUND;

			if ( $the_value !== self::NOT_FOUND ) {
				$value = $the_value;
				break;
			}
		}

		return $value;
	}

	/**
	 * Reads the values from one or more static class methods.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes_and_methods An associative array in the shape [ <class> => <method> ].
	 * @param mixed $default The default value to return.
	 *
	 * @return mixed The first value that's not equal to the default one, the default value
	 *               otherwise.
	 */
	protected function static_method( array $classes_and_methods, $default ) {
		$value = $default;

		foreach ( $classes_and_methods as $class => $method ) {
			$the_value = class_exists( $class ) && method_exists( $class, $method )
				? call_user_func( [ $class, $method ] )
				: self::NOT_FOUND;

			if ( $the_value !== self::NOT_FOUND ) {
				$value = $the_value;
				break;
			}
		}

		return $value;
	}

	/**
	 * Reads the value from one or more methods called on implementations bound in the `App::container()` container.
	 *
	 * @since 1.0.0
	 *
	 * @param array $bindings_and_methods An associative array in the shape [ <binding> => <method> ].
	 * @param mixed $default              The default value to return.
	 *
	 * @return mixed The first value that's not equal to the default one, the default value
	 *               otherwise.
	 */
	protected function method( array $bindings_and_methods, $default ) {
		$value     = $default;
		$the_value = self::NOT_FOUND;

		foreach ( $bindings_and_methods as $binding => $method ) {
			if ( App::has( $binding ) ) {
				$implementation = App::get( $binding );
				if ( method_exists( $implementation, $method ) ) {
					$the_value = $implementation->$method();
				}
			}

			if ( $the_value !== self::NOT_FOUND ) {
				$value = $the_value;
				break;
			}
		}

		return $value;
	}

	/**
	 * Reads the value from one or more functions until one returns a value that's not the default one.
	 *
	 * @since 1.0.0
	 *
	 * @param array $functions An array of functions to call, in order.
	 * @param mixed $default The default value to return.
	 *
	 * @return mixed The first value that's not equal to the default one, the default value
	 *               otherwise.
	 */
	protected function func( array $functions, $default ) {
		$value     = $default;
		$the_value = self::NOT_FOUND;

		foreach ( $functions as $function ) {
			if ( is_callable( $function ) || function_exists( (string) $function ) ) {
				$the_value = $function();
			}

			if ( $the_value !== self::NOT_FOUND ) {
				$value = $the_value;
				break;
			}
		}

		return $value;
	}

	/**
	 * Modifies the global context using the defined write locations to persist the altered values.
	 *
	 * Please keep in mind this will set the the global context for the whole request and, when the
	 * write location is an option, to the database.
	 * With great power comes great responsibility: think a lot before using this.
	 *
	 * @param array|null $fields    An optional whitelist or blacklist of fields to write
	 *                              depending on the value of the `$whitelist` parameter;
	 *                              defaults to writing all available fields.
	 * @param bool       $whitelist Whether the list of fields provided in the `$fields`
	 *                              parameter should be treated as a whitelist (`true`) or
	 *                              blacklist (`false`).
	 *
	 * @since 1.0.0
	 */
	public function dangerously_set_global_context( array $fields = null, $whitelist = true ) {
		$locations = $this->get_locations();

		if ( null !== $fields ) {
			$locations = $whitelist
				? array_intersect_key( $locations, array_combine( $fields, $fields ) )
				: array_diff_key( $locations, array_combine( $fields, $fields ) );
		}

		/**
		 * Here we intersect with the request cache to only write values we've actually read
		 * or modified. If none of the two happened then there's no need to write anything.
		 */
		foreach ( array_intersect_key( $this->request_cache, $locations ) as $key => $value ) {
			if ( ! isset( $locations[ $key ]['write'] ) ) {
				continue;
			}

			foreach ( (array) $locations[ $key ]['write'] as $location => $targets ) {
				$targets    = (array) $targets;
				$write_func = 'write_' . $location;

				foreach ( $targets as $arg_1 => $arg_2 ) {
					if ( self::FUNC === $location && is_array( $arg_2 ) && is_callable( $arg_2 ) ) {
						// Handles write functions specified as an array.
						$location_args = [ $arg_2 ];
					} else {
						$location_args = in_array( $location, self::$associative_locations, true )
							? [ $arg_1, $arg_2 ]
							: (array) $arg_2;
					}

					$args = array_merge( $location_args, [ $value ] );

					call_user_func_array( [ $this, $write_func ], $args );
				}
			}
		}
	}

	/**
	 * Writes an altered context value to a request var.
	 *
	 * @since 1.0.0
	 *
	 * @param string $request_var The request var to write.
	 * @param mixed  $value       The value to set on the request var.
	 */
	protected function write_request_var( $request_var, $value ) {
		$_REQUEST[ $request_var ] = $value;
		$_GET[ $request_var ] = $value;
		$_POST[ $request_var ] = $value;
	}

	/**
	 * Writes an altered context value to a global WP_Query object properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query_prop The global WP_Query object property to write.
	 * @param mixed  $value      The value to set on the query property.
	 */
	protected function write_query_prop( $query_prop, $value ) {
		global $wp_query;

		if ( ! $wp_query instanceof WP_Query ) {
			return;
		}

		$wp_query->{$query_prop} = $value;
	}

	/**
	 * Writes an altered context value to a global WP_Query object query var.
	 *
	 * @since 1.0.0
	 *
	 * @param string $query_var The global WP_Query query var to write.
	 * @param mixed  $value     The value to set on the query var.
	 */
	protected function write_query_var( $query_var, $value ) {
		global $wp_query;

		if ( ! $wp_query instanceof WP_Query ) {
			return;
		}

		$wp_query->set( $query_var, $value );
	}

	/**
	 * Writes an altered context value to an option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option_name The option to write.
	 * @param mixed  $value       The value to set on the option.
	 */
	protected function write_option( $option_name, $value ) {
		update_option( $option_name, $value );
	}

	/**
	 * Writes an altered context value to a transient.
	 *
	 * @since 1.0.0
	 *
	 * @param string $transient  The transient to write.
	 * @param int    $expiration The transient expiration time, in seconds.
	 * @param mixed  $value      The value to set on the transient.
	 */
	protected function write_transient( $transient, $expiration, $value ) {
		set_transient( $transient, $value, $expiration );
	}

	/**
	 * Writes an altered context value to a constant.
	 *
	 * @since 1.0.0
	 *
	 * @param string $constant The constant to define.
	 * @param mixed  $value    The value to set on the constant.
	 */
	protected function write_constant( $constant, $value ) {
		if ( defined( $constant ) ) {
			return;
		}
		define( $constant, $value );
	}

	/**
	 * Writes an altered context value to a global var.
	 *
	 * @since 1.0.0
	 *
	 * @param string $global_var The global var to set.
	 * @param mixed  $value      The value to set on the global_var.
	 */
	protected function write_global_var( $global_var, $value ) {
		$GLOBALS[ $global_var ] = $value;
	}

	/**
	 * Writes an altered context value setting a public static property on a class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The class to set the static public property on.
	 * @param string $prop  The static public property to set.
	 * @param mixed  $value The value to set on the property.
	 */
	protected function write_static_prop( $class, $prop, $value ) {
		if ( ! ( class_exists( $class ) && property_exists( $class, $prop ) ) ) {
			return;
		}

		$class::$$prop = $value;
	}

	/**
	 * Writes an altered context value setting a public property on a `App::get()` binding.
	 *
	 * @since 1.0.0
	 *
	 * @param string $binding The container binding to set the public property on.
	 * @param string $prop    The public property to set.
	 * @param mixed  $value   The value to set on the property.
	 */
	protected function write_prop( $binding, $prop, $value ) {
		if ( ! App::has( $binding ) ) {
			return;
		}

		$implementation = App::get( $binding );

		if ( ! property_exists( $implementation, $prop ) ) {
			return;
		}

		$implementation->{$prop} = $value;
	}

	/**
	 * Writes an altered context value calling a public static method on a class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class  The class to call the public static method on.
	 * @param string $method The static method to call.
	 * @param mixed  $value  The value to pass to the public static method.
	 */
	protected function write_static_method( $class, $method, $value ) {
		if ( ! class_exists( $class ) ) {
			return;
		}
		call_user_func( [ $class, $method ], $value );
	}

	/**
	 * Writes an altered context value calling a public method on a `App:get()` binding.
	 *
	 * @since 1.0.0
	 *
	 * @param string $binding The `App::get()` container binding to call the public method on.
	 * @param string $method  The method to call.
	 * @param mixed  $value   The value to pass to the public method.
	 */
	protected function write_method( $binding, $method, $value ) {
		if ( ! App::has( $binding ) ) {
			return;
		}
		call_user_func( [ App::get( $binding ), $method ], $value );
	}

	/**
	 * Writes an altered context value calling a function or closure.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $func  function, closure or callable to call.
	 * @param mixed    $value The value to pass to the callable.
	 */
	protected function write_func( $func, $value ) {
		if ( ! is_callable( $func ) ) {
			return;
		}
		call_user_func( $func, $value );
	}

	/**
	 * Adds/replaces read and write locations to a context.
	 *
	 * Locations are merged with an `array_merge` call. To refine the locations get them first with the
	 * `get_locations` method.
	 *
	 * @since 1.0.0
	 *
	 * @param array $locations An array of read and write locations to add to the context.
	 *                         The array should have the same shape as the static `$locations`
	 *                         one: `[ <location> => [ 'read' => <read_locations>, 'write' => <write_locations> ] ]`.
	 *
	 *
	 * @return Context A clone of the current context with the additional read and
	 *                         write locations added.
	 */
	public function add_locations( array $locations ) {
		$clone                     = clone $this;
		$clone->override_locations = array_merge( $clone->override_locations, $locations );

		return $clone;
	}

	/**
	 * Sets, replacing them, the locations used by this context.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param array $locations An array of locations to replace the current ones.
	 * @param bool  $use_default_locations Whether the context should use the default
	 *                                     locations defined in the static `$locations`
	 *                                     property or not.
	 *
	 * @return Context A clone of the current context with modified locations.
	 */
	public function set_locations( array $locations, $use_default_locations = true ) {
		$clone                        = clone $this;
		$clone->override_locations    = $locations;
		$clone->use_default_locations = (bool) $use_default_locations;

		return $clone;
	}

	/**
	 * Returns an array representation of the context.
	 *
	 * @since 1.0.0
	 *
	 * @return array An associative array of the context keys and values.
	 */
	public function to_array(  ) {
		$locations = array_keys( array_merge( $this->get_locations(), $this->request_cache ) );
		$dump      = [];

		foreach ( $locations as $location ) {
			$the_value = $this->get( $location, self::NOT_FOUND );

			if ( self::NOT_FOUND === $the_value ) {
				continue;
			}

			$dump[ $location ] = $the_value;
		}

		return $dump;
	}

	/**
	 * Returns the current context state in a format suitable to hydrate a Redux-like
	 * store on the front-end.
	 *
	 * This method is a filtered wrapper around the the `Context::to_array` method to allow the
	 * customization of the format when producing a store-compatible state.
	 *
	 * @param array|null $fields    An optional whitelist or blacklist of fields to include
	 *                              depending on the value of the `$whitelist` parameter;
	 *                              defaults to returning all available fields.
	 * @param bool       $whitelist Whether the list of fields provided in the `$fields`
	 *                              parameter should be treated as a whitelist (`true`) or
	 *                              blacklist (`false`).
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_state( array $fields = null, $whitelist = true ) {
		$state             = $this->to_array();
		$is_global_context = sswps_context() === $this;

		if ( null !== $fields ) {
			$state = $whitelist
				? array_intersect_key( $state, array_combine( $fields, $fields ) )
				: array_diff_key( $state, array_combine( $fields, $fields ) );
		}

		/**
		 * Filters the Redux store compatible state produced from the current context.
		 *
		 * @since 1.0.0
		 *
		 * @param array $state             The Redux store compatible state produced from the current context.
		 * @param bool  $is_global_context Whether the context producing the state is the global one
		 *                                 or a modified clone of it.
		 * @param Context $context         The context object producing the state.
		 */
		$state = apply_filters( 'sswps/context_state', $state, $is_global_context, $this );

		if ( $is_global_context ) {
			/**
			 * Filters the Redux store compatible state produced from the global context.
			 *
			 * While the `sswps/context_state` filter will apply to all contexts producing a
			 * state this filter will only apply to the global context.
			 *
			 * @since 1.0.0
			 *
			 * @param array $state The Redux store compatible state produced from the global context.
			 * @param Context $context The global context object producing the state.
			 */
			$state = apply_filters( 'sswps/global_context_state', $state, $this );
		}

		return $state;
	}

	/**
	 * Sets some locations that can only be set at runtime.
	 *
	 * Using a flag locations are added only once per request.
	 *
	 * @since 1.0.0
	 */
	protected function populate_locations() {
		if ( static::$did_populate_locations ) {
			return;
		}

		// To improve the class readability, and as a small optimization, locations are loaded from a file.
		static::$locations = require __DIR__ . '/locations.php';

		/**
		 * Filters the locations registered in the Context.
		 *
		 * @since 1.0.0
		 *
		 * @param  array  $locations  An array of locations registered on the Context object.
		 * @param Context $context   The Context object.
		 */
		static::$locations = apply_filters( 'sswps/context_locations', static::$locations, $this );

		static::$did_populate_locations = true;
	}

	/**
	 * Just dont...
	 * Unless you very specifically know what you are doing **DO NOT USE THIS METHOD**!
	 *
	 * Please keep in mind this will set force the context to repopulate all locations for the whole request, expensive
	 * and very dangerous overall since it could affect all this things we hold dear in the request.
	 *
	 * With great power comes great responsibility: think a lot before using this.
	 *
	 * @since 1.0.0
	 */
	public function dangerously_repopulate_locations() {
		static::$did_populate_locations = false;
		$this->populate_locations();
	}

	/**
	 * Reads (gets) the value applying one or more filters.
	 *
	 * @since 1.0.0
	 *
	 * @param array $filters The list of filters to apply, in order.
	 * @param mixed $default The default value to return.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	public function filter( array $filters, $default ) {
		foreach ( $filters as $filter ) {
			$the_value = apply_filters( $filter, $default );
			if ( $the_value !== $default ) {
				return $the_value;
			}
		}

		return $default;
	}

	/**
	 * Reads (gets) the value reading it from a query var parsed from the global `$wp` object.
	 *
	 * @since 1.0.0
	 *
	 * @param array $vars    The list of variables to read, in order.
	 * @param mixed $default The default value to return if no variable was parsed.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	public function wp_parsed( array $vars, $default ) {
		/** @var WP $wp */
		global $wp;

		if ( ! $wp instanceof WP || empty($wp->query_vars) ) {
			return $default;
		}

		return Arr::get_first_set( (array) $wp->query_vars, $vars, $default );
	}

	/**
	 * Reads (gets) the value reading it from a query var parsed from the query matched by the global `$wp` object.
	 *
	 * @since 1.0.0
	 *
	 * @param array $vars    The list of variables to read, in order.
	 * @param mixed $default The default value to return if no variable was parsed.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	public function wp_matched_query( array $vars, $default ) {
		/** @var WP $wp */
		global $wp;

		if ( ! $wp instanceof WP || empty( $wp->matched_query ) ) {
			return $default;
		}

		parse_str( $wp->matched_query, $query_vars );

		return Arr::get_first_set( (array) $query_vars, $vars, $default );
	}

	/**
	 * Maps an input array to the corresponding read locations.
	 *
	 * The resulting array can be used as input for the `alter_values` method.
	 * The main use of this method is to leverage the Context knowledge of the read locations, and their types, to
	 * "translate" an array of values to an array of valid read sources. As an example this is useful to "translate"
	 * the locations to an array of query vars:
	 *      $input = [ 'event_display' => 'some-view', 'event_date' => '2018-01-03' ];
	 *      $query_args = sswps_context()->map_to_read( $input, Context::REQUEST_VAR );
	 *      $url = add_query_arg( $query_args, home_url() );
	 *
	 * @since 1.0.0
	 *
	 * @param array             $input       An associative array of values in the shape `[ <location> => <value> ]`;
	 *                                       where `location` is the name of the location registered in the Context
	 *                                       locations.
	 * @param string|array|null $types       A white-list of read location types to include in the mapped output;
	 *                                       `null`
	 *                                       means all types are allowed.
	 * @param bool              $passthru    Whether to pass unknown locations in the output or not; if `false` then
	 *                                       any input key that's not a context location will not appear in the output;
	 *                                       defaults to `false` to remove unknown locations from the output.
	 *
	 * @return array An associative array in the shape `[ <read_location> => <input_value> ]`. Since some read
	 *              locations could have multiple sources the number of elements in this array will likely NOT be the
	 *              same as the number of elements in the input array. When a read location as more than 1 source then
	 *              the value will be duplicated, in the output array, to both sources.
	 */
	public function map_to_read( array $input, $types = null, $passthru = false ) {
		$mapped    = [];
		$processed = [];
		$types     = null !== $types ? (array) $types : null;

		$locations = $this->get_locations();

		// Take the current read locations
		foreach ( $locations as $key => $location ) {
			if ( ! isset( $location['read'], $input[ $key ] ) ) {
				continue;
			}

			$processed[] = $key;

			foreach ( $location['read'] as $type => $name ) {
				if ( null !== $types && ! in_array( $type, $types, true ) ) {
					continue;
				}

				foreach ( (array) $name as $destination ) {
					$mapped[ $destination ] = $input[ $key ];
				}
			}
		}

		if ( $passthru ) {
			$mapped = array_merge(
				$mapped,
				array_diff_key( $input, array_keys( $locations ), array_combine( $processed, $processed ) )
			);
		}

		ksort( $mapped );

		return $mapped;
	}

	/**
	 * Translates sub-locations to their respective location key.
	 *
	 * This method leverages the inherent knowledge of aliases stored in the Context locations to "translate" a
	 * sub-location to its location key.
	 * E.g. assume the `car` location is `read` from the [ 'carriage', 'vehicle', 'transport_mean' ] query var; calling
	 * `$context->populate_aliases( [ 'vehicle' => 'hyunday' ], 'read', Context::QUERY_VAR )` would yield
	 * `[ 'car' => 'hyunday' ]`.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $values    An associative array of value to use as "masters" to populate the aliases.
	 * @param string $type      The type of Context location to use, e.g. `Context::QUERY_VAR`.
	 * @param string $direction The direction to use for the location, one of `read` or `write`.
	 *
	 * @return array The original array, merged with the populated values.
	 */
	public function translate_sub_locations( array $values, $type, $direction = 'read' ) {
		if ( ! in_array( $direction, [ 'read', 'write' ], true ) ) {
			throw new \InvalidArgumentException(
				"Direction must be one of `read` or `write`; `{$direction}` is not valid."
			);
		}

		$filled             = [];
		$locations          = $this->get_locations();
		$matching_locations = array_filter( $locations, static function ( $location ) use ( $type, $direction ) {
			return isset( $location[ $direction ][ $type ] );
		} );

		foreach ( $matching_locations as $key => $location ) {
			$entry = (array)$location[ $direction ][ $type ];
			$found = array_intersect( array_keys( $values ), array_merge( $entry, [ $key ] ) );
			if ( $found ) {
				$filled[ $key ] = $values[ reset( $found ) ];
			}
		}

		return $filled;
	}

	/**
	 * Convenience method to get and check if a location has a truthy value or not.
	 *
	 * @since 1.0.0
	 *
	 * @param string $flag_key The location to check.
	 * @param bool   $default  The default value to return if the location is not set.
	 *
	 * @return bool Whether the location has a truthy value or not.
	 */
	public function is( $flag_key, $default = false ) {
		$val = $this->get( $flag_key, $default );

		return ! empty( $val ) || sswps_is_truthy( $val );
	}

	/**
	 * Reads the value from one callback, passing it the value of another Context location.
	 *
	 * @since 1.0.0
	 *
	 * @param array $location_and_callback An array of two elements: the location key and the callback to call on the
	 *                                     location value. The callback will receive the location value as argument.
	 *
	 * @return mixed The return value of the callback, called on the location value.
	 */
	public function location_func( array $location_and_callback ) {
		[ $location, $callback ] = $location_and_callback;

		return $callback( $this->get( $location ) );
	}

	/**
	 * Checks whether the current request is a REST API one or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the current request is a REST API one or not.
	 */
	public function doing_rest() {
		return defined( 'REST_REQUEST' ) && REST_REQUEST;
	}

	/**
	 * Reads the value from one or more global WP_Query object methods.
	 *
	 * @since 1.0.0
	 *
	 * @param array $methods The list of query methods to call, in order.
	 * @param mixed $default The default value to return if no method was defined on the global `WP_Query` object.
	 *
	 * @return mixed The first valid value found or the default value.
	 */
	public function query_method( $methods, $default ) {
		global $wp_query;
		$found = $default;

		foreach ( $methods as $method ) {
			$this_value = $wp_query instanceof WP_Query && method_exists( $wp_query, $method )
				? call_user_func( [ $wp_query, $method ] )
				: static::NOT_FOUND;

			if ( static::NOT_FOUND !== $this_value ) {
				return $this_value;
			}
		}

		return $found;
	}

	/**
	 * Whether the current request is for a PHP-rendered initial state or not.
	 *
	 * This method is a shortcut to make sure we're not doing an AJAX, REST or Cron request.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the current request is for a PHP-rendered initial state or not.
	 */
	public function doing_php_initial_state() {
		return ! $this->doing_rest() && ! $this->doing_ajax() && ! $this->doing_cron();
	}

	/**
	 * Returns the first key, if there are many, that will be used to read a location.
	 *
	 * The type ar
	 *
	 * @since 1.0.0
	 *
	 * @param string      $location The location to get the read key for.
	 * @param string|null $type     The type of read location to return the key for; default to `static::REQUEST_VAR`.
	 *
	 * @return string Either the first key for the type of read location, or the input location if not found.
	 */
	public function get_read_key_for( $location, $type = null ) {
		$type = $type ?: static::REQUEST_VAR;
		$locations = $this->get_locations();
		if ( isset( $locations[ $location ]['read'][ $type ] ) ) {
			$keys = (array) $locations[ $location ]['read'][ $type ];
			return reset( $keys );
		}

		return $location;
	}

	/**
	 * Safely set the value of a group of locations.
	 *
	 * This method can only augment the context, without altering it; it can only add new values.
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $values_or_key The values to set, if not already set or the key of the value to set, requires
	 *                             the `$value` to be passed.
	 * @param mixed|null $value    The value to set for the key, this parameter will be ignored if the `$values_or_key`
	 *                             parameter is not a string.
	 */
	public function safe_set( $values_or_key, $value = null ) {
		$values = func_num_args() === 2
			? [ $values_or_key => $value ]
			: $values_or_key;

		foreach ( $values as $key => $val ) {
			if ( static::NOT_FOUND !== $this->get( $key, static::NOT_FOUND ) ) {
				continue;
			}
			$this->request_cache[ $key ] = $val;
		}
	}

	/**
	 * Whether the current request is one to edit a list of the specified post types or not.
	 *
	 * The admin edit screen for a post type is the one that lists all the posts of that typ,
	 * it has the URL `/wp-admin/edit.php?post_type=<post_type>`.
	 *
	 * @since 1.0.0
	 * @since 1.0.0
	 *
	 * @param string|array<string> $post_type The post type or post types to check.
	 *
	 * @return bool Whether the current request is one to edit a list of the specified post types or not.
	 */
	public function is_editing_posts_list( $post_type ): bool {
		return $this->post_state->is_editing_post_list( $post_type );
	}

	/**
	 * Whether the current request is one to quick edit a single post of the specified post type or not.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array<string> $post_type The post type or post types to check.
	 *
	 * @return bool Whether the current request is one to quick edit a single post of the specified post type or not.
	 */
	public function is_inline_editing_post( $post_type ): bool {
		return $this->post_state->is_inline_editing_post( $post_type );
	}
}