<?php
namespace SimpleSecureWP\SimpleSecureStripe\Integrations;

use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;

class Controller extends Abstract_Controller {
	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->register( WooCommerceSubscriptions\Controller::class );
		$this->container->register( FunnelKit\Controller::class );

		$this->container->singleton( CartFlows\Main::class, new CartFlows\Main() );
		$this->container->singleton( CheckoutWC\Main::class, new CheckoutWC\Main() );
		$this->container->singleton( GermanMarket\Package::class, new GermanMarket\Package() );
		$this->container->singleton( WooCommerceBlocks\Package::class, new WooCommerceBlocks\Package() );
		$this->container->singleton( WooCommercePreOrders\Package::class, new WooCommercePreOrders\Package() );

		$this->hooks();
	}

	/**
	 * Bind hooks.
	 */
	public function hooks() {
		add_action( 'woocommerce_blocks_loaded', $this->container->callback( WooCommerceBlocks\Package::class, 'init' ) );
	}
}