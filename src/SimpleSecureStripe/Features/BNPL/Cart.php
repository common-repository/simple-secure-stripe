<?php
namespace SimpleSecureWP\SimpleSecureStripe\Features\BNPL;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

class Cart extends Abstract_Concern {
	/**
	 * Sanitize the gateway ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The gateway ID.
	 *
	 * @return string
	 */
	private function sanitize_gateway_id( string $id ) : string {
		return str_replace( '_', '-', $id );
	}

	/**
	 * Renders the gateway after the order total.
	 *
	 * @since 1.0.0
	 */
	public function render_after_order_total() {
		$gateways = array_filter( $this->gateways, static function( $gateway ) {
			return $gateway->get_option( 'cart_location' ) === 'below_total';
		} );

		if ( empty( $gateways ) ) {
			return;
		}

		foreach ( $gateways as $gateway ) {
			$id = $this->sanitize_gateway_id( $gateway->id );
			?>
			<tr id="wc-<?php echo esc_attr( $id ); ?>-cart-container" class="<?php echo esc_attr( $gateway->id ); ?>-cart-message-container">
				<td colspan="2">
					<div id="wc-<?php echo $id ?>-cart-msg"></div>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Renders the gateway after the checkout button.
	 *
	 * @since 1.0.0
	 */
	public function render_after_checkout_button() {
		$gateways = array_filter( $this->gateways, static function( $gateway ) {
			return $gateway->get_option( 'cart_location' ) === 'below_checkout_button';
		} );

		if ( empty( $gateways ) ) {
			return;
		}

		foreach ( $gateways as $gateway ) {
			$id = $this->sanitize_gateway_id( $gateway->id );
			?>
			<div id="wc-<?php echo esc_attr( $id ); ?>-cart-container" class="<?php echo esc_attr( $gateway->id ); ?>-cart-message-container">
				<div id="wc-<?php echo esc_attr( $id ); ?>-cart-msg"></div>
			</div>
			<?php
		}
	}
}