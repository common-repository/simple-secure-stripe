<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

/**
 *
 * @package Stripe/Gateways
 * @author Simple & Secure WP
 *
 */
class Sepa extends Abstract_Local_Payment {

	use Payment\Traits\Local_Intent;

	protected string $payment_method_type = 'sepa_debit';

	public string $token_type = 'Stripe_Sepa';

	/**
	 * @var bool
	 */
	protected $supports_save_payment_method = true;

	public function __construct() {
		$this->synchronous        = false;
		$this->local_payment_type = 'sepa_debit';
		$this->currencies         = [ 'EUR' ];
		$this->id                 = 'sswps_sepa';
		$this->tab_title          = __( 'SEPA', 'simple-secure-stripe' );
		$this->template_name      = 'local-payment.php';
		$this->method_title       = __( 'SEPA', 'simple-secure-stripe' );
		$this->method_description = __( 'SEPA gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/sepa.svg' );
		parent::__construct();

		$this->local_payment_description = sprintf(
			/* translators: %s: company name */
			__(
				'By providing your IBAN and confirming this payment, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.',
				'simple-secure-stripe'
			),
			$this->get_option( 'company_name' )
		);

		$this->settings['save_card_enabled'] = 'yes';
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

	public function init_form_fields() {
		parent::init_form_fields();
		$this->form_fields['allowed_countries']['default'] = 'all';
	}

	public function get_element_params() {
		return array_merge( parent::get_element_params(), [ 'supportedCountries' => [ 'SEPA' ] ] );
	}

	public function get_local_payment_settings() {
		return parent::get_local_payment_settings() + [
				'company_name'  => [
					'title'       => __( 'Company Name', 'simple-secure-stripe' ),
					'type'        => 'text',
					'default'     => get_bloginfo( 'name' ),
					'desc_tip'    => true,
					'description' => __( 'The name of your company that will appear in the SEPA mandate.', 'simple-secure-stripe' ),
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
			];
	}

	public function get_payment_description() {
		return parent::get_payment_description() .
			sprintf( '<p><a target="_blank" href="https://stripe.com/docs/sources/sepa-debit#testing" rel="noopener noreferrer">%s</a></p>', __( 'SEPA Test Accounts', 'simple-secure-stripe' ) );
	}

	public function get_new_method_label() {
		return __( 'New Account', 'simple-secure-stripe' );
	}

	public function get_saved_methods_label() {
		return __( 'Saved Accounts', 'simple-secure-stripe' );
	}

	public function get_payment_token( $method_id, $method_details = [] ) {
		$token = parent::get_payment_token( $method_id, $method_details );

		$mandate = $token->get_mandate();
		$url     = $token->get_mandate_url();
		if ( $mandate && ! $url ) {
			$mandate = $this->gateway->mode( $token->get_environment() )->mandates->retrieve( $mandate );
			if ( isset( $mandate->payment_method_details->sepa_debit->url ) ) {
				$token->set_mandate_url( $mandate->payment_method_details->sepa_debit->url );
			}
		}

		return $token;
	}

}