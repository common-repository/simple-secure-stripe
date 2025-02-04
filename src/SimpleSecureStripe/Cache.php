<?php
namespace SimpleSecureWP\SimpleSecureStripe;

use ArrayAccess;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\DB;

/**
 * Manage setting and expiring cached data
 *
 * Select actions can be used to force cached
 * data to expire. Implemented so far:
 *  - save_post
 *
 * When used in its ArrayAccess API the cache will provide non persistent storage.
 */
class Cache implements ArrayAccess {
	const SCHEDULED_EVENT_DELETE_TRANSIENT = 'sswps_schedule_transient_purge';

	const NO_EXPIRATION = 0;

	const NON_PERSISTENT = -1;

	/**
	 * @var array
	 */
	protected $non_persistent_keys = [];

	/**
	 * Bootstrap hook
	 *
	 * @since 1.0.0
	 */
	public function hook() {
		if ( ! wp_next_scheduled( self::SCHEDULED_EVENT_DELETE_TRANSIENT ) ) {
			wp_schedule_event( time(), 'twicedaily', self::SCHEDULED_EVENT_DELETE_TRANSIENT );
		}

		add_action( self::SCHEDULED_EVENT_DELETE_TRANSIENT, [ $this, 'delete_expired_transients' ] );

		add_action( 'shutdown', [ $this, 'maybe_delete_expired_transients' ] );
	}

	public static function setup() {
		wp_cache_add_non_persistent_groups( [ 'sswps-events-non-persistent' ] );
	}

	/**
	 * @since 1.0.0
	 *
	 * @param string       $id
	 * @param mixed        $value
	 * @param int          $expiration
	 * @param string|array $expiration_trigger
	 *
	 * @return bool
	 */
	public function set( $id, $value, $expiration = 0, $expiration_trigger = '' ) {
		$key = $this->get_id( $id, $expiration_trigger );

		/**
		 * Filters the expiration for cache objects to provide the ability
		 * to make non-persistent objects be treated as persistent.
		 *
		 * @since 1.0.0
		 *
		 * @param int          $expiration         Cache expiration time.
		 * @param string       $id                 Cache ID.
		 * @param mixed        $value              Cache value.
		 * @param string|array $expiration_trigger Action that triggers automatic expiration.
		 * @param string       $key                Unique cache key based on Cache ID and expiration trigger last run time.
		 */
		$expiration = apply_filters( 'sswps/cache_expiration', $expiration, $id, $value, $expiration_trigger, $key );

		if ( self::NON_PERSISTENT === $expiration ) {
			$group      = 'sswps-non-persistent';
			$expiration = 1;

			// Add so we know what group to use in the future.
			$this->non_persistent_keys[ $id ] = $id;
		} else {
			$group = 'sswps';
		}

		return wp_cache_set( $key, $value, $group, $expiration );
	}

	/**
	 * @param              $id
	 * @param              $value
	 * @param int          $expiration
	 * @param string|array $expiration_trigger
	 *
	 * @return bool
	 */
	public function set_transient( $id, $value, $expiration = 0, $expiration_trigger = '' ) {
		return set_transient( $this->get_id( $id, $expiration_trigger ), $value, $expiration );
	}

	/**
	 * Get cached data. Optionally set data if not previously set.
	 *
	 * Note: When a default value or callback is specified, this value gets set in the cache.
	 *
	 * @param string       $id                 The key for the cached value.
	 * @param string|array $expiration_trigger Optional. Hook to trigger cache invalidation.
	 * @param mixed        $default            Optional. A default value or callback that returns a default value.
	 * @param int          $expiration         Optional. When the default value expires, if it gets set.
	 * @param mixed        $args               Optional. Args passed to callback.
	 *
	 * @return mixed
	 */
	public function get( $id, $expiration_trigger = '', $default = false, $expiration = 0, $args = [] ) {
		$group   = isset( $this->non_persistent_keys[ $id ] ) ? 'sswps-non-persistent' : 'sswps';
		$value   = wp_cache_get( $this->get_id( $id, $expiration_trigger ), $group );

		// Value found.
		if ( false !== $value ) {
			return $value;
		}

		if ( is_callable( $default ) ) {
			// A callback has been specified.
			$value = $default( ...$args );
		} else {
			// Default is a value.
			$value = $default;
		}

		// No need to set a cache value to false since non-existent values return false.
		if ( false !== $value ) {
			$this->set( $id, $value, $expiration, $expiration_trigger );
		}

		return $value;
	}

	/**
	 * @param string       $id
	 * @param string|array $expiration_trigger
	 *
	 * @return mixed
	 */
	public function get_transient( $id, $expiration_trigger = '' ) {
		return get_transient( $this->get_id( $id, $expiration_trigger ) );
	}

	/**
	 * @param string       $id
	 * @param string|array $expiration_trigger
	 *
	 * @return bool
	 */
	public function delete( $id, $expiration_trigger = '' ) {
		$group   = isset( $this->non_persistent_keys[ $id ] ) ? 'sswps-non-persistent' : 'sswps';

		// Delete from non-persistent keys list.
		if ( 'sswps-non-persistent' === $group ) {
			unset( $this->non_persistent_keys[ $id ] );
		}

		return wp_cache_delete( $this->get_id( $id, $expiration_trigger ), $group );
	}

	/**
	 * @param string       $id
	 * @param string|array $expiration_trigger
	 *
	 * @return bool
	 */
	public function delete_transient( $id, $expiration_trigger = '' ) {
		return delete_transient( $this->get_id( $id, $expiration_trigger ) );
	}

	/**
	 * Purge all expired sswps_ transients.
	 *
	 * This uses a modification of the the query from https://core.trac.wordpress.org/ticket/20316
	 *
	 * @since 1.0.0
	 *
	 * @return void Just execute the database SQL no return required.
	 */
	public function delete_expired_transients() {
		if ( App::get_var( 'has_deleted_expired_transients', false ) ) {
			return;
		}

		global $wpdb;

		$time = time();

		$sql = "
			DELETE
				a,
				b
			FROM
				{$wpdb->options} a
				INNER JOIN {$wpdb->options} b
					ON b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
					AND b.option_value < {$time}
			WHERE
				a.option_name LIKE '\_transient\_sswps\_%'
				AND a.option_name NOT LIKE '\_transient\_timeout\_sswps\_%'
		";

		/**
		 * Allow third party filtering of the SQL used for deleting expired transients.
		 *
		 * @since 1.0.0
		 *
		 * @param string $sql   The SQL we execute to delete all the expired transients.
		 * @param int    $time  Time we are using to determine what is expired.
		 */
		$sql = apply_filters( 'sswps/cache_delete_expired_transients_sql', $sql, $time );

		if ( empty( $sql ) ) {
			return;
		}

		DB::query( $sql );

		// Set the variable to prevent this call from running twice.
		App::set_var( 'has_deleted_expired_transients', true );
	}

	/**
	 * Flag if we should delete
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $value If we should delete transients or not on shutdown.
	 *
	 * @return void No return for setting the flag.
	 */
	public function flag_required_delete_transients( $value = true ) {
		App::set_var( 'should_delete_expired_transients', $value );
	}

	/**
	 * Runs on hook `shutdown` and will delete transients on the end of the request.
	 *
	 * @since 1.0.0
	 *
	 * @return void No return for action hook method.
	 */
	public function maybe_delete_expired_transients() {
		if ( ! App::get_var( 'should_delete_expired_transients', false ) ) {
			return;
		}

		$this->delete_expired_transients();
	}

	/**
	 * @param string       $key
	 * @param string|array $expiration_trigger
	 *
	 * @return string
	 */
	public function get_id( $key, $expiration_trigger = '' ) {
		$triggers = [];

		if ( is_array( $expiration_trigger ) ) {
			$triggers = $expiration_trigger;
		} elseif ( 'sswps-non-persistent' !== $expiration_trigger && 'sswps' !== $expiration_trigger ) {
			$triggers = array_filter( explode( '|', $expiration_trigger ) );
		}

		$last = 0;
		foreach ( $triggers as $trigger ) {
			// Bail on empty trigger otherwise it creates a `sswps_last_` opt on the DB.
			if ( empty( $trigger ) ) {
				continue;
			}

			$occurrence = $this->get_last_occurrence( $trigger );

			if ( $occurrence > $last ) {
				$last = $occurrence;
			}
		}

		$last = empty( $last ) ? '' : $last;
		$id   = $key . $last;
		if ( strlen( $id ) > 80 ) {
			$id = 'sswps_' . md5( $id );
		}

		return $id;
	}

	/**
	 * Returns the time of an action last occurrence.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action The action to return the time for.
	 *
	 * @return float The time (microtime) an action last occurred, or the current microtime if it never occurred.
	 */
	public function get_last_occurrence( $action ) {
		static $cache_var_name = __METHOD__;

		$cache_last_actions = App::get_var( $cache_var_name, [] );

		if ( isset( $cache_last_actions[ $action ] ) ) {
			return $cache_last_actions[ $action ];
		}

		$last_action = (float) get_option( 'sswps_last_' . $action, null );

		if ( ! $last_action ) {
			$last_action = microtime( true );
			$this->set_last_occurrence( $action, $last_action );
		}

		$cache_last_actions[ $action ] = (float) $last_action;

		App::set_var( $cache_var_name, $cache_last_actions );

		return $cache_last_actions[ $action ];
	}

	/**
	 * Sets the time (microtime) for an action last occurrence.
	 *
	 * @since 1.0.0
	 *
	 * @param string    $action    The action to record the last occurrence of.
	 * @param int|float $timestamp The timestamp to assign to the action last occurrence or the current time (microtime).
	 *
	 * @return boolean IF we were able to set the last occurrence or not.
	 */
	public function set_last_occurrence( $action, $timestamp = 0 ) {
		if ( empty( $timestamp ) ) {
			$timestamp = microtime( true );
		}
		$updated = update_option( 'sswps_last_' . $action, (float) $timestamp );

		// For performance reasons we will only expire cache once per request, when needed.
		if ( $updated ) {
			$this->flag_required_delete_transients( true );
		}

		return $updated;
	}

	/**
	 * Builds a key from an array of components and an optional prefix.
	 *
	 * @param mixed  $components Either a single component of the key or an array of key components.
	 * @param string $prefix
	 * @param bool   $sort       Whether component arrays should be sorted or not to generate the key; defaults to
	 *                           `true`.
	 *
	 * @return string The resulting key.
	 */
	public function make_key( $components, $prefix = '', $sort = true ) {
		$key        = '';
		$components = is_array( $components ) ? $components : [ $components ];
		foreach ( $components as $component ) {
			if ( $sort && is_array( $component ) ) {
				$is_associative = count( array_filter( array_keys( $component ), 'is_numeric' ) ) < count( array_keys( $component ) );
				if ( $is_associative ) {
					ksort( $component );
				} else {
					sort( $component );
				}
			}
			$key .= maybe_serialize( $component );
		}

		return $this->get_id( $prefix . md5( $key ) );
	}

	/**
	 * Whether a offset exists.
	 *
	 * @since 1.0.0
	 * @since 1.0.0
	 *            if expiration had passed but was cached recently. Will now consider null not set.
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @return boolean Whether the offset exists in the cache.
	 *@link  http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ): bool {
		$value = $this->get( $offset );

		return $value !== false && $value !== null;
	}

	/**
	 * Offset to retrieve.
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @return mixed Can return all value types.
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->get( $offset );
	}

	/**
	 * Offset to set.
	 *
	 * @since 1.0.0
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value  The value to set.
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ): void {
		$this->set( $offset, $value, self::NON_PERSISTENT );
	}

	/**
	 * Offset to unset.
	 *
	 * @since 1.0.0
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ): void {
		$this->delete( $offset );
	}

	/**
	 * Removes a group of the cache, for now only `non_persistent` is supported.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function reset( $group = 'non_persistent' ) {
		if ( 'non_persistent' !== $group ) {
			return false;
		}
		$this->non_persistent_keys = [];
		return true;
	}
}