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
	'desc'             => [
		'type'        => 'description',
		'description' => __(
			'The PaymentRequest gateway uses your customer\'s browser to render payment options like Google Pay and Microsoft Pay. You can either use the Google Pay gateway for example, or this gateway. The difference is this gateway uses Stripe\'s PaymentRequest Button rather than render a Google Pay specific button.',
			'simple-secure-stripe'
		),
	],
	'enabled'          => [
		'title'       => __( 'Enabled', 'simple-secure-stripe' ),
		'type'        => 'checkbox',
		'default'     => 'no',
		'value'       => 'yes',
		'desc_tip'    => true,
		'description' => __( 'If enabled, your site can accept Apple Pay payments through Stripe.', 'simple-secure-stripe' ),
	],
	'general_settings' => [
		'type'  => 'title',
		'title' => __( 'General Settings', 'simple-secure-stripe' ),
	],
	'title_text'       => [
		'type'        => 'text',
		'title'       => __( 'Title', 'simple-secure-stripe' ),
		'default'     => __( 'Browser Payments', 'simple-secure-stripe' ),
		'desc_tip'    => true,
		'description' => __( 'Title of the credit card gateway', 'simple-secure-stripe' ),
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
		'default'     => 'type_ending_in',
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
			'This option determines whether the customer\'s funds are captured immediately or authorized and can be captured at a later date.',
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
		'description' => __( 'This is the status of the order once payment is complete. If <b>Default</b> is selected, then WooCommerce will set the order status automatically based on internal logic which states if a product is virtual and downloadable then status is set to complete. Products that require shipping are set to Processing. Default is the recommended setting as it allows standard WooCommerce code to process the order status.', 'simple-secure-stripe' ),
	],
	'button_section'   => [
		'type'  => 'title',
		'title' => __( 'Button Settings', 'simple-secure-stripe' ),
	],
	'button_type'      => [
		'type'        => 'select',
		'title'       => __( 'Type', 'simple-secure-stripe' ),
		'options'     => [
			'default' => __( 'default', 'simple-secure-stripe' ),
			// 'donate' => __ ( 'donate', 'simple-secure-stripe' ),
			'buy'     => __( 'buy', 'simple-secure-stripe' ),
		],
		'default'     => 'buy',
		'desc_tip'    => true,
		'description' => __( 'This defines the type of button that will display.', 'simple-secure-stripe' ),
	],
	'button_theme'     => [
		'type'        => 'select',
		'title'       => __( 'Theme', 'simple-secure-stripe' ),
		'options'     => [
			'dark'          => __( 'dark', 'simple-secure-stripe' ),
			'light'         => __( 'light', 'simple-secure-stripe' ),
			'light-outline' => __( 'light-outline', 'simple-secure-stripe' ),
		],
		'default'     => 'dark',
		'desc_tip'    => true,
		'description' => __( 'This defines the color scheme for the button.', 'simple-secure-stripe' ),
	],
	'button_height'    => [
		'type'        => 'text',
		'title'       => __( 'Height', 'simple-secure-stripe' ),
		'default'     => '40',
		'desc_tip'    => true,
		'description' => __( 'The height of the button. Max height is 64', 'simple-secure-stripe' ),
	],
];
