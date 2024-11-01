<?php


namespace SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit;

use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;

class Controller extends Abstract_Controller {
	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( Main::class, new Main() );
		$this->container->singleton( Cart\Cart::class, Cart\Cart::class );

		$this->hooks();
	}

	/**
	 * Bind hooks.
	 */
	public function hooks() {
		add_action( 'fkcart_after_checkout_button', $this->container->callback( Cart\Cart::class, 'render_after_checkout_button' ) );
	}
}