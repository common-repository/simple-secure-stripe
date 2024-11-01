<?php
namespace SimpleSecureWP\SimpleSecureStripe\Features\BNPL;

use SimpleSecureWP\SimpleSecureStripe\Gateways\Abstract_Local_Payment;

class Gateways {
	/**
	 * @var array
	 */
	private array $gateway_ids = [
		'sswps_affirm',
		'sswps_afterpay',
	];

	/**
	 * @var array
	 */
	private array $sorted_gateways = [];

	/**
	 * @var array
	 */
	private array $payment_gateways = [];

	/**
	 * @var array
	 */
	private array $ordering = [];

	/**
	 * @var int
	 */
	private int $sort = 999;

	/**
	 * @return array
	 */
	public function get() {
		if ( $this->sorted_gateways ) {
			return $this->sorted_gateways;
		}

		$this->payment_gateways = WC()->payment_gateways()->payment_gateways();
		$this->ordering         = (array) get_option( 'woocommerce_gateway_order' );
		$sort                   = 999;
		$this->sorted_gateways  = array_reduce( $this->gateway_ids, [ $this, 'index_for_sorting' ], [] );

		ksort( $this->sorted_gateways );

		return $this->sorted_gateways;
	}

	/**
	 * Indexes the gateways so that they are sorted.
	 *
	 * @param mixed $gateways
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	private function index_for_sorting( $gateways, $id ) {
		$gateway = $this->payment_gateways[ $id ] ?? null;

		if ( ! $gateway || ! $gateway instanceof Abstract_Local_Payment ) {
			return $gateways;
		}

		if ( ! wc_string_to_bool( $gateway->get_option( 'enabled' ) ) ) {
			return $gateways;
		}

		if ( ! in_array( 'shop', $gateway->get_option( 'payment_sections' ) ) ) {
			return $gateways;
		}

		if ( isset( $this->ordering[ $id ] ) && is_numeric( $this->ordering[ $id ] ) ) {
			$gateways[ $this->ordering[ $id ] ] = $gateway;
		} else {
			$gateways[ $this->sort ] = $gateway;
			$this->sort++;
		}

		return $gateways;
	}
}