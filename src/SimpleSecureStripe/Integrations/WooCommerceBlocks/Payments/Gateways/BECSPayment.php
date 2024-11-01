<?php


namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;


use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripeLocalPayment;

class BECSPayment extends AbstractStripeLocalPayment {

	protected $name = 'sswps_becs';

	public function get_payment_method_data() {
		return array_merge( parent::get_payment_method_data(), [ 'mandate' => $this->payment_method->get_local_payment_description() ] );
	}

}