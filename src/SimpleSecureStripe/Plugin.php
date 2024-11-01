<?php
namespace SimpleSecureWP\SimpleSecureStripe;

use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\Config as DBConfig;
use SimpleSecureWP\SimpleSecureStripe\StellarWP\DB\DB;
use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;

/**
 * Singleton class that handles plugin functionality like class loading.
 *
 * @since   1.0.0
 * @author Simple & Secure WP
 * @package Stripe/Classes
 *
 */
class Plugin extends Abstract_Controller {
	/**
	 * @since 1.0.0
	 */
	const FILE = SIMPLESECUREWP_STRIPE_FILE;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin version.
	 */
	const VERSION = '1.0.0';

	/**
	 * @since 1.0.0
	 *
	 * @var ?Context\Context Plugin Context.
	 */
	public $plugin_context;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin Directory.
	 */
	public string $plugin_dir;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin path.
	 */
	public string $plugin_path;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin URL.
	 */
	public string $plugin_url;

	/**
	 * @var string Text domain.
	 */
	public static string $text_domain = 'simple-secure-stripe';

	/**
	 * @inheritDoc
	 */
	public function register() {
		// Set up the plugin provider properties.
		$this->plugin_path = trailingslashit( dirname( static::FILE ) );
		$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url  = plugins_url( $this->plugin_dir, $this->plugin_path );

		define( 'SIMPLESECUREWP_STRIPE_FILE_PATH', plugin_dir_path( SIMPLESECUREWP_STRIPE_FILE ) );
		define( 'SIMPLESECUREWP_STRIPE_ASSETS', plugin_dir_url( SIMPLESECUREWP_STRIPE_FILE ) . 'src/assets/' );
		define( 'SIMPLESECUREWP_STRIPE_BASENAME', plugin_basename( SIMPLESECUREWP_STRIPE_FILE ) );

		DBConfig::setHookPrefix( 'sswps' );
		DB::init();

		/**
		 * Fire after the plugin has been bootstrapped.
		 *
		 * @since 1.0.0
		 */
		do_action( 'sswps/before_bindings' );

		$this->bootstrap();

		if ( ! App::get( Dependencies\Dependency::class )->check_plugin( static::class ) ) {
			/**
			 * Fire after the plugin has been bootstrapped.
			 *
			 * @since 1.0.0
			 */
			do_action( 'sswps/invalid_dependencies' );

			// If the plugin dependencies are not met, bail and stop here.
			return;
		}

		$this->bind_implementations();
		$this->hooks();

		/**
		 * Fire after the plugin has been bootstrapped.
		 *
		 * @since 1.0.0
		 */
		do_action( 'sswps/loaded' );
	}

	/**
	 * Set up core application hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		/**
		 * Actions
		 */
		add_action( 'woocommerce_payment_token_deleted', 'sswps_woocommerce_payment_token_deleted', 10, 2 );
		add_action( 'woocommerce_order_status_cancelled', 'sswps_order_cancelled', 10, 2 );
		add_action( 'woocommerce_order_status_completed', 'sswps_order_status_completed', 10, 2 );
		add_action( 'woocommerce_order_status_processing', 'sswps_order_status_completed', 10, 2 );
		add_action( 'sswps/remove_order_locks', 'sswps_remove_order_locks' );
		add_action( 'sswps/retry_source_chargeable', 'sswps_retry_source_chargeable' );
		add_action( 'woocommerce_init', [ $this, 'bind_woocommerce_dependencies' ] );
		add_action( 'init', [ Update::class, 'update' ] );
		add_action( 'admin_init', [ Install::class, 'initialize' ] );

		/**
		 * * Webhook Actions ***
		 */
		add_action( 'sswps/webhook_source_chargeable', 'sswps_process_source_chargeable', 10, 2 );
		add_action( 'sswps/webhook_charge_succeeded', 'sswps_process_charge_succeeded', 10, 2 );
		add_action( 'sswps/webhook_charge_failed', 'sswps_process_charge_failed', 10, 2 );
		add_action( 'sswps/webhook_charge_pending', 'sswps_process_charge_pending', 10, 1 );
		add_action( 'sswps/webhook_payment_intent_succeeded', 'sswps_process_payment_intent_succeeded', 10, 2 );
		add_action( 'sswps/webhook_payment_intent_requires_action', 'sswps_process_requires_action', 10, 1 );
		add_action( 'sswps/webhook_charge_refunded', 'sswps_process_create_refund' );
		add_action( 'sswps/webhook_charge_dispute_created', 'sswps_charge_dispute_created', 10, 1 );
		add_action( 'sswps/webhook_charge_dispute_closed', 'sswps_charge_dispute_closed', 10, 1 );
		add_action( 'sswps/webhook_review_opened', 'sswps_review_opened', 10, 1 );
		add_action( 'sswps/webhook_review_closed', 'sswps_review_closed', 10, 1 );

		/**
		 * Field_Manager
		 */
		add_action( 'woocommerce_checkout_before_customer_details', [ Field_Manager::class, 'output_banner_checkout_fields' ] );
		add_action( 'woocommerce_before_add_to_cart_form', [ Field_Manager::class, 'before_add_to_cart' ] );
		add_action( 'init', [ Field_Manager::class, 'init_action' ] );
		add_action( 'woocommerce_review_order_after_order_total', [ Field_Manager::class, 'output_checkout_fields' ] );
		add_action( 'before_woocommerce_add_payment_method', [ Field_Manager::class, 'add_payment_method_fields' ] );
		add_action( 'woocommerce_widget_shopping_cart_buttons', [ Field_Manager::class, 'mini_cart_buttons' ], 5 );

		/**
		 * Redirect_Handler
		 */
		add_action( 'wp', [ Redirect_Handler::class, 'local_payment_redirect' ] );
		add_action( 'get_header', [ Redirect_Handler::class, 'maybe_restore_cart' ], 100 );

		/**
		 * Filters
		 */
		add_filter( 'sswps/api_options', 'sswps_api_options' );
		add_filter( 'woocommerce_payment_gateways', 'sswps_payment_gateways' );
		add_filter( 'woocommerce_available_payment_gateways', 'sswps_available_payment_gateways' );
		add_action( 'woocommerce_process_shop_subscription_meta', 'sswps_process_shop_subscription_meta', 10, 2 );
		add_filter( 'woocommerce_payment_complete_order_status', 'sswps_payment_complete_order_status', 10, 3 );
		add_filter( 'woocommerce_get_customer_payment_tokens', 'sswps_get_customer_payment_tokens', 10, 3 );
		add_filter( 'woocommerce_credit_card_type_labels', 'sswps_credit_card_labels' );
		add_filter( 'plugin_action_links_' . SIMPLESECUREWP_STRIPE_BASENAME, [ Install::class, 'plugin_action_links' ] );

		register_activation_hook( SIMPLESECUREWP_STRIPE_BASENAME, [ Install::class, 'install' ] );
	}

	/**
	 * Return the url for the plugin assets.
	 *
	 * @since 1.0.0
	 *
	 * @param string $uri
	 *
	 * @return string
	 */
	public function assets_url( string $uri = '' ) : string {
		$url = SIMPLESECUREWP_STRIPE_ASSETS . $uri;
		if ( ! preg_match( '/(\.js)|(\.css)|(\.svg)|(\.png)/', $uri ) ) {
			return trailingslashit( $url );
		}

		return $url;
	}

	/**
	 * Bind core implementations to the container.
	 *
	 * @since 1.0.0
	 */
	public function bootstrap() {
		static::load_text_domain();

		$this->container->register( Assets\Controller::class );
		$this->container->singleton( Cache::class, Cache::class );
		$this->container->singleton( Dependencies\Dependency::class, Dependencies\Dependency::class );
		$this->container->singleton( Plugin_Register::class, Plugin_Register::class );
		$this->container->singleton( 'context', Context\Context::class );
		$this->container->singleton( Context\Post_Request_Type::class, Context\Post_Request_Type::class );

		App::get( Plugin_Register::class )->register_plugin();

		// load functions
		require_once SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/functions/stripe.php';
		require_once SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/functions/utils.php';
		require_once SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/functions/webhook.php';
	}

	/**
	 * Bind implementations to the container.
	 *
	 * @since 1.0.0
	 */
	public function bind_implementations() {
		$this->container->register( REST\Controller::class );
		$this->container->register( Admin\Controller::class );

		$this->container->singleton( Stripe::class, Stripe::class );
		$this->container->singleton( Field_Manager::class, Field_Manager::class );

		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			add_action( 'before_woocommerce_init', function() {
				try {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SIMPLESECUREWP_STRIPE_FILE_PATH . 'stripe-payments.php', true );
				} catch ( \Exception $e ) {
				}
			} );
		}
	}

	/**
	 * Bind implementations after WooCommerce is initialized.
	 *
	 * @since 1.0.0
	 */
	public function bind_woocommerce_dependencies() {
		$this->container->register( API\Controller::class );
		$this->container->register( Gateways\Controller::class );
		$this->container->register( Shortcodes\Controller::class );
		$this->container->register( Features\Controller::class );
		$this->container->register( Integrations\Controller::class );
		$this->container->register( Assets\Controller::class );
		$this->container->register( StripeIntegration\Controller::class );

		$this->container->singleton( Customer_Manager::class, new Customer_Manager() );
		$this->container->singleton( Controllers\PaymentIntent::class, new Controllers\PaymentIntent( [ 'sswps_cc' ] ) );
		$this->container->singleton( Products\ProductController::class, new Products\ProductController() );
	}

	/**
	 * Loads the plugin localization files.
	 *
	 * @since 1.0.0
	 */
	public static function load_text_domain() {
		$plugin_base_dir = dirname( plugin_basename( SIMPLESECUREWP_STRIPE_FILE ) );
		$plugin_rel_path = $plugin_base_dir . DIRECTORY_SEPARATOR . 'lang';

		load_plugin_textdomain( static::$text_domain, false, $plugin_rel_path );
	}

	/**
	 * Return the plugin template path.
	 *
	 * @since 1.0.0
	 */
	public function template_path() {
		return 'simple-secure-stripe';
	}

	/**
	 * Return the plguins default directory path for template files.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_view_path() : string {
		return SIMPLESECUREWP_STRIPE_FILE_PATH . 'src/views/';
	}

	/**
	 * Schedule actions required by the plugin
	 *
	 * @since 1.0.0
	 */
	public function scheduled_actions() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		if ( ! method_exists( WC(), 'queue' ) ) {
			return;
		}

		if ( WC()->queue()->get_next( 'sswps_remove_order_locks' ) ) {
			return;
		}

		WC()->queue()->schedule_recurring( strtotime( 'today midnight' ), DAY_IN_SECONDS, 'sswps_remove_order_locks' );
	}

	/**
	 * Sets context for the plugin.
	 *
	 * @param Context\Context $context
	 *
	 * @return void
	 */
	public function set_context( Context\Context $context ) {
		$this->plugin_context = $context;
	}

	/**
	 * @since 1.0.0
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function is_request( $type ) : bool {
		if ( ! did_action( 'before_woocommerce_init' ) ) {
			return false;
		}

		switch ( $type ) {
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! REST\API::is_wp_rest_request();
			default:
				return true;
		}
	}
}