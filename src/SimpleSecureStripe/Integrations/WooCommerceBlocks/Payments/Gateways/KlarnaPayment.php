<?php


namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;


use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripeLocalPayment;

class KlarnaPayment extends AbstractStripeLocalPayment {

	protected $name = 'sswps_klarna';

	public function get_payment_method_data() {
		return wp_parse_args( [
			'requiredParams' => $this->payment_method->get_required_parameters(),
		], parent::get_payment_method_data() );
	}

}