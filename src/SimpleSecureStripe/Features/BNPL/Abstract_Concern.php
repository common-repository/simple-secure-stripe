<?php
namespace SimpleSecureWP\SimpleSecureStripe\Features\BNPL;

use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Assets\Data as AssetData;
use SimpleSecureWP\SimpleSecureStripe\Plugin;

abstract class Abstract_Concern {

	/**
	 * @var array
	 */
	protected $gateways = [];

	/**
	 * Constructor!
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->gateways   = App::get( Gateways::class )->get();
	}

	/**
	 * Renders the gateway items.
	 *
	 * @since 1.0.0
	 *
	 * @param array $gateways
	 */
	public function render( array $gateways ) {}

	/**
	 * Renders data for the given location.
	 *
	 * @since 1.0.0
	 *
	 * @param string $option
	 * @param string $location.
	 */
	protected function render_when( string $option, string $location ) {
		if ( empty( $this->gateways ) ) {
			return;
		}

		$gateways = array_filter( $this->gateways, function( $gateway ) use ( $option, $location ) {
			return $gateway->get_option( $option ) === $location;
		} );

		if ( empty( $gateways ) ) {
			return;
		}

		$this->render( $gateways );
	}
}