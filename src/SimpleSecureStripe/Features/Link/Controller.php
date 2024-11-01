<?php

namespace SimpleSecureWP\SimpleSecureStripe\Features\Link;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;

/**
 * @since 1.0.0
 */
class Controller extends Abstract_Controller {
	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( Link::class, Link::class );

		$this->hooks();
	}

	/**
	 * Bind hooks.
	 */
	public function hooks() {
		// Link feature.
		add_action( 'wp_print_scripts', $this->container->callback( Link::class, 'enqueue_scripts' ), 5 );
		add_filter( 'sswps/get_localize_script_data_sswps', $this->container->callback( Link::class, 'add_script_params' ), 10, 1 );
		add_filter( 'sswps/payment_intent_args', $this->container->callback( Link::class, 'add_payment_method_type' ), 10, 2 );
		add_filter( 'woocommerce_checkout_fields', $this->container->callback( Link::class, 'add_billing_email_priority' ) );
		add_filter( 'sswps/payment_intent_confirmation_args', $this->container->callback( Link::class, 'add_confirmation_args' ), 10, 3 );
		add_filter( 'sswps/create_setup_intent_params', $this->container->callback( Link::class, 'add_setup_intent_params' ), 10, 2 );
	}
}