<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets;
use SimpleSecureWP\SimpleSecureStripe\Checker;
use SimpleSecureWP\SimpleSecureStripe\Features\Installments\Installments;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\Features\Link\Link;

/**
 *
 * @since   1.0.0
 * @package Stripe/Gateways
 * @author Simple & Secure WP
 *
 */
class CC extends Abstract_Gateway {

	use Payment\Traits\Intent;

	protected string $payment_method_type = 'card';

	public $installments;

	/**
	 * @var bool
	 */
	protected $supports_save_payment_method = true;

	public function __construct() {
		$this->id                 = 'sswps_cc';
		$this->tab_title          = __( 'Credit/Debit Cards', 'simple-secure-stripe' );
		$this->template_name      = 'credit-card.php';
		$this->token_type         = 'Stripe_CC';
		$this->method_title       = __( 'Credit Cards', 'simple-secure-stripe' );
		$this->method_description = __( 'Credit card gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		parent::__construct();
		$this->installments = App::get( Installments::class );
	}

	public function get_icon() {
		$cards = $this->get_option( 'cards', [] );
		$icons = [];
		foreach ( (array) $cards as $card ) {
			$icons[ $card ] = App::get( Plugin::class )->assets_url( "img/cards/{$card}.svg" );
		}

		$output = sswps_get_template_html(
			'card-icons.php',
			apply_filters( 'sswps/cc_icon_template_args', [
				'cards'      => $cards,
				'icons'      => $icons,
				'assets_url' => App::get( Plugin::class )->assets_url(),
			], $this )
		);

		return $output;
	}

	/**
	 * @inheritDoc
	 */
	public function register_assets() {
		parent::register_assets();

		Assets\Asset::register( 'sswps-credit-card', 'frontend/credit-card.js' )
			->add_to_group( 'sswps-local-payment' )
			->set_dependencies( [
				'sswps-stripe-external',
				'sswps-script',
			] )
			->set_action( 'wp_enqueue_scripts' )
			->add_localize_script(
				'sswps_credit_card_params',
				$this->get_localized_params()
			)
			->set_condition( [ $this, 'is_available' ] );
	}

	public function get_localized_params() {
		$data = parent::get_localized_params();

		return array_merge(
			$data,
			[
				'cardOptions'        => $this->get_card_form_options(),
				'customFieldOptions' => $this->get_card_custom_field_options(),
				'cardFormType'       => $this->get_active_card_form_type(),
				'custom_form'        => $this->is_custom_form_active(),
				'custom_form_name'   => $this->get_option( 'custom_form' ),
				'html'               => [ 'card_brand' => sprintf( '<img id="sswps-card" src="%s" />', $this->get_custom_form()['cardBrand'] ) ],
				'cards'              => [
					'visa'       => App::get( Plugin::class )->assets_url( 'img/cards/visa.svg' ),
					'amex'       => App::get( Plugin::class )->assets_url( 'img/cards/amex.svg' ),
					'mastercard' => App::get( Plugin::class )->assets_url( 'img/cards/mastercard.svg' ),
					'discover'   => App::get( Plugin::class )->assets_url( 'img/cards/discover.svg' ),
					'diners'     => App::get( Plugin::class )->assets_url( 'img/cards/diners.svg' ),
					'jcb'        => App::get( Plugin::class )->assets_url( 'img/cards/jcb.svg' ),
					'unionpay'   => App::get( Plugin::class )->assets_url( 'img/cards/china_union_pay.svg' ),
					'unknown'    => $this->get_custom_form()['cardBrand'],
				],
				'postal_regex'       => $this->get_postal_code_regex(),
				'notice_location'    => $this->get_option( 'notice_location' ),
				'notice_selector'    => $this->get_notice_css_selector(),
				'installments'       => [
					'loading' => __( 'Loading installments...', 'simple-secure-stripe' ),
				],
			]
		);
	}

	/**
	 * @since 1.0.0
	 */
	public function get_card_form_options() {
		$options = [
			'style' => $this->get_form_style(),
		];

		return apply_filters( 'sswps/cc_form_options', $options, $this );
	}

	/**
	 * @since 1.0.0
	 * @return mixed|void
	 */
	public function get_card_custom_field_options() {
		$style   = $this->get_form_style();
		$options = [];
		foreach ( [ 'cardNumber', 'cardExpiry', 'cardCvc' ] as $key ) {
			$options[ $key ] = [ 'style' => $style ];
		}

		return apply_filters( 'sswps/get_card_custom_field_options', $options, $this );
	}

	public function get_form_style() {
		if ( $this->is_custom_form_active() ) {
			$style = $this->get_custom_form()['elementStyles'];
		} else {
			$style = [
				'base'    => [
					'color'         => '#32325d',
					'fontFamily'    => '"Helvetica Neue", Helvetica, sans-serif',
					'fontSmoothing' => 'antialiased',
					'fontSize'      => '18px',
					'::placeholder' => [ 'color' => '#aab7c4' ],
					':focus'        => [],
				],
				'invalid' => [
					'color'     => '#fa755a',
					'iconColor' => '#fa755a',
				],
			];
		}

		return apply_filters( 'sswps/cc_element_style', $style, $this );
	}

	public function get_custom_form() {
		return sswps_get_custom_forms()[ $this->get_option( 'custom_form' ) ];
	}

	public function get_element_options( $options = [] ) {
		if ( $this->is_custom_form_active() ) {
			return parent::get_element_options( $this->get_custom_form()['elementOptions'] );
		} elseif ( $this->is_payment_element_active() ) {
			$options = \SimpleSecureWP\SimpleSecureStripe\Controllers\PaymentIntent::instance()->get_element_options();
			if ( App::get( Link::class )->is_active() ) {
				$options = array_merge( $options, [ 'payment_method_types' => [ 'card', 'link' ] ] );
			}
			$options['appearance'] = [ 'theme' => $this->get_option( 'theme', 'stripe' ) ];

			return parent::get_element_options( $options );
		}

		return parent::get_element_options();
	}


	/**
	 * Returns true if custom forms are enabled.
	 *
	 * @return bool
	 */
	public function is_custom_form_active() {
		return $this->get_option( 'form_type' ) === 'custom';
	}

	public function is_payment_element_active() {
		return $this->get_option( 'form_type' ) === 'payment';
	}

	public function get_custom_form_template() {
		$form = $this->get_option( 'custom_form' );

		return sswps_get_custom_forms()[ $form ]['template'];
	}

	/**
	 * Returns true if the postal code field is enabled.
	 *
	 * @return bool
	 */
	public function postal_enabled() {
		if ( is_checkout() ) {
			return $this->is_active( 'postal_enabled' );
		}
		if ( is_add_payment_method_page() ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns true if the cvv field is enabled.
	 *
	 * @return bool
	 */
	public function cvv_enabled() {
		return $this->is_active( 'cvv_enabled' );
	}

	public function get_postal_code_regex() {
		return [
			'AT' => '^([0-9]{4})$',
			'BR' => '^([0-9]{5})([-])?([0-9]{3})$',
			'CH' => '^([0-9]{4})$',
			'DE' => '^([0]{1}[1-9]{1}|[1-9]{1}[0-9]{1})[0-9]{3}$',
			'ES' => '^([0-9]{5})$',
			'FR' => '^([0-9]{5})$',
			'IT' => '^([0-9]{5})$/i',
			'IE' => '([AC-FHKNPRTV-Y]\d{2}|D6W)[0-9AC-FHKNPRTV-Y]{4}',
			'JP' => '^([0-9]{3})([-])([0-9]{4})$',
			'PT' => '^([0-9]{4})([-])([0-9]{3})$',
			'US' => '^([0-9]{5})(-[0-9]{4})?$',
			'CA' => '^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])([\ ])?(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$',
			'PL' => '^([0-9]{2})([-])([0-9]{3})',
			'CZ' => '^([0-9]{3})(\s?)([0-9]{2})$',
			'SK' => '^([0-9]{3})(\s?)([0-9]{2})$',
			'NL' => '^([1-9][0-9]{3})(\s?)(?!SA|SD|SS)[A-Z]{2}$',
		];
	}

	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see Abstract_Gateway::add_stripe_order_args()
	 */
	public function add_stripe_order_args( &$args, $order ) {
		// if the merchant is forcing 3D secure for all intents then add the required args.
		if ( $this->is_active( 'force_3d_secure' ) && is_checkout() && ! doing_action( 'woocommerce_scheduled_subscription_payment_' . $this->id ) ) {
			$args['payment_method_options']['card']['request_three_d_secure'] = 'any';
		}
	}

	/**
	 * @since 1.0.0
	 * @return mixed|void
	 */
	private function get_notice_css_selector() {
		$location = $this->get_option( 'notice_location' );
		$selector = '';
		switch ( $location ) {
			case 'acf':
				$selector = 'div.payment_method_sswps_cc';
				break;
			case 'bcf':
				$selector = '.sswps-card-notice';
				break;
			case 'toc':
				$selector = 'form.checkout';
				break;
			case 'custom':
				$selector = $this->get_option( 'notice_selector', 'div.payment_method_sswps_cc' );
				break;
		}

		return $selector;
	}

	public function is_installment_available() {
		$order_id = null;
		if ( is_checkout_pay_page() ) {
			global $wp;
			$order_id = absint( $wp->query_vars['order-pay'] );
		}

		return $this->installments->is_available( $order_id );
	}

	/**
	 * @return string Serves as a wrapper for the form_type option with some validations to ensure
	 *                a payment intent exists in the session.
	 */
	protected function get_active_card_form_type() {
		return $this->get_option( 'form_type' );
	}

	public function validate_form_type_field( $key, $value ) {
		if ( $value !== 'payment' && App::get( Settings\Advanced::class )->is_active( 'link_enabled' ) ) {
			$value = 'payment';
			\WC_Admin_Settings::add_error( __( 'Only the Stripe payment form can be used while Link is enabled.', 'simple-secure-stripe' ) );
		}

		return $value;
	}

	public function is_deferred_intent_creation() {
		return $this->is_payment_element_active();
	}

	public function get_save_payment_method_label() {
		return __( 'Save Card', 'simple-secure-stripe' );
	}
}