<?php

namespace SimpleSecureWP\SimpleSecureStripe\Features\Installments\Filters;

use SimpleSecureWP\SimpleSecureStripe\Checker;
use WC_Cart;
use WC_Order;

class Subscription extends Abstract_Filter {

	private $cart;

	private $order;

	/**
	 * @param WC_Cart|null  $cart
	 * @param WC_Order|null $order
	 */
	public function __construct( $cart, $order ) {
		$this->cart  = $cart;
		$this->order = $order;
	}

	function is_available() {
		$is_available = true;
		if ( Checker::is_woocommerce_subscriptions_active() ) {
			if ( $this->cart ) {
				$is_available = ! \WC_Subscriptions_Cart::cart_contains_subscription();
			} elseif ( $this->order ) {
				$is_available = ! wcs_order_contains_subscription( $this->order );
			}
		}

		return $is_available;
	}

}