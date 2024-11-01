<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

use SimpleSecureWP\SimpleSecureStripe\Gateways;

/**
 * @var Gateways\Abstract_Gateway $this
 */
return [
	'enabled'           => [
		'title'       => __( 'Enabled', 'simple-secure-stripe' ),
		'type'        => 'checkbox',
		'default'     => 'yes',
		'value'       => 'yes',
		'desc_tip'    => true,
		'description' => __( 'If enabled, your site can accept credit card payments through Stripe.', 'simple-secure-stripe' ),
	],
	'desc1'             => [
		'type'        => 'description',
		'description' => '<a target="_blank" href="https://stripe.com/docs/testing#cards" rel="noopener noreferrer">' . __( 'Test cards', 'simple-secure-stripe' ) . '</a>',
	],
	'general_settings'  => [
		'type'  => 'title',
		'title' => __( 'General Settings', 'simple-secure-stripe' ),
	],
	'title_text'        => [
		'type'        => 'text',
		'title'       => __( 'Title', 'simple-secure-stripe' ),
		'default'     => __( 'Credit/Debit Cards', 'simple-secure-stripe' ),
		'desc_tip'    => true,
		'description' => __( 'Title of the credit card gateway', 'simple-secure-stripe' ),
	],
	'description'       => [
		'title'       => __( 'Description', 'simple-secure-stripe' ),
		'type'        => 'text',
		'default'     => '',
		'description' => __( 'Leave blank if you don\'t want a description to show for the gateway.', 'simple-secure-stripe' ),
		'desc_tip'    => true,
	],
	'method_format'     => [
		'title'       => __( 'Credit Card Display', 'simple-secure-stripe' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => wp_list_pluck( $this->get_payment_method_formats(), 'example' ),
		'value'       => '',
		'default'     => 'type_ending_in',
		'desc_tip'    => true,
		'description' => __(
			'This option allows you to customize how the credit card will display for your customers on orders, subscriptions, etc.',
			'simple-secure-stripe'
		),
	],
	'charge_type'       => [
		'type'        => 'select',
		'title'       => __( 'Charge Type', 'simple-secure-stripe' ),
		'default'     => 'capture',
		'class'       => 'wc-enhanced-select',
		'options'     => [
			'capture'   => __( 'Capture', 'simple-secure-stripe' ),
			'authorize' => __( 'Authorize', 'simple-secure-stripe' ),
		],
		'desc_tip'    => true,
		'description' => __(
			'This option determines whether the customer\'s funds are captured immediately or authorized and can be captured at a later date.',
			'simple-secure-stripe'
		),
	],
	'order_status'      => [
		'type'        => 'select',
		'title'       => __( 'Order Status', 'simple-secure-stripe' ),
		'default'     => 'default',
		'class'       => 'wc-enhanced-select',
		'options'     => array_merge( [ 'default' => __( 'Default', 'simple-secure-stripe' ) ], wc_get_order_statuses() ),
		'tool_tip'    => true,
		'description' => __(
			'This is the status of the order once payment is complete. If <b>Default</b> is selected, then WooCommerce will set the order status automatically based on internal logic which states if a product is virtual and downloadable then status is set to complete. Products that require shipping are set to Processing. Default is the recommended setting as it allows standard WooCommerce code to process the order status.',
			'simple-secure-stripe'
		),
	],
	'save_card_enabled' => [
		'type'        => 'checkbox',
		'value'       => 'yes',
		'default'     => 'yes',
		'title'       => __( 'Allow Credit Card Save', 'simple-secure-stripe' ),
		'desc_tip'    => false,
		'description' => __(
			'If enabled, a checkbox will be available on the checkout page allowing your customers to save their credit card. The payment methods are stored securely in Stripe\'s vault and never touch your server. Note: if the cart contains a subscription, there will be no checkbox because the payment method will be saved automatically.',
			'simple-secure-stripe'
		),
	],
	'force_3d_secure'   => [
		'title'       => __( 'Force 3D Secure', 'simple-secure-stripe' ),
		'type'        => 'checkbox',
		'value'       => 'yes',
		'default'     => 'no',
		'desc_tip'    => false,
		'description' => sprintf(
		/* translators: 1: open anchor tag, 2: close anchor tag. */
			__(
				'Stripe internally determines when 3D secure should be presented based on their SCA engine. If <strong>Force 3D Secure</strong> is enabled, 3D Secure will be forced for ALL credit card transactions. In test mode 3D secure only shows for %1$s3DS Test Cards%2$s regardless of this setting.',
				'simple-secure-stripe'
			), '<a target="_blank" href="https://stripe.com/docs/testing#regulatory-cards" rel="noopener noreferrer">', '</a>'
		),
	],
	'generic_error'     => [
		'title'       => __( 'Generic Errors', 'simple-secure-stripe' ),
		'type'        => 'checkbox',
		'default'     => 'no',
		'value'       => 'yes',
		'desc_tip'    => true,
		'description' => __( 'If enabled, credit card errors will be generic when presented to the customer. Merchants may prefer to not provide details on why a card was not accepted for security purposes.', 'simple-secure-stripe' ),
	],
	'cards'             => [
		'type'        => 'multiselect',
		'title'       => __( 'Credit Card Icons', 'simple-secure-stripe' ),
		'class'       => 'wc-enhanced-select stripe-accepted-cards',
		'default'     => [ 'amex', 'discover', 'visa', 'mastercard' ],
		'options'     => [
			'visa'            => __( 'Visa', 'simple-secure-stripe' ),
			'amex'            => __( 'Amex', 'simple-secure-stripe' ),
			'discover'        => __( 'Discover', 'simple-secure-stripe' ),
			'mastercard'      => __( 'MasterCard', 'simple-secure-stripe' ),
			'jcb'             => __( 'JCB', 'simple-secure-stripe' ),
			'maestro'         => __( 'Maestro', 'simple-secure-stripe' ),
			'diners'          => __( 'Diners Club', 'simple-secure-stripe' ),
			'china_union_pay' => __( 'Union Pay', 'simple-secure-stripe' ),
		],
		'desc_tip'    => true,
		'description' => __( 'The selected icons will show customers which credit card brands you accept.', 'simple-secure-stripe' ),
	],
	'form_title'        => [
		'type'  => 'title',
		'title' => __( 'Credit Card Form', 'simple-secure-stripe' ),
	],
	'form_type'         => [
		'title'       => __( 'Card Form', 'simple-secure-stripe' ),
		'type'        => 'select',
		'options'     => [
			'inline'  => __( 'Stripe inline form', 'simple-secure-stripe' ),
			'payment' => __( 'Stripe payment form', 'simple-secure-stripe' ),
			'custom'  => __( 'Custom form', 'simple-secure-stripe' ),
		],
		'default'     => 'payment',
		'desc_tip'    => true,
		'description' => __( 'The card form design that displays on payment pages.', 'simple-secure-stripe' ),
	],
	'theme'             => [
		'title'             => __( 'Theme', 'simple-secure-stripe' ),
		'type'              => 'select',
		'default'           => 'stripe',
		'options'           => [
			'stripe' => __( 'Default', 'simple-secure-stripe' ),
			'night'  => __( 'Night', 'simple-secure-stripe' ),
			'flat'   => __( 'Flat', 'simple-secure-stripe' ),
		],
		'desc_tip'          => true,
		'description'       => __( 'The theme option controls how the Stripe payment form looks.', 'simple-secure-stripe' ),
		'custom_attributes' => [ 'data-show-if' => [ 'form_type' => 'payment' ] ],
	],
	'custom_form'       => [
		'title'             => __( 'Custom Form', 'simple-secure-stripe' ),
		'type'              => 'select',
		'options'           => wp_list_pluck( sswps_get_custom_forms(), 'label' ),
		'default'           => 'bootstrap',
		'description'       => __( 'The design of the credit card form.', 'simple-secure-stripe' ),
		'desc_tip'          => true,
		'custom_attributes' => [ 'data-show-if' => [ 'form_type' => 'custom' ] ],
	],
	'postal_enabled'    => [
		'title'             => __( 'Postal Code', 'simple-secure-stripe' ),
		'type'              => 'checkbox',
		'default'           => 'no',
		'description'       => __(
			'If enabled, the CC form will show the postal code on the checkout page. If disabled, the billing field\'s postal code will be used. The postal code will show on the Add Payment Method page for security reasons.',
			'simple-secure-stripe'
		),
		'desc_tip'          => true,
		'custom_attributes' => [ 'data-show-if' => [ 'form_type' => 'custom' ] ],
	],
	'notice_location'   => [
		'title'       => __( 'Notices Location', 'simple-secure-stripe' ),
		'type'        => 'select',
		'default'     => 'acf',
		'options'     => [
			'acf'    => __( 'Above card form', 'simple-secure-stripe' ),
			'bcf'    => __( 'Below card form', 'simple-secure-stripe' ),
			'toc'    => __( 'Top of checkout page', 'simple-secure-stripe' ),
			'custom' => __( 'Custom css selector', 'simple-secure-stripe' ),
		],
		'desc_tip'    => true,
		'description' => __(
			'This option allows you to control the location of credit card form validation errors. If you select custom, then you can provide a custom css selector for where the notices appear.',
			'simple-secure-stripe'
		),
	],
	'notice_selector'   => [
		'title'             => __( 'Notices Selector', 'simple-secure-stripe' ),
		'type'              => 'text',
		'default'           => 'div.payment_method_sswps_cc',
		'desc_tip'          => true,
		'description'       => __( 'This is the css selector where the card validation notices will be prepended to.', 'simple-secure-stripe' ),
		'custom_attributes' => [ 'data-show-if' => [ 'notice_location' => 'custom' ] ],
	],
];
