<?php

namespace SimpleSecureWP\SimpleSecureStripe\Features\Link;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets;
use SimpleSecureWP\SimpleSecureStripe\Assets\Data as AssetData;
use SimpleSecureWP\SimpleSecureStripe\Assets\API as AssetAPI;
use SimpleSecureWP\SimpleSecureStripe\Controllers\PaymentIntent;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WC_Order;

class Link {

	const DATA_KEY = 'sswpsStripeLink';

	/**
	 * Is the Link feature enabled?
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	private $enabled;

	/**
	 * Supported countries.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private array $supported_countries = [
		'AE',
		'AT',
		'AU',
		'BE',
		'BG',
		'CA',
		'CH',
		'CY',
		'CZ',
		'DE',
		'DK',
		'EE',
		'ES',
		'FI',
		'FR',
		'GB',
		'GI',
		'GR',
		'HK',
		'HR',
		'HU',
		'IE',
		'IT',
		'JP',
		'LI',
		'LT',
		'LU',
		'LV',
		'MT',
		'MX',
		'MY',
		'NL',
		'NO',
		'NZ',
		'PL',
		'PT',
		'RO',
		'SE',
		'SG',
		'SI',
		'SK',
		'US',
	];

	/**
	 * Supported payment methods.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private array $supported_payment_methods = [ 'sswps_cc' ];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->enabled = App::get( Settings\Advanced::class )->is_active( 'link_enabled' );
		if ( $this->is_active() ) {
			$this->register_assets();
		}
	}

	/**
	 * Is the Link feature active?
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_active() : bool {
		return $this->enabled && $this->is_valid_account_country();
	}

	private function register_assets() {
		Assets\Asset::register( 'sswps-link-checkout', 'dist/link-checkout.js' )
			->set_dependencies( [
				'sswps-external',
				'sswps-credit-cart',
			] )
		->set_action( 'wp_enqueue_scripts' )
		->set_condition( [ $this, 'is_active' ] )
		->enqueue();
	}

	private function is_valid_account_country() {
		return \in_array( App::get( Settings\Account::class )->get_account_country( sswps_mode() ), $this->supported_countries );
	}

	/**
	 * @param null|WC_Order $order
	 *
	 * @return bool|mixed
	 */
	public function can_process_link_payment( $order = null ) {
		if ( $order ) {
			return in_array( $order->get_payment_method(), $this->supported_payment_methods, true )
				&& in_array( App::get( Settings\Account::class )->get_account_country( sswps_order_mode( $order ) ), $this->supported_countries );
		}

		return is_checkout()
			&& WC()->cart
			&& WC()->cart->needs_payment();
	}

	public function enqueue_scripts() {
		if ( $this->can_process_link_payment() ) {
			$icon = App::get( Settings\Advanced::class )->get_option( 'link_icon', 'dark' );
			App::get( AssetData::class )->print_data( self::DATA_KEY, [
				'launchLink'      => $this->is_autoload_enabled(),
				'linkIconEnabled' => $this->is_icon_enabled(),
				'linkIcon'        => $this->is_icon_enabled() ? sswps_get_template_html( "link/link-icon-{$icon}.php" ) : null,
				'elementOptions'  => array_merge( PaymentIntent::instance()->get_element_options(), [
					'currency' => strtolower( get_woocommerce_currency() ),
					'amount'   => Utils\Currency::add_number_precision( WC()->cart->get_total( 'raw' ) ),
				] ),
			] );
			wp_enqueue_script( 'sswps-link-checkout' );
		}
	}

	public function add_script_params( $data ) {
		$data['stripeParams']['betas'][] = 'link_autofill_modal_beta_1';

		return $data;
	}

	/**
	 * @param array     $params
	 * @param WC_Order $order
	 */
	public function add_payment_method_type( $params, $order ) {
		if ( $this->can_process_link_payment( $order ) ) {
			$params['payment_method_types'][] = 'link';
		}

		return $params;
	}

	public function add_billing_email_priority( $fields ) {
		if ( App::get( Settings\Advanced::class )->is_active( 'link_email' ) ) {
			if ( isset( $fields['billing']['billing_email'] ) ) {
				$fields['billing']['billing_email']['priority'] = 1;
			}
		}

		return $fields;
	}

	public function is_autoload_enabled() {
		return App::get( Settings\Advanced::class )->is_active( 'link_autoload' );
	}

	public function is_icon_enabled() {
		return 'no' !== App::get( Settings\Advanced::class )->get_option( 'link_icon', 'dark' );
	}

	/**
	 * @param array                                             $args
	 * @param \SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentIntent $intent
	 * @param WC_Order                                         $order
	 *
	 * @return array
	 */
	public function add_confirmation_args( $args, $intent, $order ) {
		if ( isset( $intent->payment_method->type ) ) {
			if ( $intent->payment_method->type === 'link' ) {
				$args['mandate_data'] = [
					'customer_acceptance' => [
						'type'   => 'online',
						'online' => [
							'ip_address' => $order->get_customer_ip_address(),
							'user_agent' => $order->get_customer_user_agent(),
						],
					],
				];
			}
		}

		return $args;
	}

	public function add_setup_intent_params( $args, $payment_method ) {
		if ( \in_array( $payment_method->id, $this->supported_payment_methods ) ) {
			if ( $this->is_active() ) {
				$args['payment_method_types'][] = 'link';
			}
		}

		return $args;
	}

}