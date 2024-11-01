<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceSubscriptions;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets\Data;
use SimpleSecureWP\SimpleSecureStripe\Checker;
use SimpleSecureWP\SimpleSecureStripe\Constants;
use SimpleSecureWP\SimpleSecureStripe\Customer_Manager;
use SimpleSecureWP\SimpleSecureStripe\Gateways\Abstract_Gateway;
use SimpleSecureWP\SimpleSecureStripe\Utils as SSWPS_Utils;
use WC_Order;

class Payment_Intent {
	/**
	/**
	 * @param $has_set_up_intent
	 *
	 * @return mixed|true
	 */
	public function maybe_create_setup_intent( $has_set_up_intent ) {
		$utils = App::get( Utils::class );

		if ( $has_set_up_intent ) {
			return $has_set_up_intent;
		}

		if ( $utils->is_change_payment_method() ) {
			return true;
		}

		if ( $utils->is_checkout_with_free_trial() ) {
			return true;
		}

		if ( $utils->is_order_pay_with_free_trial() ) {
			return true;
		}

		return $has_set_up_intent;
	}

	/**
	 * @param array     $args
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function update_payment_intent_args( $args, $order ) {
		return $this->add_params_to_intent( $args, $order );
	}

	public function update_setup_intent_params( $args, $order ) {
		return $this->add_params_to_intent( $args, $order, 'setup_intent' );
	}

	/**
	 * @param array     $args
	 * @param WC_Order $order
	 * @param string    $type
	 *
	 * @return array
	 */
	private function add_params_to_intent( $args, $order, $type = 'payment_intent' ) {
		if ( ! in_array( 'card', $args['payment_method_types'] ) ) {
			return $args;
		}

		// check if this is an India account. If so, make sure mandate data is included.
		if ( App::get( Settings\Account::class )->get_account_country( sswps_order_mode( $order ) ) !== 'IN' ) {
			return $args;
		}

		if (
			! (
				isset( $args['setup_future_usage'] )
				&& $args['setup_future_usage'] === 'off_session'
				|| $type === 'setup_intent'
				|| wcs_order_contains_subscription( $order )
			)
		) {
			return $args;
		}

		$subscriptions = wcs_get_subscriptions_for_order( $order );
		if ( ! $subscriptions ) {
			return $args;
		}

		$total = max( array_map( function ( $subscription ) {
			return (float) $subscription->get_total();
		}, $subscriptions ) );

		if ( ! isset( $args['payment_method_options']['card'] ) ) {
			$args['payment_method_options']['card'] = [];
		}

		$args['payment_method_options']['card']['mandate_options'] = [
			'amount'          => SSWPS_Utils\Currency::add_number_precision( $total, $order->get_currency() ),
			'amount_type'     => 'maximum',
			'interval'        => 'sporadic',
			'reference'       => $order->get_id(),
			'start_date'      => time(),
			'supported_types' => [ 'india' ]
		];

		if ( $type === 'setup_intent' ) {
			$args['payment_method_options']['card']['mandate_options']['currency'] = $order->get_currency();
		}

		return $args;
	}

	/**
	 * @param array                      $args
	 * @param Abstract_Gateway $payment_method
	 *
	 * @return array
	 */
	public function add_setup_intent_params( $args, $payment_method ) {
		if ( ! in_array( 'card', $args['payment_method_types'] ) ) {
			return $args;
		}

		if ( ! $payment_method->is_mandate_required() ) {
			return $args;
		}

		if ( ! isset( $args['payment_method_options']['card'] ) ) {
			$args['payment_method_options']['card'] = [];
		}

		$total = $this->get_recurring_cart_total();
		// add margin to the total since the shipping might not have been calculated yet.
		$customer_id = sswps_get_customer_id();
		if ( ! $customer_id ) {
			$customer = App::get( Customer_Manager::class )->create_customer( WC()->customer );
			if ( ! is_wp_error( $customer ) ) {
				$customer_id = $customer->id;
				WC()->session->set( Constants::STRIPE_CUSTOMER_ID, $customer_id );
			}
		}

		$args['customer']                                          = $customer_id;
		$args['payment_method_options']['card']['mandate_options'] = [
			'amount'          => SSWPS_Utils\Currency::add_number_precision( $total ),
			'amount_type'     => 'maximum',
			'interval'        => 'sporadic',
			'reference'       => sprintf( '%1$s-%2$s', WC()->session->get_customer_id(), uniqid() ),
			'start_date'      => time(),
			'supported_types' => [ 'india' ],
			'currency'        => get_woocommerce_currency()
		];

		return $args;
	}

	public function print_script_variables() {
		if ( WC()->cart && Checker::is_woocommerce_subscriptions_active() && \WC_Subscriptions_Cart::cart_contains_subscription() ) {
			App::get( Data::class )->print_data( 'sswps_cart_contains_subscription', true );
		}
	}

	private function get_recurring_cart_total() {
		WC()->cart->calculate_totals();
		$carts = WC()->cart->recurring_carts; // @phpstan-ignore-line - This property is set somewhere.
		if ( \is_array( $carts ) ) {
			return array_reduce( $carts, function ( $total, $cart ) {
				return (float) $total + (float) $cart->get_total( 'edit' );
			}, 0 );
		}

		return 0;
	}

}