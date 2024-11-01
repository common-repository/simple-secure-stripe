<?php

namespace SimpleSecureWP\SimpleSecureStripe\Gateways;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Payment;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use WC_Order;

/**
 * Class Klarna
 *
 */
class Klarna extends Abstract_Local_Payment {

	protected string $payment_method_type = 'klarna';

	private $supported_locales = [
		'de-AT',
		'en-AT',
		'da-DK',
		'en-DK',
		'fi-FI',
		'sv-FI',
		'en-FI',
		'de-DE',
		'en-DE',
		'nl-NL',
		'en-NL',
		'nb-NO',
		'en-NO',
		'sv-SE',
		'en-SE',
		'en-GB',
		'en-US',
		'es-US',
		'nl-BE',
		'fr-BE',
		'en-BE',
		'es-ES',
		'en-ES',
		'it-IT',
		'en-IT',
		'fr-FR',
		'en-FR',
		'en-IE',
		'pl-PL',
	];

	use Payment\Traits\Local_Intent;

	public function __construct() {
		$this->local_payment_type = 'klarna';
		$this->currencies         = [ 'AUD', 'CAD', 'CHF', 'DKK', 'EUR', 'GBP', 'NOK', 'NZD', 'PLN', 'SEK', 'DKK', 'USD' ];
		$this->countries          = $this->limited_countries = [ 'AT', 'AU', 'BE', 'CA', 'CH', 'DE', 'DK', 'ES', 'FI', 'FR', 'GB', 'GR', 'IE', 'IT', 'NL', 'NO', 'NZ', 'PL', 'PT', 'SE', 'US' ];
		$this->id                 = 'sswps_klarna';
		$this->tab_title          = __( 'Klarna', 'simple-secure-stripe' );
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'Klarna', 'simple-secure-stripe' );
		$this->method_description = __( 'Klarna gateway that integrates with your Stripe account.', 'simple-secure-stripe' );
		parent::__construct();
		$this->template_name = 'klarna-v2.php';
		$this->icon          = App::get( Plugin::class )->assets_url( 'img/' . $this->get_option( 'icon' ) . '.svg' );
	}

	public function get_required_parameters() {
		return apply_filters( 'sswps/klarna_get_required_parameters', [
			'AUD' => [ 'AU' ],
			'CAD' => [ 'CA' ],
			'USD' => [ 'US' ],
			'EUR' => [ 'AT', 'BE', 'DE', 'ES', 'FI', 'FR', 'GR', 'IE', 'IT', 'NL', 'PT' ],
			'DKK' => [ 'DK' ],
			'NOK' => [ 'NO' ],
			'SEK' => [ 'SE' ],
			'GBP' => [ 'GB' ],
			'PLN' => [ 'PL' ],
			'CHF' => [ 'CH' ],
			'NZD' => [ 'NZ' ],
		], $this );
	}

	/**
	 * @param string $currency
	 * @param string $billing_country
	 * @param float  $total
	 *
	 * @return bool
	 */
	public function validate_local_payment_available( $currency, $billing_country, $total ) {
		if ( $billing_country ) {
			$params = $this->get_required_parameters();

			return isset( $params[ $currency ] ) && in_array( $billing_country, $params[ $currency ] ) !== false;
		}

		return false;
	}

	public function add_stripe_order_args( &$args, $order ) {
		$args['payment_method_options'] = [
			'klarna' => [
				'preferred_locale' => $this->get_formatted_locale_from_order( $order ),
			],
		];
	}

	/**
	 * Returns a formatted locale based on the billing country for the order.
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	private function get_formatted_locale_from_order( $order ) {
		$country = $order->get_billing_country();
		switch ( $country ) {
			case 'US':
				$locale = 'en-US';
				break;
			case 'GB':
				$locale = 'en-GB';
				break;
			case 'AT':
				$locale = 'de-AT';
				break;
			case 'BE':
				$locale = 'fr-BE';
				break;
			case 'DK':
				$locale = 'da-DK';
				break;
			case 'NO':
				$locale = 'nb-NO';
				break;
			case 'SE':
				$locale = 'sv-SE';
				break;
			case 'PL':
				$locale = 'pl-PL';
				break;
			default:
				$locale = strtolower( $country ) . '-' . strtoupper( $country );
		}
		if ( ! in_array( $locale, $this->supported_locales, true ) ) {
			$locale = 'en-US';
		}

		return $locale;
	}

	public function get_local_payment_settings() {
		return wp_parse_args(
			[
				'charge_type' => [
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
				'icon'        => [
					'title'       => __( 'Icon', 'simple-secure-stripe' ),
					'type'        => 'select',
					'options'     => [
						'klarna'      => __( 'Black text', 'simple-secure-stripe' ),
						'klarna_pink' => __( 'Pink background black text', 'simple-secure-stripe' ),
					],
					'default'     => 'klarna_pink',
					'desc_tip'    => true,
					'description' => __( 'This is the icon style that appears next to the gateway on the checkout page.', 'simple-secure-stripe' ),
				],
			],
			parent::get_local_payment_settings()
		);
	}

}
