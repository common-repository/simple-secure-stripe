<?php

namespace SimpleSecureWP\SimpleSecureStripe\Payment;

use SimpleSecureWP\SimpleSecureStripe\Constants;
use WC_Order;

/**
 * @since 1.0.0
 *
 * @property int    $fee
 * @property int    $net
 * @property int    $refunded
 * @property string $currency
 */
class Balance {

	private array $data = [];

	private $order;

	/**
	 * @param WC_Order $order
	 */
	public function __construct( $order ) {
		$this->order = $order;
		$this->data  = [
			'currency' => $order->get_meta( Constants::STRIPE_CURRENCY ),
			'fee'      => (float) $order->get_meta( Constants::STRIPE_FEE ),
			'net'      => (float) $order->get_meta( Constants::STRIPE_NET ),
			'refunded' => 0,
		];
	}

	public function __isset( $name ) {
		return isset( $this->data[ $name ] );
	}

	public function __set( $name, $value ) {
		$this->set_prop( $name, $value );
	}

	public function __get( $name ) {
		if ( method_exists( $this, 'get_' . $name ) ) {
			return $this->{'get_' . $name}();
		}

		return $this->get_prop( $name );
	}

	private function set_prop( $name, $value ) {
		$this->data[ $name ] = $value;
	}

	private function get_prop( $key, $default = '' ) {
		if ( ! isset( $this->data[ $key ] ) ) {
			$this->data[ $key ] = $default;
		}

		return $this->data[ $key ];
	}

	/**
	 * @return mixed
	 */
	public function get_fee() {
		return $this->get_prop( 'fee', 0 );
	}

	/**
	 * @return mixed
	 */
	public function get_net() {
		return $this->get_prop( 'net', 0 );
	}

	public function get_refunded() {
		return $this->get_prop( 'refunded', 0 );
	}

	/**
	 * @return mixed
	 */
	public function get_currency() {
		return $this->get_prop( 'currency' );
	}

	public function to_array() {
		return $this->data;
	}

	/**
	 * @since 1.0.0
	 * @return void
	 */
	public function save() {
		$this->update_meta_data( true );
	}

	public function update_meta_data( $save = false ) {
		if ( $this->order ) {
			$this->order->update_meta_data( Constants::STRIPE_CURRENCY, $this->currency );
			$this->order->update_meta_data( Constants::STRIPE_FEE, $this->fee );
			$this->order->update_meta_data( Constants::STRIPE_NET, $this->net );
			if ( $save ) {
				$this->order->save();
			}
		}
	}

}