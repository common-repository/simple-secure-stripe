<?php

namespace SimpleSecureWP\SimpleSecureStripe\Products;

use SimpleSecureWP\SimpleSecureStripe\Utils;

class ProductController {

	public function __construct() {
		$this->initialize();
	}

	public function initialize() {
		add_filter( 'woocommerce_available_variation', [ $this, 'add_variation_product_price' ] );
	}

	public function add_variation_product_price( $data ) {
		if ( isset( $data['display_price'] ) ) {
			$data['display_price_cents'] = Utils\Currency::add_number_precision( $data['display_price'] );
		}

		return $data;
	}

}