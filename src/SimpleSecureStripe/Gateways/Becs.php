<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\Payment;

/**
 * Class Becs
 *
 * @since   1.0.0
 * @package Stripe/Gateways
 * @author Simple & Secure WP
 */
class Becs extends Abstract_Local_Payment {

	use Payment\Traits\Local_Intent;

	protected string $payment_method_type = 'au_becs_debit';

	public bool $synchronous = false;

	public string $token_type = 'Stripe_Becs';

	public function __construct() {
		$this->local_payment_type = 'au_becs_debit';
		$this->currencies         = [ 'AUD' ];
		$this->countries          = [ 'AU' ];
		$this->id                 = 'sswps_becs';
		$this->tab_title          = __( 'BECS', 'simple-secure-stripe' );
		$this->method_title       = __( 'BECS', 'simple-secure-stripe' );
		$this->method_description = __( 'BECS direct debit gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = '';
		parent::__construct();

		$this->local_payment_description = sprintf(
			/* translators: 1: open anchor tag, 2: close anchor tag, 3: company name */
			__(
				'By providing your bank account details and confirming this payment, you agree to this Direct Debit Request and the %1$sDirect Debit Request service agreement%2$s, and authorise Stripe Payments Australia Pty Ltd ACN 160 180 343 Direct Debit User ID number 507156 ("Stripe") to debit your account through the Bulk Electronic Clearing System (BECS) on behalf of %3$s (the "Merchant") for any amounts separately communicated to you by the Merchant. You certify that you are either an account holder or an authorised signatory on the account listed above.',
				'simple-secure-stripe'
			),
			'<a href="https://stripe.com/au-becs-dd-service-agreement/legal" target="_blank" rel="noopener noreferrer">',
			'</a>',
			$this->get_option( 'company_name' )
		);
	}

	public function init_supports() {
		parent::init_supports();
		$this->supports[] = 'subscriptions';
		$this->supports[] = 'subscription_cancellation';
		$this->supports[] = 'multiple_subscriptions';
		$this->supports[] = 'subscription_reactivation';
		$this->supports[] = 'subscription_suspension';
		$this->supports[] = 'subscription_date_changes';
		$this->supports[] = 'subscription_payment_method_change_admin';
		$this->supports[] = 'subscription_amount_changes';
		$this->supports[] = 'subscription_payment_method_change_customer';
		$this->supports[] = 'pre-orders';
	}

	public function get_local_payment_settings() {
		return array_merge( parent::get_local_payment_settings(), [
			'company_name'  => [
				'title'       => __( 'Company Name', 'simple-secure-stripe' ),
				'type'        => 'text',
				'default'     => get_bloginfo( 'name' ),
				'description' => __( 'The company name that appears in the BECS mandate text.', 'simple-secure-stripe' ),
			],
			'method_format' => [
				'title'       => __( 'Payment Method Display', 'simple-secure-stripe' ),
				'type'        => 'select',
				'class'       => 'wc-enhanced-select',
				'options'     => wp_list_pluck( $this->get_payment_method_formats(), 'example' ),
				'default'     => 'type_ending_last4',
				'desc_tip'    => true,
				'description' => __( 'This option allows you to customize how the payment method will display for your customers on orders, subscriptions, etc.', 'simple-secure-stripe' ),
			],
		] );
	}

	public function get_new_method_label() {
		return __( 'New Account', 'simple-secure-stripe' );
	}

	public function get_saved_methods_label() {
		return __( 'Saved Accounts', 'simple-secure-stripe' );
	}

}
