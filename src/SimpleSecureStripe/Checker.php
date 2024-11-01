<?php
namespace SimpleSecureWP\SimpleSecureStripe;

class Checker {
	/**
	 * Checks if the current request is a front end one.
	 *
	 * @return bool
	 */
	public static function is_frontend_request() {
		return ! is_admin() || defined( 'DOING_AJAX' ) && ! defined( 'DOING_CRON' );
	}

	/**
	 * Checks if WooCommerce Subscriptions is active.
	 *
	 * @return bool
	 */
	public static function is_woocommerce_pre_orders_active() {
		return class_exists( 'WC_Pre_Orders' );
	}

	/**
	 * Checks if WooCommerce Subscriptions is active.
	 *
	 * @return bool
	 */
	public static function is_woocommerce_subscriptions_active() {
		return function_exists( 'wcs_is_subscription' );
	}
}