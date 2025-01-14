<?php
namespace SimpleSecureWP\SimpleSecureStripe\Assets;

use InvalidArgumentException;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

class Asset {
	/**
	 * @var array The asset action.
	 */
	protected $action = [];

	/**
	 * The asset style data.
	 *
	 * @see: wp_style_add_data()
	 *
	 * @var array
	 */
	protected $add_style_data = [];

	/**
	 * The callable to execute after enqueuing.
	 *
	 * @var mixed
	 */
	protected $after_enqueue;

	/**
	 * The asset conditional callable.
	 *
	 * @var mixed
	 */
	protected $condition;

	/**
	 * The asset dependencies.
	 *
	 * @var array
	 */
	protected array $dependencies = [];

	/**
	 * The asset file path.
	 *
	 * @var ?string
	 */
	protected $file;

	/**
	 * The asset groups.
	 *
	 * This is used for organizing assets into groups.
	 *
	 * @var array
	 */
	protected array $groups = [];

	/**
	 * Should the asset be loaded in the footer?
	 *
	 * @var bool
	 */
	protected $in_footer = true;

	/**
	 * Should the asset be marked as async?
	 *
	 * @var bool
	 */
	protected $is_async = false;

	/**
	 * Should the asset be marked as deferred?
	 *
	 * @var bool
	 */
	protected $is_deferred = false;

	/**
	 * Is the asset enqueued?
	 *
	 * @var bool
	 */
	protected $is_enqueued = false;

	/**
	 * Is the asset a module?
	 *
	 * @var bool
	 */
	protected $is_module = false;

	/**
	 * Is the asset printed?
	 *
	 * @var bool
	 */
	protected $is_printed = false;

	/**
	 * Is the asset registered?
	 *
	 * @var bool
	 */
	protected $is_registered = false;

	/**
	 * Is the asset a vendor asset?
	 *
	 * @var bool
	 */
	protected $is_vendor = false;

	/**
	 * The asset wp_localize_script objects for this asset.
	 *
	 * @var array
	 */
	protected array $wp_localize_script_objects = [];

	/**
	 * The asset file media setting.
	 *
	 * @var string
	 */
	protected $media = 'all';

	/**
	 * The asset file min url.
	 *
	 * @var ?string
	 */
	protected $min_url;

	/**
	 * The root plugin path for this asset.
	 *
	 * @var string
	 */
	protected string $plugin_path = '';

	/**
	 * Content or callable that should be printed after the asset.
	 *
	 * @var mixed
	 */
	protected $print_after;

	/**
	 * Content or callable that should be printed before the asset.
	 *
	 * @var mixed
	 */
	protected $print_before;

	/**
	 * The asset priority.
	 *
	 * @var int
	 */
	protected int $priority = 10;

	/**
	 * Whether the asset should print rather than enqueue.
	 *
	 * @var bool
	 */
	protected $should_print = false;

	/**
	 * The asset slug.
	 *
	 * @var ?string
	 */
	protected $slug;

	/**
	 * The asset type.
	 *
	 * @var ?string
	 */
	protected $type;

	/**
	 * The asset file url.
	 *
	 * @var ?string
	 */
	protected $url;

	/**
	 * The asset version.
	 *
	 * @var ?string
	 */
	protected $version;

	/**
	 * Constructor.
	 *
	 * @param string $slug The asset slug.
	 * @param string $file The asset file path.
	 * @param string $version The asset version.
	 * @param string|null $plugin_path The path to the root of the plugin.
	 */
	public function __construct( string $slug, string $file, string $version = Plugin::VERSION, $plugin_path = null ) {
		$this->slug        = sanitize_key( $slug );
		$this->file        = $file;
		$this->version     = $version;
		$this->plugin_path = $plugin_path ?? App::get( Plugin::class )->plugin_path;

		if (
			strpos( $this->file, 'vendor/' ) !== false
			|| strpos( $this->file, 'node_modules/' ) !== false
		) {
			$this->is_vendor = true;
		}

		$this->infer_type();
	}

	/**
	 * @since 1.0.0
	 *
	 * @param string $dependency
	 *
	 * @return static
	 */
	public function add_dependency( string $dependency ) {
		if ( isset( $this->dependencies[ $dependency ] ) ) {
			return $this;
		}

		$this->dependencies[ $dependency ] = $dependency;

		return $this;
	}

	/**
	 * Adds data to be attached to the stylesheet.
	 *
	 * @see: wp_style_add_data()
	 *
	 * @since 1.0.0
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return static
	 */
	public function add_style_data( string $key, $value ) {
		$this->add_style_data[ $key ] = $value;
		return $this;
	}

	/**
	 * @since 1.0.0
	 *
	 * @param string $group
	 *
	 * @return static
	 */
	public function add_to_group( string $group ) {
		if ( isset( $this->groups[ $group ] ) ) {
			return $this;
		}

		$this->groups[ $group ] = $group;

		return $this;
	}

	/**
	 * Set a callable that should fire after enqueuing.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $callable A callable that executes after the asset is enqueued.
	 *
	 * @return static
	 */
	public function call_after_enqueue( $callable ) {
		if ( ! is_callable( $callable ) ) {
			throw new InvalidArgumentException( 'The argument must be a callable.' );
		}

		$this->after_enqueue = $callable;
		return $this;
	}

	/**
	 * Adds a wp_localize_script object to the asset.
	 *
	 * @since 1.0.0
	 *
	 * @param string $object_name JS object name.
	 * @param array  $data Data assigned to the JS object.
	 *
	 * @return static
	 */
	public function add_localize_script( string $object_name, array $data ) {
		$this->wp_localize_script_objects[ $object_name ] = $data;
		return $this;
	}

	/**
	 * Enqueue the asset.
	 *
	 * @since 1.0.0
	 */
	public function enqueue() {
		App::get( API::class )->register_in_wp( $this );
	}

	/**
	 * Get the asset action.
	 *
	 * @return array
	 */
	public function get_action() : array {
		return $this->action;
	}

	/**
	 * Get the asset after enqueue callable.
	 *
	 * @return mixed
	 */
	public function get_after_enqueue() {
		return $this->after_enqueue;
	}

	/**
	 * Get the asset condition callable.
	 *
	 * @return mixed
	 */
	public function get_condition() {
		return $this->condition;
	}

	/**
	 * Get the asset dependencies.
	 *
	 * @return array
	 */
	public function get_dependencies() : array {
		return $this->dependencies;
	}

	/**
	 * Get the asset file.
	 *
	 * @return string
	 */
	public function get_file() : string {
		return $this->file;
	}

	/**
	 * Get the asset groups.
	 *
	 * @return array
	 */
	public function get_groups() : array {
		return $this->groups;
	}

	/**
	 * Get the asset wp_localize_script_objects.
	 *
	 * @return array
	 */
	public function get_localize_scripts() : array {
		return $this->wp_localize_script_objects;
	}

	/**
	 * Get the asset media setting.
	 *
	 * @return string
	 */
	public function get_media() : string {
		return $this->media;
	}

	/**
	 * Get the asset min url.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_min_url() : string {
		$this->get_url();

		if ( $this->min_url === null ) {
			return $this->url;
		}

		return $this->min_url;
	}

	/**
	 * Get the print_after value.
	 *
	 * @return mixed
	 */
	public function get_print_after() {
		return $this->print_after;
	}

	/**
	 * Get the print_before value.
	 *
	 * @return mixed
	 */
	public function get_print_before() {
		return $this->print_before;
	}

	/**
	 * Get the asset action priority.
	 *
	 * @return int
	 */
	public function get_priority() : int {
		return $this->priority;
	}

	/**
	 * Get the asset slug.
	 *
	 * @return string
	 */
	public function get_slug() : string {
		return $this->slug;
	}

	/**
	 * Get the asset style data.
	 *
	 * @return array
	 */
	public function get_style_data() : array {
		return $this->add_style_data;
	}

	/**
	 * Get the asset type.
	 *
	 * @return string
	 */
	public function get_type() : string {
		return $this->type;
	}

	/**
	 * Get the asset url.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $use_min_if_available Use the minified version of the asset if available.
	 *
	 * @return string
	 */
	public function get_url( bool $use_min_if_available = true ) : string {
		if ( $this->url === null ) {
			if ( filter_var( $this->file, FILTER_VALIDATE_URL ) ) {
				$this->url = $this->file;
			} else {
				$this->url = sswps_asset_url( $this->file, $this->plugin_path, $this->is_vendor() ? '' : null );
			}
		}

		if ( $this->min_url === null ) {
			$this->min_url = static::maybe_get_min_file( $this->url );
		}

		if ( $use_min_if_available && $this->min_url ) {
			return $this->min_url;
		}

		return $this->url;
	}

	/**
	 * Get the asset version.
	 *
	 * @return string
	 */
	public function get_version() : string {
		return $this->version;
	}

	/**
	 * Sets the asset to be loaded in the footer.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function in_footer() {
		$this->in_footer = true;

		return $this;
	}

	/**
	 * Sets the asset to be loaded in the header.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function in_header() {
		$this->in_footer = false;

		return $this;
	}

	/**
	 * Set the asset type.
	 *
	 * @since 1.0.0
	 */
	protected function infer_type() {
		if ( substr( $this->file, -3, 3 ) === '.js' ) {
			$this->type = 'js';
		} elseif ( substr( $this->file, -4, 4 ) === '.css' ) {
			$this->type = 'css';
		}
	}

	/**
	 * Returns whether or not the asset is async.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_async() : bool {
		return $this->is_async;
	}

	/**
	 * Returns whether or not the asset is deferred.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_deferred() : bool {
		return $this->is_deferred;
	}

	/**
	 * Returns whether or not the asset is enqueued.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_enqueued() : bool {
		return $this->is_enqueued;
	}

	/**
	 * Returns whether or not the asset goes in the footer.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_in_footer() : bool {
		return $this->in_footer;
	}

	/**
	 * Returns whether or not the asset goes in the header.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_in_header() : bool {
		return ! $this->in_footer;
	}

	/**
	 * Returns whether or not the asset is a module.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_module() : bool {
		return $this->is_module;
	}

	/**
	 * Returns whether or not the asset is printed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_printed() : bool {
		return $this->is_printed;
	}

	/**
	 * Returns whether or not the asset is registered.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_registered() : bool {
		return $this->is_registered;
	}

	/**
	 * Returns whether or not the asset is a vendor asset.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_vendor() : bool {
		return $this->is_vendor;
	}

	/**
	 * Returns the path to a minified version of a js or css file, if it exists.
	 * If the file does not exist, returns false.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url The absolute URL to the un-minified file.
	 *
	 * @return string|false The url to the minified version or false, if file not found.
	 */
	public static function maybe_get_min_file( $url ) {
		static $wpmu_plugin_url;
		static $wp_plugin_url;
		static $wp_content_url;
		static $plugins_url;
		static $base_dirs;

		$urls = [];
		if ( ! isset( $wpmu_plugin_url ) ) {
			$wpmu_plugin_url = set_url_scheme( WPMU_PLUGIN_URL );
		}

		if ( ! isset( $wp_plugin_url ) ) {
			$wp_plugin_url = set_url_scheme( WP_PLUGIN_URL );
		}

		if ( ! isset( $wp_content_url ) ) {
			$wp_content_url = set_url_scheme( WP_CONTENT_URL );
		}

		if ( ! isset( $plugins_url ) ) {
			$plugins_url = plugins_url();
		}

		if ( ! isset( $base_dirs ) ) {
			$base_dirs[ WPMU_PLUGIN_DIR ] = wp_normalize_path( WPMU_PLUGIN_DIR );
			$base_dirs[ WP_PLUGIN_DIR ]   = wp_normalize_path( WP_PLUGIN_DIR );
			$base_dirs[ WP_CONTENT_DIR ]  = wp_normalize_path( WP_CONTENT_DIR );
		}

		if ( 0 === strpos( $url, $wpmu_plugin_url ) ) {
			// URL inside WPMU plugin dir.
			$base_dir = $base_dirs[ WPMU_PLUGIN_DIR ];
			$base_url = $wpmu_plugin_url;
		} elseif ( 0 === strpos( $url, $wp_plugin_url ) ) {
			// URL inside WP plugin dir.
			$base_dir = $base_dirs[ WP_PLUGIN_DIR ];
			$base_url = $wp_plugin_url;
		} elseif ( 0 === strpos( $url, $wp_content_url ) ) {
			// URL inside WP content dir.
			$base_dir = $base_dirs[ WP_CONTENT_DIR ];
			$base_url = $wp_content_url;
		} elseif ( 0 === strpos( $url, $plugins_url ) ) {
			$base_dir = $base_dirs[ WP_PLUGIN_DIR ];
			$base_url = $plugins_url;
		} else {
			// Resource needs to be inside wp-content or a plugins dir.
			return false;
		}

		$script_debug = defined( 'SCRIPT_DEBUG' ) && sswps_is_truthy( SCRIPT_DEBUG );

		// Strip the plugin URL and make this relative.
		$relative_location = str_replace( $base_url, '', $url );

		if ( $script_debug ) {
			// Add the actual url after having the min file added.
			$urls[] = $relative_location;
		}

		// If needed add the Min Files.
		if ( substr( $relative_location, -3, 3 ) === '.js' ) {
			$urls[] = preg_replace( '#(.*src/assets/js/).*/([a-zA-Z0-0\-\_\.]+.js)#', '$1dist/$2', $relative_location );
		} elseif ( substr( $relative_location, -4, 4 ) === '.css' ) {
			$urls[] = preg_replace( '#(.*src/assets/css/).*/([a-zA-Z0-0\-\_\.]+.css)#', '$1dist/$2', $relative_location );
		}

		if ( ! $script_debug ) {
			// Add the actual url after having the min file added.
			$urls[] = $relative_location;
		}

		// Check for all Urls added to the array.
		foreach ( $urls as $partial_path ) {
			$file_path = wp_normalize_path( $base_dir . $partial_path );
			$file_url  = $base_url . $partial_path;

			if ( file_exists( $file_path ) ) {
				return $file_url;
			}
		}

		// If we don't have any real file return false.
		return false;
	}

	/**
	 * Print the asset.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function print() {
		$this->should_print = true;
		return $this;
	}

	/**
	 * Set the print_after value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $thing A string or callable to print after the asset.
	 *
	 * @return static
	 */
	public function print_after( $thing ) {
		$this->print_after = $thing;

		return $this;
	}

	/**
	 * Set the print_before value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $thing A string or callable to print before the asset.
	 *
	 * @return static
	 */
	public function print_before( $thing ) {
		$this->print_before = $thing;

		return $this;
	}

	/**
	 * Registers an asset.
	 *
	 * @param string $slug The asset slug.
	 * @param string $file The asset file path.
	 * @param string $version The asset version.
	 * @param string|null $plugin_path The path to the root of the plugin.
	 */
	public static function register( string $slug, string $file, string $version = Plugin::VERSION, $plugin_path = null ) {
		return App::get( API::class )->register( new self( $slug, $file, $version, $plugin_path ) );
	}

	/**
	 * Set the asset action.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action WordPress action that this asset will be registered to.
	 *
	 * @return static
	 */
	public function set_action( string $action ) {
		$this->action[ $action ] = $action;

		return $this;
	}

	/**
	 * Set the asset as async.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function set_as_async() {
		$this->is_async = true;
		return $this;
	}

	/**
	 * Set the asset as deferred.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function set_as_deferred() {
		$this->is_deferred = true;
		return $this;
	}

	/**
	 * Set the asset enqueue status to true.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function set_as_enqueued() {
		$this->is_enqueued = true;
		return $this;
	}

	/**
	 * Set the asset as a module.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function set_as_module() {
		$this->is_module = true;
		return $this;
	}

	/**
	 * Set the asset as not async.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function set_as_not_async() {
		$this->is_async = false;
		return $this;
	}

	/**
	 * Set the asset as not deferred.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function set_as_not_deferred() {
		$this->is_deferred = false;
		return $this;
	}

	/**
	 * Set the asset print status to false.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function set_as_not_printed() {
		$this->is_printed = false;
		return $this;
	}

	/**
	 * Set the asset print status to true.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function set_as_printed() {
		$this->is_printed = true;
		return $this;
	}

	/**
	 * Set the asset registration status to true.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function set_as_registered() {
		$this->is_registered = true;
		return $this;
	}

	/**
	 * Set the asset enqueue status to false.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function set_as_unenqueued() {
		$this->is_enqueued = false;
		return $this;
	}

	/**
	 * Set the asset registration status to false.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	public function set_as_unregistered() {
		$this->is_registered = false;
		return $this;
	}

	/**
	 * Set the asset condition for inclusion.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $condition A callable that returns a boolean indicating if the asset should be enqueued.
	 *
	 * @return static
	 */
	public function set_condition( $condition ) {
		if ( ! is_callable( $condition ) ) {
			throw new InvalidArgumentException( 'The condition argument must be a callable.' );
		}

		$this->condition = $condition;
		return $this;
	}

	/**
	 * @since 1.0.0
	 *
	 * @param array $dependencies
	 *
	 * @return static
	 */
	public function set_dependencies( array $dependencies ) {
		$this->dependencies = [];

		foreach ( $dependencies as $dependency ) {
			$this->add_dependency( $dependency );
		}

		return $this;
	}

	/**
	 * Set the asset media.
	 *
	 * @since 1.0.0
	 *
	 * @param string $media Asset media setting.
	 *
	 * @return static
	 */
	public function set_media( string $media ) {
		$this->media = esc_attr( $media );
		return $this;
	}

	/**
	 * Set the asset priority.
	 *
	 * @since 1.0.0
	 *
	 * @param int $priority Asset priority.
	 *
	 * @return static
	 */
	public function set_priority( int $priority ) {
		$this->priority = absint( $priority );
		return $this;
	}

	/**
	 * Set the asset type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Asset type.
	 *
	 * @return static
	 */
	public function set_type( string $type ) {
		$this->type = $type;
		return $this;
	}

	/**
	 * Get whether or not the script should print.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function should_print() : bool {
		return $this->should_print;
	}

	/**
	 * @since 1.0.0
	 *
	 * @param string $group
	 *
	 * @return static
	 */
	public function remove_from_group( string $group ) {
		if ( ! isset( $this->groups[ $group ] ) ) {
			return $this;
		}

		unset( $this->groups[ $group ] );

		return $this;
	}
}