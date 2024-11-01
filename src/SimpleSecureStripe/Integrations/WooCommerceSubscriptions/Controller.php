<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceSubscriptions;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Checker;
use SimpleSecureWP\SimpleSecureStripe\REST\API;
use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;

class Controller extends Abstract_Controller {
	/**
	 * @inheritDoc
	 */
	public function register() {
		if ( ! Checker::is_woocommerce_subscriptions_active() ) {
			return;
		}

		App::singleton( Order_Metadata::class, Order_Metadata::class );
		App::singleton( Payment_Intent::class, Payment_Intent::class );
		App::singleton( Retry_Manager::class, Retry_Manager::class );
		App::singleton( Utils::class, Utils::class );
		App::singleton( REST\Change_Payment_Method::class, REST\Change_Payment_Method::class );
		App::get( API::class )->add_controller( 'subscriptions', App::get( REST\Change_Payment_Method::class ) );

		add_filter( 'sswps/create_setup_intent', App::callback( Payment_Intent::class, 'maybe_create_setup_intent' ) );
		add_filter( 'sswps/payment_intent_args', App::callback( Payment_Intent::class, 'update_payment_intent_args' ), 10, 2 );
		add_filter( 'sswps/setup_intent_params', App::callback( Payment_Intent::class, 'update_setup_intent_params' ), 10, 2 );
		add_filter( 'sswps/update_setup_intent_params', App::callback( Payment_Intent::class, 'update_setup_intent_params' ), 10, 2 );

		/**
		 * Filter that is called when a setup-intent is created via the REST API
		 */
		add_filter( 'sswps/create_setup_intent_params', App::callback( Payment_Intent::class, 'add_setup_intent_params' ), 10, 2 );

		add_action( 'sswps/output_checkout_fields', App::callback( Payment_Intent::class, 'print_script_variables' ) );
		add_action( 'sswps/save_order_meta', App::callback( Order_Metadata::class, 'save_order_metadata' ), 10, 4 );
	}
}