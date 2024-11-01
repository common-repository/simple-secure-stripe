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
	'desc'              => [
		'type'        => 'description',
		'description' => sprintf(
			/* translators: 1: open anchor tag, 2: close anchor tag. */
			__( 'For US customers only. Read through our %1$sdocumentation%2$s to configure ACH payments', 'simple-secure-stripe' ),
			'<a target="_blank" href="https://docs.paymentplugins.com/sswps/config/#/sswps_ach" rel="noopener noreferrer">',
			'</a>'
		),
	],
	'enabled'           => [
		'title'       => __( 'Enabled', 'simple-secure-stripe' ),
		'type'        => 'checkbox',
		'default'     => 'no',
		'value'       => 'yes',
		'desc_tip'    => true,
		'description' => __( 'If enabled, your site can accept ACH payments through Stripe.', 'simple-secure-stripe' ),
	],
	'general_settings'  => [
		'type'  => 'title',
		'title' => __( 'General Settings', 'simple-secure-stripe' ),
	],
	'title_text'        => [
		'type'        => 'text',
		'title'       => __( 'Title', 'simple-secure-stripe' ),
		'default'     => __( 'ACH Payment', 'simple-secure-stripe' ),
		'desc_tip'    => true,
		'description' => __( 'Title of the ACH gateway' ),
	],
	'description'       => [
		'title'       => __( 'Description', 'simple-secure-stripe' ),
		'type'        => 'text',
		'default'     => '',
		'description' => __( 'Leave blank if you don\'t want a description to show for the gateway.', 'simple-secure-stripe' ),
		'desc_tip'    => true,
	],
	'order_button_text' => [
		'title'       => __( 'Order Button Text', 'simple-secure-stripe' ),
		'type'        => 'text',
		'default'     => __( 'Bank Payment', 'simple-secure-stripe' ),
		'description' => __(
			'The text on the Place Order button that displays when the gateway is selected on the checkout page.',
			'simple-secure-stripe'
		),
		'desc_tip'    => true,
	],
	'business_name'     => [
		'type'        => 'text',
		'title'       => __( 'Business Name', 'simple-secure-stripe' ),
		'default'     => get_bloginfo( 'name' ),
		'description' => __( 'The name that appears in the ACH mandate.', 'simple-secure-stripe' ),
		'desc_tip'    => true,
	],
	'method_format'     => [
		'title'       => __( 'ACH Display', 'simple-secure-stripe' ),
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
	'fee'               => [
		'title'       => __( 'ACH Fee', 'simple-secure-stripe' ),
		'type'        => 'ach_fee',
		'class'       => '',
		'value'       => '',
		'default'     => [
			'type'    => 'none',
			'taxable' => 'no',
			'value'   => '0',
		],
		'options'     => [
			'none'    => __( 'None', 'simple-secure-stripe' ),
			'amount'  => __( 'Amount', 'simple-secure-stripe' ),
			'percent' => __( 'Percentage', 'simple-secure-stripe' ),
		],
		'desc_tip'    => true,
		'description' => __(
			'You can assign a fee to the order for ACH payments. Amount is a static amount and percentage is a percentage of the cart amount.',
			'simple-secure-stripe'
		),
	],
];
