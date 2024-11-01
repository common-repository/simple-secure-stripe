<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\Gateways;

use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Assets\Api;
use SimpleSecureWP\SimpleSecureStripe\Integrations\WooCommerceBlocks\Payments\AbstractStripePayment;
use SimpleSecureWP\SimpleSecureStripe\Features\Link\Link;

class LinkPayment extends AbstractStripePayment {

	protected $name = 'sswps_link_checkout';

	private $link;


	public function __construct( Link $link, Api $assets ) {
		$this->link       = $link;
		$this->assets_api = $assets;
	}

	public function initialize() {
		add_filter( 'sswps/blocks_general_data', [ $this, 'add_stripe_params' ] );
	}

	public function is_active() {
		return $this->link->is_active();
	}

	public function add_stripe_params( $data ) {
		if ( $this->link->is_active() ) {
			$data['stripeParams']['betas'][] = 'link_autofill_modal_beta_1';
		}

		return $data;
	}

	public function get_payment_method_data() {
		$advanced_settings = App::get( Settings\Advanced::class );
		return [
			'name'            => $this->name,
			'launchLink'      => $this->link->is_autoload_enabled(),
			'linkIconEnabled' => $this->link->is_icon_enabled(),
			'linkIcon'        => $this->link->is_icon_enabled()
				? \sswps_get_template_html( "link/link-icon-{$advanced_settings->get_option('link_icon')}.php" )
				: null,
		];
	}

	public function get_payment_method_script_handles() {
		$this->assets_api->register_script( 'sswps-blocks-link', 'dist/link-checkout.js' );

		return [ 'sswps-blocks-link' ];
	}

	protected function is_express_checkout_enabled() {
		return true;
	}

}