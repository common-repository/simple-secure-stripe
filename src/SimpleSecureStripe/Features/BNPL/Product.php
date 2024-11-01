<?php
namespace SimpleSecureWP\SimpleSecureStripe\Features\BNPL;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

class Product extends Abstract_Concern {

	public function render_above_price() {
		$this->render_when( 'product_location', 'above_price' );
	}

	public function render_below_price() {
		$this->render_when( 'product_location', 'below_price' );
	}

	public function render_below_add_to_cart() {
		$this->render_when( 'product_location', 'below_add_to_cart' );
	}

	public function render( array $gateways ) {
		foreach ( $gateways as $gateway ) {
			$id = str_replace( '_', '-', $gateway->id );
			?>
			<div id="wc-<?php echo esc_attr( $id ); ?>-product-msg" class="<?php echo esc_attr( $gateway->id ); ?>-product-message sswps-bnpl-product-message"></div>
			<?php
		}
	}
}