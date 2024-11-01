<?php


namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;


use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripeLocalPayment;

class AlipayPayment extends AbstractStripeLocalPayment {

	protected $name = 'sswps_alipay';
}