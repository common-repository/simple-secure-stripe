<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Tokens;

/**
 * Class Afterpay
 *
 * @since   1.0.0
 * @package Stripe/Gateways
 */
class Afterpay extends Abstract_Local_Payment {

	use Payment\Traits\Local_Intent;

	protected string $payment_method_type = 'afterpay_clearpay';

	public function __construct() {
		$this->local_payment_type = 'afterpay_clearpay';
		$this->currencies         = [ 'AUD', 'CAD', 'NZD', 'GBP', 'USD', 'EUR' ];
		$this->countries          = [ 'AU', 'CA', 'NZ', 'GB', 'US', 'FR', 'ES' ];
		$this->id                 = 'sswps_afterpay';
		$this->tab_title          = __( 'Afterpay', 'simple-secure-stripe' );
		$this->method_title       = __( 'Afterpay', 'simple-secure-stripe' );
		$this->method_description = __( 'Afterpay gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		$this->icon               = '';
		parent::__construct();
		$this->template_name = 'afterpay.php';
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

	public function get_local_payment_settings() {
		$settings = wp_parse_args( [
			'charge_type'                 => [
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
			'payment_sections'            => [
				'type'        => 'multiselect',
				'title'       => __( 'Payment Sections', 'simple-secure-stripe' ),
				'class'       => 'wc-enhanced-select',
				'options'     => [
					'product'   => __( 'Product Page', 'simple-secure-stripe' ),
					'cart'      => __( 'Cart Page', 'simple-secure-stripe' ),
					'mini_cart' => __( 'Mini Cart', 'simple-secure-stripe' ),
					'shop'      => __( 'Shop/Category Page', 'simple-secure-stripe' ),
				],
				'default'     => [ 'product', 'cart' ],
				'description' => __(
					'These are the additional sections where the Afterpay messaging will be enabled. You can control individual products via the Edit product page.',
					'simple-secure-stripe'
				),
			],
			'hide_ineligible'             => [
				'title'       => __( 'Hide If Ineligible', 'simple-secure-stripe' ),
				'type'        => 'checkbox',
				'value'       => 'yes',
				'default'     => 'no',
				'desc_tip'    => true,
				'description' => __( 'If enabled, Afterpay won\'t show when the products in the cart are not eligible.', 'simple-secure-stripe' ),
			],
			'checkout_styling'            => [
				'type'  => 'title',
				'title' => __( 'Checkout Page Styling', 'simple-secure-stripe' ),
			],
			'icon_checkout'               => [
				'title'       => __( 'Icon', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'black-on-mint',
				'options'     => [
					'black-on-mint'  => __( 'Black on mint', 'simple-secure-stripe' ),
					'black-on-white' => __( 'Black on white', 'simple-secure-stripe' ),
					'mint-on-black'  => __( 'Mint on black', 'simple-secure-stripe' ),
					'white-on-black' => __( 'White on black', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'This is the icon style that appears next to the gateway on the checkout page.', 'simple-secure-stripe' ),
			],
			'intro_text_checkout'         => [
				'title'   => __( 'Intro text', 'simple-secure-stripe' ),
				'type'    => 'select',
				'default' => 'In',
				'options' => [
					'In'     => 'In',
					'Or'     => 'Or',
					'Pay'    => 'Pay',
					'Pay in' => 'Pay in',
				],
			],
			'modal_link_style_checkout'   => [
				'title'       => __( 'Modal link style', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'circled-info-icon',
				'options'     => [
					'more-info-text'    => __( 'More info text', 'simple-secure-stripe' ),
					'circled-info-icon' => __( 'Circled info icon', 'simple-secure-stripe' ),
					'learn-more-text'   => __( 'Learn more text', 'simple-secure-stripe' ),
				],
				'description' => __( 'This is the style of the Afterpay info link.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'modal_theme_checkout'        => [
				'title'       => __( 'Modal link style', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'mint',
				'options'     => [
					'mint'  => __( 'Mint', 'simple-secure-stripe' ),
					'white' => __( 'White', 'simple-secure-stripe' ),
				],
				'description' => __( 'This is the theme color for the Afterpay info modal.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'show_interest_free_checkout' => [
				'type'        => 'checkbox',
				'title'       => __( 'Show interest free', 'simple-secure-stripe' ),
				'default'     => 'no',
				'value'       => 'yes',
				'description' => __( 'If enabled, the Afterpay message will contain the interest free text.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'cart_styling'                => [
				'type'  => 'title',
				'title' => __( 'Cart Page Styling', 'simple-secure-stripe' ),
			],
			'icon_cart'                   => [
				'title'       => __( 'Icon', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'black-on-mint',
				'options'     => [
					'black-on-mint'  => __( 'Black on mint', 'simple-secure-stripe' ),
					'black-on-white' => __( 'Black on white', 'simple-secure-stripe' ),
					'mint-on-black'  => __( 'Mint on black', 'simple-secure-stripe' ),
					'white-on-black' => __( 'White on black', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'This is the icon style that appears next to the gateway on the checkout page.', 'simple-secure-stripe' ),
			],
			'intro_text_cart'             => [
				'title'   => __( 'Intro text', 'simple-secure-stripe' ),
				'type'    => 'select',
				'default' => 'Or',
				'options' => [
					'In'     => 'In',
					'Or'     => 'Or',
					'Pay'    => 'Pay',
					'Pay in' => 'Pay in',
				],
			],
			'modal_link_style_cart'       => [
				'title'       => __( 'Modal link style', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'circled-info-icon',
				'options'     => [
					'more-info-text'    => __( 'More info text', 'simple-secure-stripe' ),
					'circled-info-icon' => __( 'Circled info icon', 'simple-secure-stripe' ),
					'learn-more-text'   => __( 'Learn more text', 'simple-secure-stripe' ),
				],
				'description' => __( 'This is the style of the Afterpay info link.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'modal_theme_cart'            => [
				'title'       => __( 'Modal link style', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'mint',
				'options'     => [
					'mint'  => __( 'Mint', 'simple-secure-stripe' ),
					'white' => __( 'White', 'simple-secure-stripe' ),
				],
				'description' => __( 'This is the theme color for the Afterpay info modal.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'show_interest_free_cart'     => [
				'type'        => 'checkbox',
				'title'       => __( 'Show interest free', 'simple-secure-stripe' ),
				'default'     => 'no',
				'value'       => 'yes',
				'description' => __( 'If enabled, the Afterpay message will contain the interest free text.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'cart_location'               => [
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
			'product_styling'             => [
				'type'  => 'title',
				'title' => __( 'Product Page Styling', 'simple-secure-stripe' ),
			],
			'icon_product'                => [
				'title'       => __( 'Icon', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'black-on-mint',
				'options'     => [
					'black-on-mint'  => __( 'Black on mint', 'simple-secure-stripe' ),
					'black-on-white' => __( 'Black on white', 'simple-secure-stripe' ),
					'mint-on-black'  => __( 'Mint on black', 'simple-secure-stripe' ),
					'white-on-black' => __( 'White on black', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'This is the icon style that appears next to the gateway on the checkout page.', 'simple-secure-stripe' ),
			],
			'intro_text_product'          => [
				'title'   => __( 'Intro text', 'simple-secure-stripe' ),
				'type'    => 'select',
				'default' => 'Pay in',
				'options' => [
					'In'     => 'In',
					'Or'     => 'Or',
					'Pay'    => 'Pay',
					'Pay in' => 'Pay in',
				],
			],
			'modal_link_style_product'    => [
				'title'       => __( 'Modal link style', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'circled-info-icon',
				'options'     => [
					'more-info-text'    => __( 'More info text', 'simple-secure-stripe' ),
					'circled-info-icon' => __( 'Circled info icon', 'simple-secure-stripe' ),
					'learn-more-text'   => __( 'Learn more text', 'simple-secure-stripe' ),
				],
				'description' => __( 'This is the style of the Afterpay info link.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'modal_theme_product'         => [
				'title'       => __( 'Modal link style', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'mint',
				'options'     => [
					'mint'  => __( 'Mint', 'simple-secure-stripe' ),
					'white' => __( 'White', 'simple-secure-stripe' ),
				],
				'description' => __( 'This is the theme color for the Afterpay info modal.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'show_interest_free_product'  => [
				'type'        => 'checkbox',
				'title'       => __( 'Show interest free', 'simple-secure-stripe' ),
				'default'     => 'no',
				'value'       => 'yes',
				'description' => __( 'If enabled, the Afterpay message will contain the interest free text.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'product_location'            => [
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
			'shop_styling'                => [
				'type'  => 'title',
				'title' => __( 'Shop/Category Page Styling', 'simple-secure-stripe' ),
			],
			'icon_shop'                   => [
				'title'       => __( 'Icon', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'black-on-mint',
				'options'     => [
					'black-on-mint'  => __( 'Black on mint', 'simple-secure-stripe' ),
					'black-on-white' => __( 'Black on white', 'simple-secure-stripe' ),
					'mint-on-black'  => __( 'Mint on black', 'simple-secure-stripe' ),
					'white-on-black' => __( 'White on black', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __( 'This is the icon style that appears next to the gateway on the checkout page.', 'simple-secure-stripe' ),
			],
			'intro_text_shop'             => [
				'title'   => __( 'Intro text', 'simple-secure-stripe' ),
				'type'    => 'select',
				'default' => 'Pay in',
				'options' => [
					'In'     => 'In',
					'Or'     => 'Or',
					'Pay'    => 'Pay',
					'Pay in' => 'Pay in',
				],
			],
			'modal_link_style_shop'       => [
				'title'       => __( 'Modal link style', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'circled-info-icon',
				'options'     => [
					'more-info-text'    => __( 'More info text', 'simple-secure-stripe' ),
					'circled-info-icon' => __( 'Circled info icon', 'simple-secure-stripe' ),
					'learn-more-text'   => __( 'Learn more text', 'simple-secure-stripe' ),
				],
				'description' => __( 'This is the style of the Afterpay info link.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'modal_theme_shop'            => [
				'title'       => __( 'Modal link style', 'simple-secure-stripe' ),
				'type'        => 'select',
				'default'     => 'mint',
				'options'     => [
					'mint'  => __( 'Mint', 'simple-secure-stripe' ),
					'white' => __( 'White', 'simple-secure-stripe' ),
				],
				'description' => __( 'This is the theme color for the Afterpay info modal.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'show_interest_free_shop'     => [
				'type'        => 'checkbox',
				'title'       => __( 'Show interest free', 'simple-secure-stripe' ),
				'default'     => 'no',
				'value'       => 'yes',
				'description' => __( 'If enabled, the Afterpay message will contain the interest free text.', 'simple-secure-stripe' ),
				'desc_tip'    => true,
			],
			'shop_location'               => [
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
		], parent::get_local_payment_settings() );

		// @todo maybe add this option back in a future version.
		//unset( $settings['title_text'] );

		if ( $this->is_restricted_account_country() ) {
			$account_country                           = App::get( Settings\Account::class )->get_account_country( sswps_mode() );
			$settings['specific_countries']['options'] = [ strtoupper( $account_country ) ];
			unset( $settings['allowed_countries']['options']['all_except'] );
		}

		return $settings;
	}

	/**
	 * @inheritDoc
	 */
	public function register_assets() {
		parent::register_assets();

		/*
		Assets\Asset::register( 'sswps-afterpay-product', 'frontend/afterpay.js' )
			->add_to_group( 'sswps-local-payment-product' )
			->set_dependencies( [
				'sswps-script',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_afterpay_product_params',
				$this->get_localized_params( 'product' )
			)
			->add_localize_script(
				'sswps_afterpay_cart_params',
				$this->get_localized_params( 'cart' )
			)
			->set_condition( [ $this, 'is_available' ] );

		Assets\Asset::register( 'sswps-afterpay-messaging', 'dist/afterpay-messaging.js' )
			->add_to_group( 'sswps-local-payment-category' )
			->set_action( 'wp_enqueue_scripts' )
			->set_condition( [ $this, 'is_available' ] );
*/
		App::get( Assets\Data::class )->add( $this->id, [
			'supportedCurrencies' => $this->currencies,
			'requiredParams'      => $this->get_required_parameters(),
			'msgOptions'          => $this->get_afterpay_message_options( 'shop' ),
			'hideIneligible'      => wc_string_to_bool( $this->get_option( 'hide_ineligible' ) ),
			'elementOptions'      => $this->get_element_options(),
		] );
	}

	public function product_fields() {
		$this->enqueue_frontend_scripts( 'product' );
		$this->output_display_items( 'product' );
	}

	public function cart_fields() {
		$this->enqueue_frontend_scripts( 'cart' );
		$this->output_display_items( 'cart' );
	}

	public function mini_cart_fields() {
		$this->output_display_items( 'cart' );
	}

	public function get_required_parameters() {
		return apply_filters( 'sswps/afterpay_get_required_parameters', [
			'AUD' => [ 'AU', 1, 2000 ],
			'CAD' => [ 'CA', 1, 2000 ],
			'NZD' => [ 'NZ', 1, 2000 ],
			'GBP' => [ 'GB', 1, 1000 ],
			'USD' => [ 'US', 1, 2000 ],
			'EUR' => [ [ 'FR', 'ES' ], 1, 1000 ],
		], $this );
	}

	/**
	 * @param $currency
	 * @param $billing_country
	 * @param $total
	 *
	 * @return bool
	 */
	public function validate_local_payment_available( $currency, $billing_country, $total ) {
		$_available      = false;
		$account_country = App::get( Settings\Account::class )->get_account_country( sswps_mode() );
		// in test mode, the API keys might have been manually entered which
		// means the account settings 'country' value will be blank
		if ( empty( $account_country ) && sswps_mode() === 'test' ) {
			$account_country = wc_get_base_location()['country'];
		}
		$params          = $this->get_required_parameters();
		$filtered_params = isset( $params[ $currency ] ) ? $params[ $currency ] : false;
		if ( $filtered_params ) {
			[ $country, $min_amount, $max_amount ] = $filtered_params;
			if ( ! is_array( $country ) ) {
				$country = [ $country ];
			}
			// 1. Country associated with currency must match the Stripe account's registered country
			// 2. Stripe docs state the customer billing country must match the Stripe account country. This rule
			// only pertains to EUR. All currencies do not enforce this requirement.
			// https://stripe.com/docs/payments/afterpay-clearpay#collection-schedule
			$_available = in_array( $account_country, $country, true )
				&& ( $currency !== 'EUR' || ! $billing_country || $account_country === $billing_country )
				&& ( $min_amount <= $total && $total <= $max_amount );
		}

		return $_available;
	}

	public function get_icon() {
		return '';
	}

	public function get_localized_params( $context = 'checkout' ) {
		$params                      = parent::get_localized_params();
		$params['currencies']        = $this->currencies;
		$params['msg_options']       = $this->get_afterpay_message_options( $context );
		$params['supported_locales'] = $this->get_supported_locales();
		$params['requirements']      = $this->get_required_parameters();
		$params['hide_ineligible']   = $this->is_active( 'hide_ineligible' ) ? 'yes' : 'no';
		$params['locale']            = sswps_get_site_locale();

		return $params;
	}

	public function get_supported_locales() {
		return apply_filters( 'sswps/afterpay_supported_locales', [ 'en-US', 'en-CA', 'en-AU', 'en-NZ', 'en-GB', 'fr-FR', 'it-IT', 'es-ES' ] );
	}

	public function get_element_options( $options = [] ) {
		$locale = sswps_get_site_locale();
		if ( ! in_array( $locale, $this->get_supported_locales() ) ) {
			$locale = 'auto';
		}
		$options['locale'] = $locale;

		return parent::get_element_options( $options ); // TODO: Change the autogenerated stub
	}

	public function get_afterpay_message_options( $context = 'checkout' ) {
		$options = [
			'logoType'         => 'badge',
			'badgeTheme'       => $this->get_option( "icon_{$context}" ),
			'lockupTheme'      => 'black',
			'introText'        => $this->get_option( "intro_text_{$context}" ),
			'showInterestFree' => $this->is_active( "show_interest_free_{$context}" ),
			'modalTheme'       => $this->get_option( "modal_theme_{$context}" ),
			'modalLinkStyle'   => $this->get_option( "modal_link_style_{$context}" ),
			'isEligible'       => true,
		];

		if ( in_array( $context, [ 'cart', 'checkout' ] ) ) {
			unset( $options['isEligible'] );
			$options['isCartEligible'] = true;
		}

		return apply_filters( 'sswps/afterpay_message_options', $options, $context, $this );
	}

	public function get_payment_token( $method_id, $method_details = [] ) {
		/**
		 *
		 * @var Tokens\Local $token
		 */
		$token = parent::get_payment_token( $method_id, $method_details );
		$token->set_gateway_title( __( 'Afterpay', 'simple-secure-stripe' ) );

		return $token;
	}

	protected function get_payment_description() {
		$desc = '<p>' . __( 'Stripe accounts in the following countries can accept Afterpay payments with local currency settlement', 'simple-secure-stripe' ) . ': ' . implode( ',', $this->countries ) . '</p>';
		if ( ( $country = App::get( Settings\Account::class )->get_account_country( sswps_mode() ) ) ) {
			$params = $this->get_required_parameters();
			// get currency for country
			foreach ( $params as $currency => $param ) {
				$account_country = ! is_array( $param[0] ) ? [ $param[0] ] : $param[0];
				if ( in_array( $country, $account_country, true ) ) {
					$desc .= sprintf(
						/* translators: 1 - currency, 2 - country */
						__(
							'Store currency must be %1$s for Afterpay to show because your Stripe account is registered in %2$s. This is a requirement of Afterpay.',
							'simple-secure-stripe'
						),
						$currency,
						$country
					);
					if ( $this->is_restricted_account_country() ) {
						$desc .= __( 'You can accept payments from customers in the same country that you registered your Stripe account in.', 'simple-secure-stripe' );
					}

					return $desc;
				}
			}
		}

		$desc .= '<p>' . __(
			'You can accept payments from customers in the same country that you registered your Stripe account in. Payments must also match the local currency of the Stripe account country.',
			'simple-secure-stripe'
		) . '</p>';

		return $desc;
	}

	public function enqueue_mini_cart_scripts() {
		$scripts = App::get( Assets\Assets::class );
		if ( ! wp_script_is( $scripts->get_handle( 'mini-cart' ) ) ) {
			$scripts->enqueue_script(
				'mini-cart',
				$scripts->assets_url( 'js/frontend/mini-cart.js' ),
				apply_filters( 'sswps/mini_cart_dependencies', [ 'sswps-script' ], $scripts )
			);
		}
		$scripts->localize_script( 'mini-cart', $this->get_localized_params( 'cart' ), 'wc_' . $this->id . '_mini_cart_params' );
	}

	public function add_stripe_order_args( &$args, $order ) {
		if ( empty( $args['shipping'] ) ) {
			// This ensures digital products can be processed
			$args['shipping'] = [
				'address' => [
					'city'        => $order->get_billing_city(),
					'country'     => $order->get_billing_country(),
					'line1'       => $order->get_billing_address_1(),
					'line2'       => $order->get_billing_address_2(),
					'postal_code' => $order->get_billing_postcode(),
					'state'       => $order->get_billing_state(),
				],
				'name'    => $this->payment_object->get_name_from_order( $order, 'billing' ),
			];
		}
	}

	private function is_restricted_account_country() {
		$result          = false;
		$account_country = App::get( Settings\Account::class )->get_account_country( sswps_mode() );
		if ( $account_country ) {
			$params = $this->get_required_parameters();
			[ $countries ] = $params['EUR'];
			if ( in_array( $account_country, $countries, true ) ) {
				$result = true;
			}
		}

		return $result;
	}

}