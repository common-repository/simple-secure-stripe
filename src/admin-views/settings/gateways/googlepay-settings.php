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
	'desc1'            => [
		'type'        => 'description',
		'description' => '<a target="_blank" href="https://pay.google.com/business/console" rel="noopener noreferrer">' . __( 'Google Pay Business Console', 'simple-secure-stripe' ) . '</a>',
	],
	'desc2'            => [
		'type'        => 'description',
		'description' => '<a target="_blank" href="https://docs.paymentplugins.com/sswps/config/#/sswps_googlepay?id=testing" rel="noopener noreferrer">' . __( 'Testing Google Pay', 'simple-secure-stripe' ) . '</a>',
	],
	'desc3'            => [
		'type'        => 'description',
		'description' => __(
			'When test mode is enabled, Google Pay will work without a merchant ID, allowing you to capture the necessary screenshots the Google API team needs to approve your integration request.',
			'simple-secure-stripe'
		),
	],
	'desc4'            => [
		'type'        => 'description',
		'description' => sprintf(
		/* translators: 1: open anchor tag, 2: close anchor tag. */
			__(
				'If you don\'t want to request a Google Merchant ID, you can use the %1$sPayment Request Gateway%2$s which has a Google Pay integration through Stripe via the Chrome browser.',
				'simple-secure-stripe'
			),
			'<a target="_blank" href="' .
			esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=sswps_payment_request' ) ) . '" rel="noopener noreferrer">',
			'</a>'
		),
	],
	'enabled'          => [
		'title'       => __( 'Enabled', 'simple-secure-stripe' ),
		'type'        => 'checkbox',
		'default'     => 'no',
		'value'       => 'yes',
		'desc_tip'    => true,
		'description' => __( 'If enabled, your site can accept Google Pay payments through Stripe.', 'simple-secure-stripe' ),
	],
	'general_settings' => [
		'type'  => 'title',
		'title' => __( 'General Settings', 'simple-secure-stripe' ),
	],
	'merchant_id'      => [
		'type'        => 'text',
		'title'       => __( 'Merchant ID', 'simple-secure-stripe' ),
		'default'     => '',
		'description' => __(
			'Your Google Merchant ID is given to you by the Google API team once you register for Google Pay. While testing in TEST mode you can leave this value blank and Google Pay will work.',
			'simple-secure-stripe'
		),
	],
	'title_text'       => [
		'type'        => 'text',
		'title'       => __( 'Title', 'simple-secure-stripe' ),
		'default'     => __( 'Google Pay', 'simple-secure-stripe' ),
		'desc_tip'    => true,
		'description' => __( 'Title of the credit card gateway' ),
	],
	'description'      => [
		'title'       => __( 'Description', 'simple-secure-stripe' ),
		'type'        => 'text',
		'default'     => '',
		'description' => __( 'Leave blank if you don\'t want a description to show for the gateway.', 'simple-secure-stripe' ),
		'desc_tip'    => true,
	],
	'method_format'    => [
		'title'       => __( 'Credit Card Display', 'simple-secure-stripe' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => wp_list_pluck( $this->get_payment_method_formats(), 'example' ),
		'value'       => '',
		'default'     => 'gpay_name',
		'desc_tip'    => true,
		'description' => __(
			'This option allows you to customize how the credit card will display for your customers on orders, subscriptions, etc.',
			'simple-secure-stripe'
		),
	],
	'charge_type'      => [
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
			'This option determines whether the customer\'s funds are capture immediately or authorized and can be captured at a later date.',
			'simple-secure-stripe'
		),
	],
	'payment_sections' => [
		'type'        => 'multiselect',
		'title'       => __( 'Payment Sections', 'simple-secure-stripe' ),
		'class'       => 'wc-enhanced-select',
		'options'     => [
			'product'         => __( 'Product Page', 'simple-secure-stripe' ),
			'cart'            => __( 'Cart Page', 'simple-secure-stripe' ),
			'mini_cart'       => __( 'Mini Cart', 'simple-secure-stripe' ),
			'checkout_banner' => __( 'Top of Checkout', 'simple-secure-stripe' ),
		],
		'default'     => [ 'product', 'cart' ],
		'description' => $this->get_payment_section_description(),
	],
	'order_status'     => [
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
	'merchant_name'    => [
		'type'        => 'text',
		'title'       => __( 'Merchant Name', 'simple-secure-stripe' ),
		'default'     => get_bloginfo( 'name' ),
		'description' => __( 'The name of your business as it appears on the Google Pay payment sheet.', 'simple-secure-stripe' ),
		'desc_tip'    => true,
	],
	'icon'             => [
		'title'       => __( 'Icon', 'simple-secure-stripe' ),
		'type'        => 'select',
		'options'     => [
			'googlepay_round_outline' => __( 'With Rounded Outline', 'simple-secure-stripe' ),
			'googlepay_outline'       => __( 'With Outline', 'simple-secure-stripe' ),
			'googlepay_standard'      => __( 'Standard', 'simple-secure-stripe' ),
		],
		'default'     => 'googlepay_round_outline',
		'desc_tip'    => true,
		'description' => __(
			'This is the icon style that appears next to the gateway on the checkout page. Google\'s API team typically requires the With Outline option on the checkout page for branding purposes.',
			'simple-secure-stripe'
		),
	],
	'button_section'   => [
		'type'  => 'title',
		'title' => __( 'Button Options', 'simple-secure-stripe' ),
	],
	'button_color'     => [
		'title'       => __( 'Button Color', 'simple-secure-stripe' ),
		'type'        => 'select',
		'class'       => 'gpay-button-option button-color',
		'options'     => [
			'black' => __( 'Black', 'simple-secure-stripe' ),
			'white' => __( 'White', 'simple-secure-stripe' ),
		],
		'default'     => 'black',
		'description' => __( 'The button color of the GPay button.', 'simple-secure-stripe' ),
	],
	'button_style'     => [
		'title'       => __( 'Button Style', 'simple-secure-stripe' ),
		'type'        => 'select',
		'class'       => 'gpay-button-option button-style',
		'options'     => [
			'buy'       => __( 'Buy', 'simple-secure-stripe' ),
			'plain'     => __( 'Plain', 'simple-secure-stripe' ),
			'checkout'  => __( 'Checkout', 'simple-secure-stripe' ),
			'order'     => __( 'Order', 'simple-secure-stripe' ),
			'pay'       => __( 'Pay', 'simple-secure-stripe' ),
			'subscribe' => __( 'subscribe', 'simple-secure-stripe' ),
		],
		'default'     => 'buy',
		'description' => __( 'The button style of the GPay button.', 'simple-secure-stripe' ),
	],
	'button_shape'     => [
		'title'       => __( 'Button Shape', 'simple-secure-stripe' ),
		'type'        => 'select',
		'class'       => 'gpay-button-option gpay-button-shape',
		'default'     => 'rect',
		'options'     => [
			'rect' => __( 'Rectangle', 'simple-secure-stripe' ),
			'pill' => __( 'Pill shape', 'simple-secure-stripe' ),
		],
		'description' => __( 'The button shape', 'simple-secure-stripe' ),
	],
	'button_render'    => [
		'type'        => 'button_demo',
		'title'       => __( 'Button Design', 'simple-secure-stripe' ),
		'id'          => 'gpay-button',
		'description' => __( 'If you can\'t see the Google Pay button, try switching to a Chrome browser.', 'simple-secure-stripe' ),
	],
];
