<?php

namespace SimpleSecureWP\SimpleSecureStripe\Features\Installments;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Features\Installments\Installments as InstallmentsFeature;
use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;

/**
 * @since 1.0.0
 */
class Controller extends Abstract_Controller {
	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( InstallmentsFeature::class, InstallmentsFeature::class );
		$this->container->singleton( Formatter::class, Formatter::class );

		if ( ! InstallmentsFeature::is_active() ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Bind hooks.
	 */
	public function hooks() {
		add_action( 'sswps/save_order_meta', $this->container->callback( InstallmentsFeature::class, 'add_order_meta' ), 10, 3 );
		add_filter( 'woocommerce_get_order_item_totals', $this->container->callback( InstallmentsFeature::class, 'add_order_item_total' ), 10, 2 );
		add_filter( 'sswps/can_update_payment_intent', $this->container->callback( InstallmentsFeature::class, 'can_update_payment_intent' ), 10, 2 );
		add_filter( 'sswps/payment_intent_confirmation_args', $this->container->callback( InstallmentsFeature::class, 'add_confirmation_args' ), 10, 2 );
	}
}