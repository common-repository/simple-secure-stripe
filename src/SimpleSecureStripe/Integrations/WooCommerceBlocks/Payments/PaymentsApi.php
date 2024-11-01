<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments;

use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;
use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;
use Automattic\WooCommerce\Blocks\Registry\Container as Container;
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use SimpleSecureWP\SimpleSecureStripe\Checker;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Assets\Api as AssetsApi;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Config;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Package;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways\AffirmPayment;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways\BlikPayment;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways\KonbiniPayment;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways\PayNowPayment;
use SimpleSecureWP\SimpleSecureStripe\Installments\InstallmentController;
use SimpleSecureWP\SimpleSecureStripe\Features\Installments\Installments;
use SimpleSecureWP\SimpleSecureStripe\Features\Link\Link;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\REST;
use WP_Error;

class PaymentsApi {

	private $container;

	private $config;

	private $assets_registry;

	/**
	 * @var PaymentMethodRegistry
	 */
	private $payment_method_registry;

	/**
	 * @var PaymentResult
	 */
	protected $payment_result;

	private $payment_methods = [];

	public function __construct( Container $container, Config $config, AssetDataRegistry $assets_registry ) {
		$this->container       = $container;
		$this->config          = $config;
		$this->assets_registry = $assets_registry;
		$this->add_payment_methods();
		$this->initialize();
	}

	private function initialize() {
		add_action( 'woocommerce_blocks_payment_method_type_registration', [ $this, 'register_payment_methods' ] );
		add_action( 'woocommerce_blocks_checkout_enqueue_data', [ $this, 'enqueue_checkout_data' ] );
		add_action( 'woocommerce_blocks_cart_enqueue_data', [ $this, 'enqueue_cart_data' ] );
		add_action( 'woocommerce_rest_checkout_process_payment_with_context', [ $this, 'payment_with_context' ], 10, 2 );
		add_action( 'sswps/blocks_enqueue_styles', [ $this, 'enqueue_payment_styles' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_styles' ] );
	}

	private function add_payment_methods() {
		$this->container->register( Gateways\CreditCardPayment::class, function( Container $container ) {
			$instance = new Gateways\CreditCardPayment( $container->get( AssetsApi::class ) );
			$instance->set_installments( App::get( Installments::class ) );

			return $instance;
		} );
		$this->container->register( Gateways\GooglePayPayment::class, function( Container $container ) {
			return new Gateways\GooglePayPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\ApplePayPayment::class, function( Container $container ) {
			return new Gateways\ApplePayPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\PaymentRequest::class, function( Container $container ) {
			return new Gateways\PaymentRequest( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\IdealPayment::class, function( Container $container ) {
			return new Gateways\IdealPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\P24Payment::class, function( Container $container ) {
			return new Gateways\P24Payment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\BancontactPayment::class, function( Container $container ) {
			return new Gateways\BancontactPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\GiropayPayment::class, function( Container $container ) {
			return new Gateways\GiropayPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\EPSPayment::class, function( Container $container ) {
			return new Gateways\EPSPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\MultibancoPayment::class, function( Container $container ) {
			return new Gateways\MultibancoPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\SepaPayment::class, function( Container $container ) {
			return new Gateways\SepaPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\SofortPayment::class, function( Container $container ) {
			return new Gateways\SofortPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\WeChatPayment::class, function( Container $container ) {
			return new Gateways\WeChatPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\FPXPayment::class, function( Container $container ) {
			return new Gateways\FPXPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\BECSPayment::class, function( Container $container ) {
			return new Gateways\BECSPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\GrabPayPayment::class, function( Container $container ) {
			return new Gateways\GrabPayPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\AlipayPayment::class, function( Container $container ) {
			return new Gateways\AlipayPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\KlarnaPayment::class, function( Container $container ) {
			return new Gateways\KlarnaPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\ACHPayment::class, function( Container $container ) {
			return new Gateways\ACHPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\AfterpayPayment::class, function( Container $container ) {
			return new Gateways\AfterpayPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\BoletoPayment::class, function( Container $container ) {
			return new Gateways\BoletoPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\OXXOPayment::class, function( Container $container ) {
			return new Gateways\OXXOPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\LinkPayment::class, function( $container ) {
			$instance = new Gateways\LinkPayment( App::get( Link::class ), $container->get( AssetsApi::class ) );

			return $instance;
		} );
		$this->container->register( Gateways\AffirmPayment::class, function( Container $container ) {
			return new AffirmPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\BlikPayment::class, function( Container $container ) {
			return new BlikPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\KonbiniPayment::class, function( Container $container ) {
			return new KonbiniPayment( $container->get( AssetsApi::class ) );
		} );
		$this->container->register( Gateways\PayNowPayment::class, function( Container $container ) {
			return new PayNowPayment( $container->get( AssetsApi::class ) );
		} );
	}

	/**
	 * Register all payment methods used by the plugin.
	 *
	 * @param PaymentMethodRegistry $registry
	 */
	public function register_payment_methods( PaymentMethodRegistry $registry ) {
		//$payment_gateways              = WC()->payment_gateways()->payment_gateways();
		$this->payment_method_registry = $registry;
		$payment_methods               = [
			Gateways\CreditCardPayment::class,
			Gateways\GooglePayPayment::class,
			Gateways\ApplePayPayment::class,
			Gateways\PaymentRequest::class,
			Gateways\IdealPayment::class,
			Gateways\P24Payment::class,
			Gateways\BancontactPayment::class,
			Gateways\GiropayPayment::class,
			Gateways\EPSPayment::class,
			Gateways\MultibancoPayment::class,
			Gateways\SepaPayment::class,
			Gateways\SofortPayment::class,
			Gateways\WeChatPayment::class,
			Gateways\FPXPayment::class,
			Gateways\BECSPayment::class,
			Gateways\GrabPayPayment::class,
			Gateways\AlipayPayment::class,
			Gateways\KlarnaPayment::class,
			Gateways\ACHPayment::class,
			Gateways\AfterpayPayment::class,
			Gateways\BoletoPayment::class,
			Gateways\OXXOPayment::class,
			Gateways\LinkPayment::class,
			Gateways\AffirmPayment::class,
			Gateways\BlikPayment::class,
			Gateways\KonbiniPayment::class,
			Gateways\PayNowPayment::class,
		];

		foreach ( $payment_methods as $clazz ) {
			$this->add_payment_method_to_registry( $clazz, $registry );
		}
	}

	/**
	 * @param                       $clazz
	 * @param PaymentMethodRegistry $registry
	 */
	private function add_payment_method_to_registry( $clazz, $registry ) {
		$instance = $this->container->get( $clazz );
		$registry->register( $instance );
		$this->payment_methods[] = $instance;
	}

	/**
	 * @param AssetsApi $style_api
	 */
	public function enqueue_payment_styles( $style_api ) {
		foreach ( $this->payment_method_registry->get_all_registered() as $payment_method ) {
			if ( $payment_method instanceof AbstractStripePayment ) {
				$payment_method->enqueue_payment_method_styles( $style_api );
			}
		}
	}

	public function enqueue_editor_styles() {
		if ( wp_script_is( 'wc-checkout-block', 'registered' ) || wp_script_is( 'wc-cart-block', 'registered' ) ) {
			App::get( Package::class )->container()->get( AssetsApi::class )->enqueue_style();
		}
	}

	public function enqueue_checkout_data() {
		$this->enqueue_data( 'checkout' );
	}

	public function enqueue_cart_data() {
		$this->enqueue_data( 'cart' );
	}

	private function enqueue_data( $page ) {
		if ( ! $this->assets_registry->exists( 'stripeGeneralData' ) ) {
			$this->assets_registry->add(
				'stripeGeneralData', apply_filters( 'sswps/blocks_general_data', [
				'page'           => $page,
				'mode'           => sswps_mode(),
				'publishableKey' => sswps_get_publishable_key(),
				'stripeParams'   => [
					'stripeAccount' => sswps_get_account_id(),
					'apiVersion'    => '2020-08-27',
					'betas'         => [],
				],
				'version'        => $this->config->get_version(),
				'blocksVersion'  => \Automattic\WooCommerce\Blocks\Package::get_version(),
				'isOlderVersion' => \version_compare( \Automattic\WooCommerce\Blocks\Package::get_version(), '9.5.0', '<' ),
				'routes'         => [
					'process/payment'       => REST\API::get_endpoint( App::get( REST\Checkout::class )->rest_uri( 'checkout/payment' ) ),
					'create/setup_intent'   => REST\API::get_endpoint( App::get( REST\Payment_Intent::class )->rest_uri( 'setup-intent' ) ),
					'create/payment_intent' => REST\API::get_endpoint( App::get( REST\Payment_Intent::class )->rest_uri( 'payment-intent' ) ),
					'sync/intent'           => REST\API::get_endpoint( App::get( REST\Payment_Intent::class )->rest_uri( 'sync-payment-intent' ) ),
					'update/source'         => REST\API::get_endpoint( App::get( REST\Source::class )->rest_uri( 'update' ) ),
					'payment/data'          => REST\API::get_endpoint( App::get( REST\Google_Pay::class )->rest_uri( 'shipping-data' ) ),
					'shipping-address'      => REST\API::get_endpoint( App::get( REST\Cart::class )->rest_uri( 'shipping-address' ) ),
					'shipping-method'       => REST\API::get_endpoint( App::get( REST\Cart::class )->rest_uri( 'shipping-method' ) ),
				],
				'assetsUrl'      => App::get( Plugin::class )->assets_url(),
			] )
			);
		}
		if ( ! $this->assets_registry->exists( 'stripeErrorMessages' ) ) {
			$this->assets_registry->add( 'stripeErrorMessages', sswps_get_error_messages() );
		}

		if ( ! $this->assets_registry->exists( 'stripePaymentData' ) ) {
			$payment_data = [];
			if ( WC()->cart && sswps_pre_orders_active() && \WC_Pre_Orders_Cart::cart_contains_pre_order() && \WC_Pre_Orders_Product::product_is_charged_upon_release( \WC_Pre_Orders_Cart::get_pre_order_product() ) ) {
				$payment_data['pre_order'] = true;
			}
			if ( WC()->cart && Checker::is_woocommerce_subscriptions_active() && \WC_Subscriptions_Cart::cart_contains_subscription() ) {
				$payment_data['subscription'] = true;
			}
			$this->assets_registry->add( 'stripePaymentData', $payment_data );
		}
	}

	public function payment_with_context( PaymentContext $context, PaymentResult $result ) {
		$this->payment_result = $result;
		add_action( 'sswps/process_payment_error', [ $this, 'process_payment_error' ] );
	}

	/**
	 * @param WP_Error $error |null
	 */
	public function process_payment_error( $error ) {
		if ( $this->payment_result && $error ) {
			// add the error to the payment result
			$this->payment_result->set_payment_details( [
				'stripeErrorMessage' => $error->get_error_message(),
			] );
		}
	}

	/**
	 * @return AbstractStripePayment[]
	 */
	public function get_payment_methods() {
		return $this->payment_methods;
	}

}