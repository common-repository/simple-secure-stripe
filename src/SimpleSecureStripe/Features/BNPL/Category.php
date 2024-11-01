<?php
namespace SimpleSecureWP\SimpleSecureStripe\Features\BNPL;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Utils;

class Category extends Abstract_Concern {
	public function initialize_loop() {
		App::get( Assets\Data::class )->add( 'currency', get_woocommerce_currency() );
		App::get( Assets\Data::class )->add( 'products', [] );
		App::get( Assets\Data::class )->add( 'product_types', [ 'simple', 'variable', 'group' ] );
	}

	/**
	 * Enqueue assets and add data.
	 *
	 * @since 1.0.0
	 */
	public function end_loop() {
		if ( empty( $this->gateways ) ) {
			return;
		}

		$this->enqueue_scripts();
		if ( App::get( Assets\Data::class )->has_data() ) {
			App::get( Assets\Data::class )->print_data( 'sswps_bnpl_shop_params', App::get( Assets\Data::class )->get_data() );
		}
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 1.0.0
	 */
	private function enqueue_scripts() {
		if ( empty( $this->gateways ) ) {
			return;
		}

		foreach ( $this->gateways as $gateway ) {
			$gateway->enqueue_category_scripts();
		}
	}

	/**
	 * Add product data to the asset data collection.
	 *
	 * @since 1.0.0
	 */
	public function add_product_data() {
		global $product;
		if ( ! $product ) {
			return;
		}

		if ( empty( $this->gateways ) ) {
			return;
		}

		$data   = App::get( Assets\Data::class )->get( 'products' );
		$price  = wc_get_price_to_display( $product );
		$data[] = [
			'id'           => $product->get_id(),
			'price'        => $price,
			'price_cents'  => Utils\Currency::add_number_precision( $price ),
			'product_type' => $product->get_type(),
		];
		App::get( Assets\Data::class )->add( 'products', $data );
	}

	/**
	 * Helper function for rendering below the price.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_after_price() {
		$this->render_when( 'shop_location', 'below_price' );
	}

	/**
	 * Helper function for rendering below the add to cart button.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_after_add_to_cart() {
		$this->render_when( 'shop_location', 'below_add_to_cart' );
	}

	/**
	 * Renders the gateway items.
	 *
	 * @since 1.0.0
	 *
	 * @param array $gateways
	 */
	public function render( $gateways ) {
		global $product;
		if ( ! $product || ! $gateways ) {
			return;
		}

		$product_id = $product->get_id();

		foreach ( $gateways as $gateway ) {
			$gateway->enqueue_payment_method_styles();
			$id = $gateway->id . '-' . $product_id;
			?>
			<div class="sswps-shop-message-container <?php echo esc_attr( $gateway->id ); ?>" id="sswps-shop-message-<?php echo esc_attr( $id ); ?>"></div>
			<?php
		}
	}
}