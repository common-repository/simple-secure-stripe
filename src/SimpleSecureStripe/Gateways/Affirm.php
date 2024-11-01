<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

class Affirm extends Abstract_Local_Payment {

	use Payment\Traits\Local_Intent;

	protected string $payment_method_type = 'affirm';

	public $max_amount = 30001;

	public function __construct() {
		$this->local_payment_type = 'affirm';
		$this->currencies         = [ 'USD', 'CAD' ];
		$this->countries          = [ 'US', 'CA' ];
		$this->limited_countries  = [ 'US', 'CA' ];
		$this->id                 = 'sswps_affirm';
		$this->tab_title          = __( 'Affirm', 'simple-secure-stripe' );
		$this->method_title       = __( 'Affirm', 'simple-secure-stripe' );
		$this->method_description = __( 'Affirm gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = App::get( Plugin::class )->assets_url( 'img/affirm.svg' );
		parent::__construct();
		$this->template_name = 'affirm.php';
	}

	public function init_supports() {
		parent::init_supports();
		$this->supports[] = 'sswps_cart_checkout';
		$this->supports[] = 'sswps_product_checkout';
		$this->supports[] = 'sswps_mini_cart_checkout';
	}

	public function get_order_button_text( $text ) {
		return __( 'Complete Order', 'simple-secure-stripe' );
	}

	public function get_payment_method_requirements() {
		return apply_filters( 'sswps/affirm_get_required_payments', [
			'USD' => [ 'US' ],
			'CAD' => [ 'CA' ],
		] );
	}

	public function is_local_payment_available() {
		if ( parent::is_local_payment_available() ) {
			return WC()->cart && $this->get_order_total() >= 50;
		}

		return false;
	}

	public function cart_fields() {
		$this->enqueue_frontend_scripts( 'cart' );
		$this->output_display_items( 'cart' );
	}

	public function product_fields() {
		$this->enqueue_frontend_scripts( 'product' );
		$this->output_display_items( 'product' );
	}

	/**
	 * @inheritDoc
	 */
	public function register_assets() {
		parent::register_assets();

		Assets\Asset::register( 'sswps-affirm-checkout', 'dist/affirm-messaging.js' )
			->add_to_group( 'sswps-local-payment' )
			->add_to_group( 'sswps-local-payment-cart' )
			->add_to_group( 'sswps-local-payment-category' )
			->add_to_group( 'sswps-local-payment-product' )
			->set_dependencies( [
				'sswps-script',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_affirm_checkout_params',
				$this->get_localized_params( 'checkout' )
			)
			->add_localize_script(
				'sswps_affirm_cart_params',
				$this->get_localized_params( 'cart' )
			)
			->add_localize_script(
				'sswps_affirm_product_params',
				$this->get_localized_params( 'product' )
			)
			->set_condition( [ $this, 'is_available' ] );

		App::get( Assets\Data::class )->add( $this->id, [
			'messageOptions'      => [
				'logoColor' => $this->get_option( "shop_logo_color", 'primary' ),
				'fontColor' => $this->get_option( "shop_font_color", 'black' ),
				'fontSize'  => $this->get_option( "shop_font_size", '1em' ),
				'textAlign' => $this->get_option( "shop_text_align", 'start' ),
			],
			'supportedCurrencies' => $this->currencies,
		] );
	}

	public function get_local_payment_settings() {
		return array_merge( parent::get_local_payment_settings(), [
			'charge_type'         => [
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
			'payment_sections'    => [
				'type'        => 'multiselect',
				'title'       => __( 'Payment Sections', 'simple-secure-stripe' ),
				'class'       => 'wc-enhanced-select',
				'options'     => [
					'product' => __( 'Product Page', 'simple-secure-stripe' ),
					'cart'    => __( 'Cart Page', 'simple-secure-stripe' ),
					'shop'    => __( 'Shop/Category Page', 'simple-secure-stripe' ),
				],
				'default'     => [ 'cart' ],
				'desc_tip'    => true,
				'description' => __(
					'These are the sections where the Affirm messaging will be enabled.',
					'simple-secure-stripe'
				),
			],
			'checkout_styling'    => [
				'type'  => 'title',
				'title' => __( 'Checkout Message Styling', 'simple-secure-stripe' ),
			],
			'checkout_logo_color' => [
				'title'       => __( 'Logo Color', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'primary',
				'options'     => [
					'primary' => __( 'Primary', 'simple-secure-stripe' ),
					'black'   => __( 'Black', 'simple-secure-stripe' ),
					'white'   => __( 'White', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'The color of the Affirm logo that appears in the messaging.', 'simple-secure-stripe' ),
			],
			'checkout_font_color' => [
				'title'       => __( 'Font Color', 'simple-secure-stripe' ),
				'type'        => 'text',
				'default'     => 'black',
				'desc_tip'    => true,
				'description' => __( 'The color of the Affirm message font. Valid values are hex color codes or values like red, blue, green, etc.', 'simple-secure-stripe' ),
			],
			'checkout_font_size'  => [
				'title'       => __( 'Font Size', 'simple-secure-stripe' ),
				'type'        => 'text',
				'default'     => '1em',
				'desc_tip'    => true,
				'description' => __( 'The size of the Affirm message font. Valid values are in px, em, rem', 'simple-secure-stripe' ),
			],
			'checkout_text_align' => [
				'title'       => __( 'Font Alignment', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'start',
				'options'     => [
					'start'  => __( 'Start', 'simple-secure-stripe' ),
					'end'    => __( 'End', 'simple-secure-stripe' ),
					'center' => __( 'Center', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'The alignment of the Affirm message.', 'simple-secure-stripe' ),
			],
			'cart_styling'        => [
				'type'  => 'title',
				'title' => __( 'Cart Message Styling', 'simple-secure-stripe' ),
			],
			'cart_logo_color'     => [
				'title'       => __( 'Logo Color', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'primary',
				'options'     => [
					'primary' => __( 'Primary', 'simple-secure-stripe' ),
					'black'   => __( 'Black', 'simple-secure-stripe' ),
					'white'   => __( 'White', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'The color of the Affirm logo that appears in the messaging.', 'simple-secure-stripe' ),
			],
			'cart_font_color'     => [
				'title'       => __( 'Font Color', 'simple-secure-stripe' ),
				'type'        => 'text',
				'default'     => 'black',
				'desc_tip'    => true,
				'description' => __( 'The color of the Affirm message font. Valid values are hex color codes or values like red, blue, green, etc.', 'simple-secure-stripe' ),
			],
			'cart_font_size'      => [
				'title'       => __( 'Font Size', 'simple-secure-stripe' ),
				'type'        => 'text',
				'default'     => '1em',
				'desc_tip'    => true,
				'description' => __( 'The size of the Affirm message font. Valid values are in px, em, rem', 'simple-secure-stripe' ),
			],
			'cart_text_align'     => [
				'title'       => __( 'Font Alignment', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'start',
				'options'     => [
					'start'  => __( 'Start', 'simple-secure-stripe' ),
					'end'    => __( 'End', 'simple-secure-stripe' ),
					'center' => __( 'Center', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'The alignment of the Affirm message.', 'simple-secure-stripe' ),
			],
			'cart_location'       => [
				'title'       => __( 'Message Location', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'below_total',
				'options'     => [
					'below_total'           => __( 'Below Total', 'simple-secure-stripe' ),
					'below_checkout_button' => __( 'Below Checkout Button', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'This option controls the location in which the messaging for the payment method will appear.', 'simple-secure-stripe' ),
			],
			'product_styling'     => [
				'type'  => 'title',
				'title' => __( 'Product Message Styling', 'simple-secure-stripe' ),
			],
			'product_logo_color'  => [
				'title'       => __( 'Logo Color', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'primary',
				'options'     => [
					'primary' => __( 'Primary', 'simple-secure-stripe' ),
					'black'   => __( 'Black', 'simple-secure-stripe' ),
					'white'   => __( 'White', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'The color of the Affirm logo that appears in the messaging.', 'simple-secure-stripe' ),
			],
			'product_font_color'  => [
				'title'       => __( 'Font Color', 'simple-secure-stripe' ),
				'type'        => 'text',
				'default'     => 'black',
				'desc_tip'    => true,
				'description' => __( 'The color of the Affirm message font. Valid values are hex color codes or values like red, blue, green, etc.', 'simple-secure-stripe' ),
			],
			'product_font_size'   => [
				'title'       => __( 'Font Size', 'simple-secure-stripe' ),
				'type'        => 'text',
				'default'     => '1em',
				'desc_tip'    => true,
				'description' => __( 'The size of the Affirm message font. Valid values are in px, em, rem', 'simple-secure-stripe' ),
			],
			'product_text_align'  => [
				'title'       => __( 'Font Alignment', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'start',
				'options'     => [
					'start'  => __( 'Start', 'simple-secure-stripe' ),
					'end'    => __( 'End', 'simple-secure-stripe' ),
					'center' => __( 'Center', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'The alignment of the Affirm message.', 'simple-secure-stripe' ),
			],
			'product_location'    => [
				'title'       => __( 'Message Location', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'below_price',
				'options'     => [
					'above_price'       => __( 'Above Price', 'simple-secure-stripe' ),
					'below_price'       => __( 'Below Price', 'simple-secure-stripe' ),
					'below_add_to_cart' => __( 'Below Add to Cart', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'This option controls the location in which the messaging for the payment method will appear.', 'simple-secure-stripe' ),
			],
			'shop_styling'        => [
				'type'  => 'title',
				'title' => __( 'Shop/Category Message Styling', 'simple-secure-stripe' ),
			],
			'shop_logo_color'     => [
				'title'       => __( 'Logo Color', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'primary',
				'options'     => [
					'primary' => __( 'Primary', 'simple-secure-stripe' ),
					'black'   => __( 'Black', 'simple-secure-stripe' ),
					'white'   => __( 'White', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'The color of the Affirm logo that appears in the messaging.', 'simple-secure-stripe' ),
			],
			'shop_font_color'     => [
				'title'       => __( 'Font Color', 'simple-secure-stripe' ),
				'type'        => 'text',
				'default'     => 'black',
				'desc_tip'    => true,
				'description' => __( 'The color of the Affirm message font. Valid values are hex color codes or values like red, blue, green, etc.', 'simple-secure-stripe' ),
			],
			'shop_font_size'      => [
				'title'       => __( 'Font Size', 'simple-secure-stripe' ),
				'type'        => 'text',
				'default'     => '1em',
				'desc_tip'    => true,
				'description' => __( 'The size of the Affirm message font. Valid values are in px, em, rem', 'simple-secure-stripe' ),
			],
			'shop_text_align'     => [
				'title'       => __( 'Font Alignment', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'start',
				'options'     => [
					'start'  => __( 'Start', 'simple-secure-stripe' ),
					'end'    => __( 'End', 'simple-secure-stripe' ),
					'center' => __( 'Center', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'The alignment of the Affirm message.', 'simple-secure-stripe' ),
			],
			'shop_location'       => [
				'title'       => __( 'Shop/Category Location', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'below_price',
				'options'     => [
					'below_price'       => __( 'Below Price', 'simple-secure-stripe' ),
					'below_add_to_cart' => __( 'Below Add to Cart', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'This option controls the location in which the messaging for the payment method will appear.', 'simple-secure-stripe' ),
			],
		] );
	}

	public function get_icon() {
		return '<div id="sswps-affirm-message-container"></div>';
	}

	public function get_localized_params( $context = 'checkout' ) {
		return array_merge( parent::get_localized_params(), [
			'messageOptions'      => [
				'logoColor' => $this->get_option( "{$context}_logo_color", 'primary' ),
				'fontColor' => $this->get_option( "{$context}_font_color", 'black' ),
				'fontSize'  => $this->get_option( "{$context}_font_size", '1em' ),
				'textAlign' => $this->get_option( "{$context}_text_align", 'start' ),
			],
			'supportedCurrencies' => $this->currencies,
		] );
	}

	public function get_payment_description() {
		$desc = parent::get_payment_description();

		return $desc . ' ' . sprintf(
			/* translators: 1 - min amount, 2 - max amount */
				__( 'and cart/product total is between %1$s and %2$s.', 'simple-secure-stripe' ),
				wc_price( 50, [ 'currency' => 'USD' ] ),
				wc_price( 30000, [ 'currency' => 'USD' ] )
			);
	}

	public function validate_local_payment_available( $currency, $billing_country, $total ) {
		$requirements    = $this->get_payment_method_requirements();
		$account_country = App::get( Settings\Account::class )->get_account_country( sswps_mode() );
		// Is this an unsupported currency?
		if ( ! isset( $requirements[ $currency ] ) ) {
			return false;
		}

		$countries = $requirements[ $currency ];

		/**
		 * Validate that the $billing_country matches the Stripe account's registered country
		 * and the $billing_country is in the array of $countries.
		 */
		return $billing_country === $account_country && in_array( $billing_country, $countries, true );
	}

}