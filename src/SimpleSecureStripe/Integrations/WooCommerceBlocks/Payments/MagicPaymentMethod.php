<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments;

class MagicPaymentMethod {

	const BASE_CLASS = 'SimpleSecureWP\\SimpleSecureStripe\\Gateways\\';

	/**
	 * @var string
	 */
	protected $name;

	protected $payment_method;

	public function __construct( $name ) {
		$this->name = $name;
		$this->initialize_payment_gateway();
	}

	public function __get( $name ) {
		if ( $this->payment_method ) {
			return $this->payment_method->{$name};
		}

		return '';
	}

	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return string
	 */
	public function __call( $name, $arguments ) {
		if ( $this->payment_method && method_exists( $this->payment_method, $name ) ) {
			return $this->payment_method->{$name}( ...$arguments );
		}

		return '';
	}

	private function initialize_payment_gateway() {
		$suffix  = str_replace( 'sswps_', '', $this->name );
		$clazzes = [
			self::BASE_CLASS . strtoupper( $suffix ),
			self::BASE_CLASS . ucfirst( $suffix ),
			self::BASE_CLASS . ucwords( $suffix, '_' ),
		];
		foreach ( $clazzes as $clazz ) {
			if ( class_exists( $clazz ) ) {
				$this->payment_method = new $clazz();
				break;
			}
		}
	}

}