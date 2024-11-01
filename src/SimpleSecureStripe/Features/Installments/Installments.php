<?php

namespace SimpleSecureWP\SimpleSecureStripe\Features\Installments;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Gateways;
use SimpleSecureWP\SimpleSecureStripe\Features\Installments\Filters;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
use SimpleSecureWP\SimpleSecureStripe\SimpleSecureWP\RequestHandling\Request;
use SimpleSecureWP\SimpleSecureStripe\Stripe\Charge;
use SimpleSecureWP\SimpleSecureStripe\Stripe\PaymentIntent;
use SimpleSecureWP\SimpleSecureStripe\Utils;
use WC_Order;

/**
 * @since 1.0.0
 */
class Installments {
	/**
	 * Check if the feature is active.
	 *
	 * @return bool
	 */
	public static function is_active() : bool {
		return wc_string_to_bool( App::get( Settings\Advanced::class )->get_option( 'installments' ) );
	}

	/**
	 * Returns whether or not installments are available.
	 *
	 * @param WC_Order|int|null $order
	 *
	 * @return bool
	 */
	public function is_available( $order = null ) : bool {
		if ( ! static::is_active() ) {
			return false;
		}

		if ( $order !== null ) {
			if ( is_int( $order ) ) {
				$order = wc_get_order( $order );
			}
			$filters = $this->order_filters_factory( $order );
		} else {
			$filters = $this->cart_filters_factory();
		}

		$is_available = true;
		foreach ( $filters as $filter ) {
			if ( ! $filter->is_available() ) {
				return false;
			}
		}

		return (bool) apply_filters( 'sswps/features/installments/is_available', $is_available );
	}

	/**
	 * @return array
	 */
	private function cart_filters_factory() {
		$currency = get_woocommerce_currency();

		return [
			new Filters\Currency( $currency, App::get( Settings\Account::class )->get_account_country( sswps_mode() ) ),
			new Filters\Order_Total( WC()->cart ? WC()->cart->get_total( 'raw' ) : 0, $currency ),
			new Filters\Subscription( WC()->cart, null ),
			new Filters\Pre_Orders( WC()->cart, null ),
		];
	}

	private function order_filters_factory( WC_Order $order ) {
		$currency = $order->get_currency();

		return [
			new Filters\Currency( $currency, App::get( Settings\Account::class )->get_account_country( sswps_order_mode( $order ) ) ),
			new Filters\Order_Total( $order->get_total( 'raw' ), $currency ),
			new Filters\Subscription( null, $order ),
			new Filters\Pre_Orders( null, $order ),
		];
	}

	/**
	 * @param WC_Order                  $order
	 * @param Gateways\Abstract_Gateway $payment_method
	 * @param Charge                    $charge
	 */
	public function add_order_meta( $order, $payment_method, $charge ) {
		if ( empty( $charge->payment_method_details->card->installments->plan ) ) {
			return;
		}

		$plan = $charge->payment_method_details->card->installments->plan;
		$order->update_meta_data( Constants::INSTALLMENT_PLAN, App::get( Formatter::class )->format_plan_id( $plan ) );
	}

	/**
	 * @param array $rows
	 * @param WC_Order $order
	 */
	public function add_order_item_total( $rows, $order ) {
		$plan = $order->get_meta( Constants::INSTALLMENT_PLAN );
		if ( ! $plan ) {
			return $rows;
		}

		$amount = Utils\Currency::add_number_precision( $order->get_total( 'raw' ), $order->get_currency() );
		$formatter = App::get( Formatter::class );

		$rows[ Constants::INSTALLMENT_PLAN ] = [
			'label' => __( 'Installments:', 'simple-secure-stripe' ),
			'value' => $formatter->format_plan(
				$formatter->parse_plan_from_id( $plan, true ),
				$amount,
				$order->get_currency()
			),
		];

		return $rows;
	}

	/**
	 * @param bool $can_update
	 * @param array $intent
	 */
	public function can_update_payment_intent( $can_update, $intent ) {
		if ( ! $can_update && ! empty( $intent['payment_method_options']['card']['installments']['enabled'] ) ) {
			if ( $intent['status'] !== 'succeeded' ) {
				$can_update = true;
			}
		}

		return $can_update;
	}

	/**
	 * @param                      $args
	 * @param PaymentIntent        $intent
	 */
	public function add_confirmation_args( $args, $intent ) {
		if ( empty( $intent->payment_method_options->card->installments->available_plans ) ) {
			return $args;
		}

		$plan_id = wc_clean( Request::get_sanitized_var( Constants::INSTALLMENT_PLAN, null ) );
		if ( App::get( Formatter::class )->is_valid_plan( $plan_id ) ) {
			$args['payment_method_options'] = [ 'card' => [ 'installments' => [ 'plan' => App::get( Formatter::class )->parse_plan_from_id( $plan_id ) ] ] ];
		}

		return $args;
	}

	public static function get_supported_countries() {
		$filter = new Filters\Currency( 0, null );

		return $filter->get_supported_countries();
	}

	public static function get_supported_currencies() {
		$filter = new Filters\Currency( 0, null );

		return $filter->get_supported_currencies();
	}
}