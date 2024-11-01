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
	'desc'                 => [
		'type'        => 'description',
		'description' => sprintf(
			'<span class="sswps-register-domain"><button class="button button-secondary api-register-domain">%s</button></span>',
			__( 'Register Domain', 'simple-secure-stripe' ),
		),
	],
	'desc2' => [
		'type' => 'description',
		'description' => sprintf(
			/* translators: 1: open anchor tag, 2: close anchor tag. */
			__( 'This plugin attemps to add the domain association file to your server automatically when you click the Register Domain button. If that fails due to file permssions, you must add the <strong>%1$s.well-known/apple-developer-merchantid-domain-association%2$s</strong> file to your domain  and register your domain within the Stripe Dashboard.', 'simple-secure-stripe' ),
			'<a href="https://stripe.com/files/apple-pay/apple-developer-merchantid-domain-association">',
			'</a>'
		),
	],
	'desc3' => [
		'type' => 'description',
		'description' => __( 'In order for Apple Pay to display, you must test with an iOS device and have a payment method saved in the Apple Wallet.', 'simple-secure-stripe' ),
	],
	'enabled'              => [
		'title'       => __( 'Enabled', 'simple-secure-stripe' ),
		'type'        => 'checkbox',
		'default'     => 'no',
		'value'       => 'yes',
		'desc_tip'    => true,
		'description' => __( 'If enabled, your site can accept Apple Pay payments through Stripe.', 'simple-secure-stripe' ),
	],
	'general_settings'     => [
		'type'  => 'title',
		'title' => __( 'General Settings', 'simple-secure-stripe' ),
	],
	'title_text'           => [
		'type'        => 'text',
		'title'       => __( 'Title', 'simple-secure-stripe' ),
		'default'     => __( 'Apple Pay', 'simple-secure-stripe' ),
		'desc_tip'    => true,
		'description' => __( 'Title of the Apple Pay gateway', 'simple-secure-stripe' ),
	],
	'description'          => [
		'title'       => __( 'Description', 'simple-secure-stripe' ),
		'type'        => 'text',
		'default'     => '',
		'description' => __( 'Leave blank if you don\'t want a description to show for the gateway.', 'simple-secure-stripe' ),
		'desc_tip'    => true,
	],
	'method_format'        => [
		'title'       => __( 'Credit Card Display', 'simple-secure-stripe' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => wp_list_pluck( $this->get_payment_method_formats(), 'example' ),
		'value'       => '',
		'default'     => 'type_ending_in',
		'desc_tip'    => true,
		'description' => __( 'This option allows you to customize how the credit card will display for your customers on orders, subscriptions, etc.' ),
	],
	'charge_type'          => [
		'type'        => 'select',
		'title'       => __( 'Charge Type', 'simple-secure-stripe' ),
		'default'     => 'capture',
		'class'       => 'wc-enhanced-select',
		'options'     => [
			'capture'   => __( 'Capture', 'simple-secure-stripe' ),
			'authorize' => __( 'Authorize', 'simple-secure-stripe' ),
		],
		'desc_tip'    => true,
		'description' => __( 'This option determines whether the customer\'s funds are captured immediately or authorized and can be captured at a later date.', 'simple-secure-stripe' ),
	],
	'payment_sections'     => [
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
	'order_status'         => [
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
	'button_section'       => [
		'type'  => 'title',
		'title' => __( 'Button Settings', 'simple-secure-stripe' ),
	],
	'button_style'         => [
		'type'        => 'select',
		'title'       => __( 'Button Design', 'simple-secure-stripe' ),
		'class'       => 'wc-enhanced-select',
		'default'     => 'apple-pay-button-black',
		'options'     => [
			'apple-pay-button-black'           => __( 'Black Button', 'simple-secure-stripe' ),
			'apple-pay-button-white-with-line' => __( 'White With Black Line', 'simple-secure-stripe' ),
			'apple-pay-button-white'           => __( 'White Button', 'simple-secure-stripe' ),
		],
		'description' => __( 'This is the style for all Apple Pay buttons presented on your store.', 'simple-secure-stripe' ),
	],
	'button_type_checkout' => [
		'title'   => __( 'Checkout button type', 'simple-secure-stripe' ),
		'type'    => 'select',
		'options' => [
			'plain'     => __( 'Standard Button', 'simple-secure-stripe' ),
			'buy'       => __( 'Buy with Apple Pay', 'simple-secure-stripe' ),
			'check-out' => __( 'Checkout with Apple Pay', 'simple-secure-stripe' ),
		],
		'default' => 'plain',
	],
	'button_type_cart'     => [
		'title'   => __( 'Cart button type', 'simple-secure-stripe' ),
		'type'    => 'select',
		'options' => [
			'plain'     => __( 'Standard Button', 'simple-secure-stripe' ),
			'buy'       => __( 'Buy with Apple Pay', 'simple-secure-stripe' ),
			'check-out' => __( 'Checkout with Apple Pay', 'simple-secure-stripe' ),
		],
		'default' => 'plain',
	],
	'button_type_product'  => [
		'title'   => __( 'Product button type', 'simple-secure-stripe' ),
		'type'    => 'select',
		'options' => [
			'plain'     => __( 'Standard Button', 'simple-secure-stripe' ),
			'buy'       => __( 'Buy with Apple Pay', 'simple-secure-stripe' ),
			'check-out' => __( 'Checkout with Apple Pay', 'simple-secure-stripe' ),
		],
		'default' => 'buy',
	],
];
