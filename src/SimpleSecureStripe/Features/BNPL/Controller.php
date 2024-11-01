<?php
namespace SimpleSecureWP\SimpleSecureStripe\Features\BNPL;

use SimpleSecureWP\SimpleSecureStripe\Abstract_Controller;

/**
 * Buy Now, Pay Later feature provider.
 *
 * @since 1.0.0
 */
class Controller extends Abstract_Controller {
	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( Cart::class, Cart::class );
		$this->container->singleton( Category::class, Category::class );
		$this->container->singleton( Gateways::class, Gateways::class );
		$this->container->singleton( Product::class, Product::class );
	}

	public function hooks() {
		// Cart.
		add_action( 'woocommerce_cart_totals_after_order_total', $this->container->callback( Cart::class, 'render_after_order_total' ) );
		add_action( 'woocommerce_proceed_to_checkout', $this->container->callback( Cart::class, 'render_after_checkout_button' ), 21 );

		// Category.
		add_action( 'woocommerce_before_shop_loop', $this->container->callback( Category::class, 'initialize_loop' ) );
		add_action( 'woocommerce_shop_loop', $this->container->callback( Category::class, 'add_product_data' ) );
		add_action( 'woocommerce_after_shop_loop_item_title', $this->container->callback( Category::class, 'render_after_price' ), 20 );
		add_action( 'woocommerce_after_shop_loop_item', $this->container->callback( Category::class, 'render_after_add_to_cart' ), 15 );
		add_action( 'woocommerce_after_shop_loop', $this->container->callback( Category::class, 'end_loop' ) );

		// Product.
		add_action( 'woocommerce_single_product_summary', $this->container->callback( Product::class, 'render_above_price' ), 8 );
		add_action( 'woocommerce_single_product_summary', $this->container->callback( Product::class, 'render_below_price' ), 15 );
		add_action( 'woocommerce_after_add_to_cart_button', $this->container->callback( Product::class, 'render_below_add_to_cart' ), 5 );
	}
}