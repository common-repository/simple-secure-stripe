<?php


namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;


use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripeLocalPayment;

class GrabPayPayment extends AbstractStripeLocalPayment {

	protected $name = 'sswps_grabpay';
}