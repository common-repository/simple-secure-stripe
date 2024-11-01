<?php

namespace SimpleSecureWP\SimpleSecureStripe\Admin\Settings;

use SimpleSecureWP\SimpleSecureStripe\Features\Installments\Installments;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Utils;

/**
 * @since 1.0.0
 */
class Advanced extends Abstract_Settings {

	public function __construct() {
		$this->id        = 'sswps_advanced';
		$this->tab_title = __( 'Advanced Settings', 'simple-secure-stripe' );
		parent::__construct();
	}

	public function hooks() {
		parent::hooks();
		add_action( 'woocommerce_update_options_checkout_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	public function init_form_fields() {
		$this->form_fields = [
			'title'                  => [
				'type'  => 'title',
				'title' => __( 'Advanced Settings', 'simple-secure-stripe' ),
			],
			'settings_description'   => [
				'type'        => 'description',
				'description' => __( 'This section provides advanced settings that allow you to configure functionality that fits your business process.', 'simple-secure-stripe' ),
			],
			'locale'                 => [
				'title'       => __( 'Locale Type', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'site',
				'options'     => [
					'auto' => __( 'Auto', 'simple-secure-stripe' ),
					'site' => __( 'Site Locale', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __(
					'If set to "auto" Stripe will determine the locale to use based on the customer\'s browser/location settings. Site locale uses the WordPress locale setting.',
					'simple-secure-stripe'
				),
			],
			'installments'           => [
				'title'       => __( 'Installments', 'simple-secure-stripe' ),
				'type'        => 'checkbox',
				'default'     => 'yes',
				'value'       => 'yes',
				'desc_tip'    => false,
				/* translators: supported countries description. */
				'description' => sprintf( __( 'If enabled, installments will be available for the credit card gateway. %1$s', 'simple-secure-stripe' ), $this->get_supported_countries_description() ),
			],
			'statement_descriptor'   => [
				'title'             => __( 'Statement Descriptor', 'simple-secure-stripe' ),
				'type'              => 'text',
				'default'           => '',
				'desc_tip'          => true,
				'description'       => __(
					'Maximum of 22 characters. This value represents the full statement descriptor that your customer will see. If left blank, Stripe will use your account descriptor.',
					'simple-secure-stripe'
				),
				'sanitize_callback' => function( $value ) {
					if ( ! empty( $value ) && strlen( $value ) > 21 ) {
						$value = substr( $value, 0, 22 );
					}

					return Utils\Misc::sanitize_statement_descriptor( $value );
				},
			],
			'stripe_fee'             => [
				'title'       => __( 'Display Stripe Fee', 'simple-secure-stripe' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'value'       => 'yes',
				'desc_tip'    => true,
				'description' => __(
					'If enabled, the Stripe fee will be displayed on the Order Details page. The fee and net payout are displayed in your Stripe account currency.',
					'simple-secure-stripe'
				),
			],
			'stripe_fee_currency'    => [
				'title'             => __( 'Fee Display Currency', 'simple-secure-stripe' ),
				'type'              => 'checkbox',
				'default'           => 'no',
				'description'       => __(
					'If enabled, the Stripe fee and payout will be displayed in the currency of the order. Stripe by default provides the fee and payout in the Stripe account\'s currency.',
					'simple-secure-stripe'
				),
				'custom_attributes' => [
					'data-show-if' => [
						'stripe_fee' => true,
					],
				],
			],
			'capture_status'         => [
				'title'       => __( 'Capture Status', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'completed',
				'options'     => [
					'completed'  => __( 'Completed', 'simple-secure-stripe' ),
					'processing' => __( 'Processing', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'For orders that are authorized, when the order is set to this status, it will trigger a capture.', 'simple-secure-stripe' ),
			],
			'refund_cancel'          => [
				'title'       => __( 'Refund On Cancel', 'simple-secure-stripe' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'value'       => 'yes',
				'desc_tip'    => true,
				'description' => __( 'If enabled, the plugin will process a payment cancellation or refund within Stripe when the order\'s status is set to cancelled.', 'simple-secure-stripe' ),
			],
			'link_title'             => [
				'type'  => 'title',
				'title' => __( 'Link Settings', 'simple-secure-stripe' ),
			],
			'link_enabled'           => [
				'title'       => __( 'Faster Checkout With Link', 'simple-secure-stripe' ),
				'type'        => 'checkbox',
				'default'     => 'yes',
				'value'       => 'yes',
				'description' => __(
					'With Link enabled, Stripe will use your customer\'s email address to determine if they have used Stripe in the past. If yes, their payment info, billing and shipping information can be used to
				auto-populate the checkout page resulting in higher conversion rates and less customer friction. If enabled, the Stripe payment form will be used because it\'s the only card form compatible with Link.', 'simple-secure-stripe'
				),
			],
			'link_email'             => [
				'title'             => __( 'Move email field to top of page', 'simple-secure-stripe' ),
				'type'              => 'checkbox',
				'default'           => 'no',
				'value'             => 'yes',
				'description'       => __( 'If enabled, the email field will be placed at the top of the checkout page. Link uses the email address so it\'s best to prioritize it.', 'simple-secure-stripe' ),
				'custom_attributes' => [
					'data-show-if' => [
						'link_enabled' => true,
					],
				],
			],
			'link_icon'              => [
				'title'             => __( 'Show Link Icon', 'simple-secure-stripe' ),
				'type'              => 'select',
				'default'           => 'no',
				'options'           => [
					'light' => __( 'Light', 'simple-secure-stripe' ),
					'dark'  => __( 'Dark', 'simple-secure-stripe' ),
					'no'    => __( 'No Icon', 'simple-secure-stripe' ),
				],
				'description'       => __( 'Render the Link icon in the email field. This indicates to customers that Link is enabled.', 'simple-secure-stripe' ),
				'custom_attributes' => [
					'data-show-if' => [
						'link_enabled' => true,
					],
				],
			],
			'link_autoload'          => [
				'title'             => __( 'Launch link on page load', 'simple-secure-stripe' ),
				'type'              => 'checkbox',
				'default'           => 'no',
				'value'             => 'yes',
				'description'       => __( 'If enabled and the email address is already populated, the plugin will attempt to launch Link  on the checkout page.', 'simple-secure-stripe' ),
				'custom_attributes' => [
					'data-show-if' => [
						'link_enabled' => true,
					],
				],
			],
			'x' => [
				'type' => 'html',
			],
			'gdpr' => [
				'title' => __( 'GDPR Settings', 'simple-secure-stripe' ),
				'type'  => 'title',
			],
			'customer_creation' => [
				'title' => __( 'Customer Creation', 'simple-secure-stripe' ),
				'type'  => 'select',
				'default' => 'account_creation',
				'options' => [
					'account_creation' => __( 'When account is created', 'simple-secure-stripe' ),
					'checkout' => __( 'When a customer ID is required', 'simple-secure-stripe' ),
				],
				'description' => __( 'This option allows you to control when a Stripe customer object is created. The plugin can create a Stripe customer ID when your customer creates an account with your store, or it can wait until the Stripe customer ID is required for things like payment on the checkout page.', 'simple-secure-stripe' )
			],
			'disputes'               => [
				'title' => __( 'Dispute Settings', 'simple-secure-stripe' ),
				'type'  => 'title',
			],
			'dispute_created'        => [
				'title'       => __( 'Dispute Created', 'simple-secure-stripe' ),
				'type'        => 'checkbox',
				'default'     => 'yes',
				'description' => __(
					'If enabled, the plugin will listen for the <strong>charge.dispute.created</strong> webhook event and set the order\'s status to on-hold by default.',
					'simple-secure-stripe'
				),
			],
			'dispute_created_status' => [
				'title'             => __( 'Disputed Created Order Status', 'simple-secure-stripe' ),
				'type'              => 'select',
				'default'           => 'wc-on-hold',
				'options'           => wc_get_order_statuses(),
				'description'       => __( 'The status assigned to an order when a dispute is created.', 'simple-secure-stripe' ),
				'custom_attributes' => [
					'data-show-if' => [
						'dispute_created' => true,
					],
				],
			],
			'dispute_closed'         => [
				'title'       => __( 'Dispute Closed', 'simple-secure-stripe' ),
				'type'        => 'checkbox',
				'default'     => 'yes',
				'description' => __(
					'If enabled, the plugin will listen for the <strong>charge.dispute.closed</strong> webhook event and set the order\'s status back to the status before the dispute was opened.',
					'simple-secure-stripe'
				),
			],
			'reviews'                => [
				'title' => __( 'Review Settings', 'simple-secure-stripe' ),
				'type'  => 'title',
			],
			'review_created'         => [
				'title'       => __( 'Review Created', 'simple-secure-stripe' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __(
					'If enabled, the plugin will listen for the <strong>review.created</strong> webhook event and set the order\'s status to on-hold by default.',
					'simple-secure-stripe'
				),
			],
			'review_closed'          => [
				'title'       => __( 'Review Closed', 'simple-secure-stripe' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __(
					'If enabled, the plugin will listen for the <strong>review.closed</strong> webhook event and set the order\'s status back to the status before the review was opened.',
					'simple-secure-stripe'
				),
			],
			'email_title'            => [
				'type'  => 'title',
				'title' => __( 'Stripe Email Options', 'simple-secure-stripe' ),
			],
			'email_enabled'          => [
				'type'        => 'checkbox',
				'title'       => __( 'Email Receipt', 'simple-secure-stripe' ),
				'default'     => 'no',
				'description' => __(
					'If enabled, an email receipt will be sent to the customer by Stripe when the order is processed.',
					'simple-secure-stripe'
				),
			],
		];
	}

	public function process_admin_options() {
		parent::process_admin_options();
		if ( $this->is_active( 'link_enabled' ) ) {
			/**
			 * @var Gateways\Abstract_Gateway $payment_method
			 */
			$payment_method = WC()->payment_gateways()->payment_gateways()['sswps_cc'];
			$payment_method->update_option( 'form_type', 'payment' );
			sswps_log_info( 'Stripe payment form enabled for Link integration compatibility' );
		}

		return true;
	}

	public function is_fee_enabled() {
		return $this->is_active( 'stripe_fee' );
	}

	public function is_display_order_currency() {
		return $this->is_active( 'stripe_fee_currency' );
	}

	public function is_email_receipt_enabled() {
		return $this->is_active( 'email_enabled' );
	}

	public function is_refund_cancel_enabled() {
		return $this->is_active( 'refund_cancel' );
	}

	public function is_dispute_created_enabled() {
		return $this->is_active( 'dispute_created' );
	}

	public function is_dispute_closed_enabled() {
		return $this->is_active( 'dispute_closed' );
	}

	public function is_review_opened_enabled() {
		return $this->is_active( 'review_created' );
	}

	public function is_review_closed_enabled() {
		return $this->is_active( 'review_closed' );
	}

	public function get_supported_countries_description() {
		return sprintf(
			/* translators: 1: list of supported countries, 2: list of supported currencies. */
			__( 'Supported Stripe account countries: %1$s. Supported currencies: %2$s', 'simple-secure-stripe' ),
			implode( ', ', Installments::get_supported_countries() ),
			implode( ', ', Installments::get_supported_currencies() )
		);
	}

}
