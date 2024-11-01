<?php
namespace SimpleSecureWP\SimpleSecureStripe\Dependencies;

use SimpleSecureWP\SimpleSecureStripe\Notices\Notice;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

/**
 * Tracks which plugins are registered, activated, or requirements satisfied.
 */
class Dependency {

	/**
	 * A multidimensional array of active plugins in the following format.
	 *
	 * [
	 *  'class'   => 'main class name',
	 *  'version' => 'version num', (optional)
	 *  'path'    => 'Path to the main plugin/bootstrap file' (optional)
	 * ]
	 *
	 * @since 1.0.0
	 */
	protected array $active_plugins = [];

	/**
	 * A multidimensional array of active plugins in the following format.
	 *
	 * [
	 *  'class'             => 'main class name',
	 *  'path'              => 'Path to the main plugin/bootstrap file'
	 *  'version'           => 'version num', (optional)
	 *  'dependencies'      => 'A multidimensional of dependencies' (optional)
	 * ]
	 *
	 * @since 1.0.0
	 */
	protected array $registered_plugins = [];

	/**
	 * An array of class Notice\Plugin_Download per plugin.
	 *
	 * @since 1.0.0
	 */
	protected array $admin_messages = [];

	/**
	 * Adds a plugin to the active list
	 *
	 * @since 1.0.0
	 *
	 * @param string      $main_class   Main/base class for this plugin.
	 * @param null|string $version      Version number of plugin.
	 * @param null|string $path         Path to the main plugin/bootstrap file.
	 * @param array       $dependencies An array of dependencies for a plugin.
	 */
	public function add_registered_plugin( $main_class, $version = null, $path = null, $dependencies = [] ) {
		$plugin = [
			'class'        => $main_class,
			'version'      => $version,
			'path'         => $path,
			'dependencies' => $dependencies,
		];

		$this->registered_plugins[ $main_class ] = $plugin;

		if ( $path ) {
			$this->admin_messages[ $main_class ] = new Notice\Plugin_Download( $path );
		}
	}

	/**
	 * Retrieves registered plugin array.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_registered_plugins() {
		return $this->registered_plugins;
	}

	/**
	 * Adds a plugin to the active list.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_class Main/base class for this plugin.
	 * @param string $version    Version number of plugin.
	 * @param string $path       Path to the main plugin/bootstrap file.
	 */
	public function add_active_plugin( $main_class, $version = null, $path = null ) {
		$plugin = [
			'class'   => $main_class,
			'version' => $version,
			'path'    => $path,
		];

		$this->active_plugins[ $main_class ] = $plugin;
	}

	/**
	 * Retrieves active plugin array
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_active_plugins() {
		return $this->active_plugins;
	}

	/**
	 * Searches the plugin list for key/value pair and return the full details for that plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_key The array key this value will appear in.
	 * @param string $search_val The value itself.
	 *
	 * @return array|null
	 */
	public function get_plugin_by_key( $search_key, $search_val ) {
		foreach ( $this->get_active_plugins() as $plugin ) {
			if ( isset( $plugin[ $search_key ] ) && $plugin[ $search_key ] === $search_val ) {
				return $plugin;
			}
		}

		return null;
	}

	/**
	 * Retrieves the plugins details by class name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_class Main/base class for this plugin.
	 *
	 * @return array|null
	 */
	public function get_plugin_by_class( $main_class ) {
		return $this->get_plugin_by_key( 'class', $main_class );
	}

	/**
	 * Retrieves the version of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_class Main/base class for this plugin.
	 *
	 * @return string|null Version.
	 */
	public function get_plugin_version( $main_class ) {
		$plugin = $this->get_plugin_by_class( $main_class );

		return ( isset( $plugin['version'] ) ? $plugin['version'] : null );
	}

	/**
	 * Checks if the plugin is active.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_class Main/base class for this plugin.
	 *
	 * @return bool
	 */
	public function is_plugin_active( $main_class ) {
		return ( $this->get_plugin_by_class( $main_class ) !== null );
	}

	/**
	 * Searches the registered plugin list for key/value pair and return the full details for that plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_key The array key this value will appear in.
	 * @param string $search_val The value itself.
	 *
	 * @return array|null
	 */
	public function get_registered_plugin_by_key( $search_key, $search_val ) {
		foreach ( $this->get_registered_plugins() as $plugin ) {
			if ( isset( $plugin[ $search_key ] ) && $plugin[ $search_key ] === $search_val ) {
				return $plugin;
			}
		}

		return null;
	}

	/**
	 * Retrieves the registered plugins details by class name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_class Main/base class for this plugin.
	 *
	 * @return array|null
	 */
	public function get_registered_plugin_by_class( $main_class ) {
		return $this->get_registered_plugin_by_key( 'class', $main_class );
	}

	/**
	 * Retrieves the version of the registered plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_class Main/base class for this plugin.
	 *
	 * @return string|null Version.
	 */
	public function get_registered_plugin_version( $main_class ) {
		$plugin = $this->get_registered_plugin_by_class( $main_class );

		return ( isset( $plugin['version'] ) ? $plugin['version'] : null );
	}

	/**
	 * Checks if the plugin is active.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_class Main/base class for this plugin.
	 *
	 * @return bool
	 */
	public function is_plugin_registered( $main_class ) {
		return ( $this->get_registered_plugin_by_class( $main_class ) !== null );
	}


	/**
	 * Checks if a plugin is active and has the specified version.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_class Main/base class for this plugin.
	 * @param string $version Version to do a compare against.
	 * @param string $compare Version compare string, defaults to >=.
	 *
	 * @return bool
	 */
	public function is_plugin_version( $main_class, $version, $compare = '>=' ) {
		//active plugin check to see if the correct version is active
		if ( ! $this->is_plugin_active( $main_class ) ) {
			return false;
		} elseif ( version_compare( $this->get_plugin_version( $main_class ), $version, $compare ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Is the plugin registered with at least the minimum version.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_class Main/base class for this plugin.
	 * @param string $version Version to do a compare against.
	 * @param string $compare Version compare string, defaults to >=.
	 *
	 * @return bool
	 */
	public function is_plugin_version_registered( $main_class, $version, $compare = '>=' ) {
		//registered plugin check if addon as it tests if it might load
		if ( ! $this->is_plugin_registered( $main_class ) ) {
			return false;
		} elseif ( version_compare( $this->get_registered_plugin_version( $main_class ), $version, $compare ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if each plugin is active and exceeds the specified version number.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugins_required Each item is a 'class_name' => 'min version' pair. Min ver can be null.
	 *
	 * @return bool
	 */
	public function has_requisite_plugins( $plugins_required = [] ) {
		foreach ( $plugins_required as $class => $version ) {
			// Return false if the plugin is not set or is a lesser version
			if ( ! $this->is_plugin_active( $class ) ) {
				return false;
			}

			if ( null !== $version && ! $this->is_plugin_version( $class, $version ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieves Registered Plugin by Class Name from Array.
	 *
	 * @since 1.0.0
	 *
	 * @return array|boolean
	 */
	public function get_registered_plugin( $class ) {
		$plugins = $this->registered_plugins;

		return isset( $plugins[ $class ] ) ? $plugins[ $class ] : false;
	}

	/**
	 * Gets all dependencies or single class requirements if parent, co, add does not exist use array as is if they
	 * do exist check each one in turn.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin       An array of data for given registered plugin.
	 * @param array $dependencies An array of dependencies for a plugin.
	 * @param bool  $addon        Indicates if the plugin is an add-on for the base plugin.
	 *
	 * @return true|int  The number of failed dependency checks; `true` or `0` to indicate no checks failed.
	 */
	public function has_valid_dependencies( $plugin, $dependencies = [], $addon = false ) {
		if ( empty( $dependencies ) ) {
			return true;
		}

		$failed_dependency = 0;

		$plugins = new Plugins();

		foreach ( $dependencies as $class => $version ) {
			// Handle third party plugins.
			if ( ! empty( $version['callback'] ) && is_callable( $version['callback'] ) ) {
				$result = call_user_func( $version['callback'] );

				if ( $result ) {
					continue;
				}

				$version = isset( $version['version'] ) ? $version['version'] : null;
			}

			// if no class for add-on
			$checked_plugin = $this->get_registered_plugin( $class );
			if ( $addon && empty( $checked_plugin ) ) {
				continue;
			}

			$is_registered = $this->is_plugin_version_registered( $class, $version );
			if ( ! empty( $is_registered ) ) {
				continue;
			}

			$dependent_plugin = $plugins->get_plugin_by_class( $class );

			$this->admin_messages[ $plugin['class'] ]->add_required_plugin(
				$dependent_plugin['short_name'],
				$dependent_plugin['thickbox_url'],
				$is_registered,
				$version,
				$addon
			);
			$failed_dependency++;
		}

		return $failed_dependency;
	}

	/**
	 * Register a Plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file_path    Full file path to the base plugin file.
	 * @param string $main_class   The Main/base class for this plugin.
	 * @param string $version      The plugin version.
	 * @param array  $classes_req  Any Main class files/plugins required for this to run.
	 * @param array  $dependencies an array of dependencies to check.
	 */
	public function register_plugin( $file_path, $main_class, $version, $classes_req = [], $dependencies = [] ) {
		/**
		 * Filters the version string for a plugin.
		 *
		 * @since 4.9
		 *
		 * @param string $version The plugin version number, e.g. "4.0.4".
		 * @param array $dependencies An array of dependencies for the plugins. These can include parent, add-on and other dependencies.
		 * @param string $file_path The absolute path to the plugin main file.
		 * @param array $classes_req Any Main class files/plugins required for this to run.
		 */
		$version = apply_filters( "sswps/register_{$main_class}_plugin_version", $version, $dependencies, $file_path, $classes_req );
		/**
		 * Filters the dependencies array for a plugin.
		 *
		 * @since 4.9
		 *
		 * @param array $dependencies An array of dependencies for the plugins. These can include parent, add-on and other dependencies.
		 * @param string $version The plugin version number, e.g. "4.0.4".
		 * @param string $file_path The absolute path to the plugin main file.
		 * @param array $classes_req Any Main class files/plugins required for this to run.
		 */
		$dependencies = apply_filters( "sswps/register_{$main_class}_plugin_dependencies", $dependencies, $version, $file_path, $classes_req );

		//add all plugins to registered_plugins
		$this->add_registered_plugin( $main_class, $version, $file_path, $dependencies );

		// Checks to see if the plugins are active for extensions
		if ( ! empty( $classes_req ) && ! $this->has_requisite_plugins( $classes_req ) ) {
			$plugins = new Plugins();
			foreach ( $classes_req as $class => $plugin_version ) {
				$plugin         = $plugins->get_plugin_by_class( $class );

				$is_active      = $this->is_plugin_version( $class, $plugin_version );

				$this->admin_messages[ $main_class ]->add_required_plugin(
					$plugin['short_name'],
					$plugin['thickbox_url'],
					$is_active,
					$plugin_version,
					false
				);
			}
		}

		// only set plugin to Active when registering
		if ( Plugin::class === $main_class ) {
			$this->add_active_plugin( $main_class, $version, $file_path );
		}

	}

	/**
	 * Checks if this plugin has permission to run, if not it notifies the admin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_class   The Main/base class for this plugin.
	 *
	 * @return bool Indicates if plugin should continue initialization.
	 */
	public function check_plugin( $main_class ) {

		$parent_dependencies_met      = true;
		$co_dependencies_met          = true;
		$addon_dependencies_met       = true;
		$third_party_dependencies_met = true;

		// Check if plugin is registered, if not return false.
		$plugin = $this->get_registered_plugin( $main_class );
		if ( empty( $plugin ) ) {
			return false;
		}

		// Check parent dependencies in add-on.
		if ( ! empty( $plugin['dependencies']['parent-dependencies'] ) ) {
			$parent_dependencies_met = $this->has_valid_dependencies( $plugin, $plugin['dependencies']['parent-dependencies'] ) === 0;
		}
		// Check co-dependencies in add-on.
		if ( ! empty( $plugin['dependencies']['co-dependencies'] ) ) {
			$co_dependencies_met = $this->has_valid_dependencies( $plugin, $plugin['dependencies']['co-dependencies'] ) === 0;
		}

		// Check add-on dependencies from parent.
		$addon_dependencies_met = (bool) $this->check_addon_dependencies( $main_class );

		if ( ! empty( $plugin['dependencies']['third-party'] ) ) {
			$third_party_dependencies_met = $this->has_valid_dependencies( $plugin, $plugin['dependencies']['third-party'] ) === 0;
		}

		// If good then we set as active plugin and continue to load.
		if ( ! $parent_dependencies_met || ! $co_dependencies_met || ! $addon_dependencies_met || ! $third_party_dependencies_met ) {
			return false;
		}

		$this->add_active_plugin( $main_class, $plugin['version'], $plugin['path'] );

		return true;
	}

	/**
	 * Check an add-on dependencies for its parent.
	 *
	 * @since 1.0.0
	 *
	 * @param string $main_class A string of the main class for the plugin being checked.
	 *
	 * @return bool  Returns false if any dependency is invalid.
	 */
	protected function check_addon_dependencies( $main_class ) {
		$is_valid = true;

		foreach ( $this->registered_plugins as $registered ) {
			if ( empty( $registered['dependencies']['addon-dependencies'][ $main_class ] ) ) {
				continue;
			}

			$dependencies = [ $main_class => $registered['dependencies']['addon-dependencies'][ $main_class ] ];
			$check        = $this->has_valid_dependencies( $registered, $dependencies, true );

			if ( ! sswps_is_truthy( $check ) ) {
				$is_valid = false;
			}
		}

		return $is_valid;
	}
}