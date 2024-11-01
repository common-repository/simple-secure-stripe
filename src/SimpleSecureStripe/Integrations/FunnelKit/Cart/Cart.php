<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit\Cart;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Field_Manager;

class Cart {

	public function render_after_checkout_button() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$cart = WC()->cart;
		if ( ! $cart || ! $cart->needs_payment() ) {
			return;
		}

		Field_Manager::mini_cart_buttons();
		if ( ! is_ajax() ) {
			return;
		}

		?>
		<script>
			if (window.jQuery) {
				jQuery(document.body).triggerHandler('wc_fragments_refreshed');
			}
		</script>
		<style>
			.wc-stripe-gpay-mini-cart,
			.wc-stripe-applepay-mini-cart,
			.wc-stripe-payment-request-mini-cart {
				margin-top: 10px;
				display: block;
			}
		</style>
		<?php
	}

	/**
	 * @return bool
	 */
	public function is_enabled(): bool {
		return class_exists( '\FKCart\Plugin' );
	}
}