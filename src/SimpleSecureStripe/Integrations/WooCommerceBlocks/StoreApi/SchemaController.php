<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\StoreApi;

use Automattic\WooCommerce\StoreApi\Schemas\ExtendSchema;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\PaymentsApi;

class SchemaController {

	private ExtendSchema $extend_schema;

	private PaymentsApi $payments_api;

	public function __construct( ExtendSchema $extend_schema, PaymentsApi $payments_api ) {
		$this->extend_schema = $extend_schema;
		$this->payments_api  = $payments_api;
	}

	public function initialize() {
		foreach ( $this->payments_api->get_payment_methods() as $payment_method ) {
			if ( $payment_method->is_active() ) {
				$data = $payment_method->get_endpoint_data();
				if ( ! empty( $data ) ) {
					if ( $data instanceof EndpointData ) {
						$data = $data->to_array();
					}
					$this->extend_schema->register_endpoint_data( $data );
				}
			}
		}
	}

}