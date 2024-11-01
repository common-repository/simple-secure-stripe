<?php


namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;


use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripeLocalPayment;

class SepaPayment extends AbstractStripeLocalPayment {

	protected $name = 'sswps_sepa';

	public function get_payment_method_data() {
		return wp_parse_args( [
			'mandate' => $this->payment_method->get_local_payment_description(),
		], parent::get_payment_method_data() );
	}
}