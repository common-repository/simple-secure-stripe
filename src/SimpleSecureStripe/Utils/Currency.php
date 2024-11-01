<?php
namespace SimpleSecureWP\SimpleSecureStripe\Utils;

class Currency {
	/**
	 * Add number precision to a number.
	 *
	 * @since   1.0.0
	 *
	 * @param float  $value
	 * @param string $currency
	 * @param bool   $round
	 *
	 * @return float
	 */
	public static function add_number_precision( $value, $currency = '', $round = true ) {
		if ( ! is_numeric( $value ) ) {
			$value = 0;
		}

		/**
		 * @since 1.0.0
		 * round before performing precision calculation
		 */
		$decimals       = wc_get_price_decimals();
		$value          = round( $value, $decimals );
		$currency       = empty( $currency ) ? get_woocommerce_currency() : $currency;
		$currencies     = static::get_currencies();
		$exp            = isset( $currencies[ $currency ] ) ? $currencies[ $currency ] : 2;
		$cent_precision = pow( 10, $exp );
		$value          = $value * $cent_precision;
		$value          = $round ? round( $value, wc_get_rounding_precision() - $decimals ) : $value;

		if ( is_numeric( $value ) && floor( $value ) != $value ) {
			// there are some decimal points that need to be removed.
			$value = round( $value );
		}

		return $value;
	}

	/**
	 * Return an array of Stripe currencies where the value of each
	 * currency is the curency multiplier.
	 *
	 * @since   1.0.0
	 * @return mixed
	 */
	public static function get_currencies() {
		return apply_filters(
			'sswps/get_currencies',
			[
				'BHD' => 3,
				'BIF' => 0,
				'CLP' => 0,
				'DJF' => 0,
				'GNF' => 0,
				'IQD' => 3,
				'JOD' => 3,
				'JPY' => 0,
				'KMF' => 0,
				'KRW' => 0,
				'KWD' => 3,
				'LYD' => 3,
				'MGA' => 0,
				'OMR' => 3,
				'PYG' => 0,
				'RWF' => 0,
				'TND' => 3,
				'UGX' => 0,
				'VND' => 0,
				'VUV' => 0,
				'XAF' => 0,
				'XOF' => 0,
				'XPF' => 0,
			]
		);
	}

	/**
	 * Remove precision from a number.
	 *
	 * @since 1.0.0
	 *
	 * @param float  $value
	 * @param string $currency
	 * @param bool   $round
	 * @param int|null $precision
	 *
	 * @return float|int
	 */
	public static function remove_number_precision( $value, $currency = '', $round = true, $precision = null ) {
		$currency   = empty( $currency ) ? get_woocommerce_currency() : $currency;
		$currencies = static::get_currencies();
		$exp        = isset( $currencies[ $currency ] ) ? $currencies[ $currency ] : 2;
		$number     = $value / pow( 10, $exp );

		return $round ? round( $number, $precision === null ? wc_get_price_decimals() : $precision ) : $number;
	}
}