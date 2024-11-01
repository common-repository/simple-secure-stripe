<?php

namespace SimpleSecureWP\SimpleSecureStripe;

/**
 *
 * @since  1.0.0
 * @author Simple & Secure WP
 *
 */
class Constants {
	const CUSTOMER_ID = '_sswps_customer';
	const PAYMENT_METHOD_TOKEN = '_sswps_payment_method_token';
	const PAYMENT_INTENT_ID = '_sswps_payment_intent_id';
	const PAYMENT_INTENT = '_sswps_payment_intent';
	const MODE = '_sswps_mode';
	const CART_ARGS = '_sswps_cart_args';
	const CHARGE_STATUS = '_sswps_charge_status';
	const SOURCE_ID = '_sswps_stripe_source_id';
	const STRIPE_INTENT_ID = '_sswps_stripe_intent_id';
	const STRIPE_CUSTOMER_ID = '_sswps_stripe_customer_id';
	const SUCCESS = 'success';
	const FAILURE = 'failure';
	const WOOCOMMERCE_STRIPE_ORDER_PAY = 'WOOCOMMERCE_STRIPE_ORDER_PAY';
	const PRODUCT_GATEWAY_ORDER = '_sswps_stripe_gateway_order';
	const BUTTON_POSITION = '_sswps_stripe_button_position';
	const REDIRECT_HANDLER = 'sswps_redirect_handler';
	const PROCESSING_PAYMENT = 'processing_payment';
	const PROCESSING_ORDER_PAY = 'processing_order_pay';
	const REQUIRES_CONFIRMATION = 'requires_confirmation';
	const REQUIRES_ACTION = 'requires_action';
	const SUCCEEDED = 'succeeded';
	const REQUIRES_CAPTURE = 'requires_capture';
	const REQUIRES_PAYMENT_METHOD = 'requires_payment_method';
	const SETUP_INTENT_ID = '_sswps_setup_intent_id';
	const BALANCE_TRANSACTION = '_sswps_stripe_balance_transaction';
	const STRIPE_FEE = '_sswps_stripe_fee';

	const STRIPE_MANDATE = '_sswps_mandate';
	const STRIPE_NET = '_sswps_stripe_net';
	const STRIPE_CURRENCY = '_sswps_stripe_currency';
	const PREV_STATUS = '_sswps_stripe_prev_status';
	const VOUCHER_PAYMENT = '_sswps_stripe_voucher_payment';
	const INSTALLMENT_PLAN = '_sswps_stripe_installment_plan';
	const LIVE = 'live';
	const TEST = 'test';
	const VERSION_KEY = 'sswps_version';
	const INITIAL_INSTALL = 'sswps_initialize_install';
	const AUTOMATIC = 'automatic';
	const MANUAL = 'manual';
}
