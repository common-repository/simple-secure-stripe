<?php

namespace SimpleSecureWP\SimpleSecureStripe\REST;

use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;

class Controller extends Abstract_Controller {
	/**
	 * REST controllers.
	 */
	private array $controllers = [
		'cart'           => Cart::class,
		'checkout'       => Checkout::class,
		'googlepay'      => Google_Pay::class,
		'order_actions'  => Order_Actions::class,
		'payment_intent' => Payment_Intent::class,
		'payment_method' => Payment_Method::class,
		'product_data'   => Product_Data::class,
		'settings'       => Gateway_Settings::class,
		'signup'         => Signup::class,
		'source'         => Source::class,
		'webhook'        => Webhook::class,
	];

	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( API::class, API::class );

		foreach ( $this->controllers as $key => $class ) {
			$this->container->singleton( $class, $class );
			$this->container->get( API::class )->add_controller( $key, $this->container->get( $class ) );
		}

		$this->hooks();
	}

	/**
	 * Hooks.
	 */
	private function hooks() {
		add_action( 'wc_ajax_sswps_frontend_request', $this->container->callback(API::class, 'process_frontend_request' ) );
		add_action( 'rest_api_init', $this->container->callback(API::class,  'register_routes' ) );
		add_action( 'wp_ajax_sswps_admin_request', $this->container->callback(API::class,  'process_frontend_request' ) );
	}
}