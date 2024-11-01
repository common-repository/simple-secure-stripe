<?php

namespace SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit\Checkout\Compatibility;

use SimpleSecureWP\SimpleSecureStripe\Field_Manager;
use SimpleSecureWP\SimpleSecureStripe\Integrations\FunnelKit\AssetsApi;

class ExpressButtonController {

	/**
	 * @var AbstractCompatibility[]
	 */
	protected $payment_gateways = [];

	private $id = 'paymentplugins_sswps';

	private $assets;

	public function __construct( AssetsApi $assets ) {
		$this->assets = $assets;
		$this->initialize();
	}

	protected function initialize() {
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'handle_checkout_page_found' ] );
		add_filter( 'wfacp_smart_buttons', [ $this, 'add_buttons' ], 20 );
		add_action( 'wfacp_smart_button_container_' . $this->id, [ $this, 'render_express_buttons' ] );
	}

	public function handle_checkout_page_found() {
		if ( $this->has_express_buttons() ) {
			$this->assets->enqueue_style( 'sswps-funnelkit-checkout', 'integrations/FunnelKit/checkout/styles.css' );
			$this->assets->enqueue_script( 'sswps-funnelkit-checkout', 'dist/sswps-funnelkit-checkout.js' );
		}
	}

	private function has_express_buttons() {
		foreach ( $this->get_payment_gateways() as $gateway ) {
			if ( $gateway->is_enabled() && $gateway->is_express_enabled() ) {
				return true;
			}
		}

		return false;
	}

	private function get_payment_gateways() {
		$this->initialize_gateways();

		return $this->payment_gateways;
	}

	private function get_payment_gateway_classes() {
		return [
			'sswps_googlepay'       => GooglePay::class,
			'sswps_applepay'        => ApplePay::class,
			'sswps_payment_request' => PaymentRequest::class
		];
	}

	private function initialize_gateways() {
		if ( empty( $this->payment_gateways ) ) {
			$payment_methods = WC()->payment_gateways()->payment_gateways();
			foreach ( $this->get_payment_gateway_classes() as $id => $clazz ) {
				if ( isset( $payment_methods[ $id ] ) ) {
					$this->payment_gateways[ $id ] = new $clazz( $payment_methods[ $id ] );
				}
			}
		}
	}

	public function add_buttons( $buttons ) {
		if ( $this->has_express_buttons() ) {
			$buttons[ $this->id ] = [
				'iframe' => true
			];
			remove_action( 'woocommerce_checkout_before_customer_details', [ Field_Manager::class, 'output_banner_checkout_fields' ] );
		}

		return $buttons;
	}

	public function render_express_buttons() {
		?>
        <ul class="sswps_checkout_banner_gateways sswps-wfacp-express-buttons" style="list-style: none">
			<?php foreach ( $this->payment_gateways as $gateway ): ?>
                <li class="sswps-checkout-banner-gateway banner_payment_method_<?php echo $gateway->get_payment_gateway()->id ?>">

                </li>
			<?php endforeach; ?>
        </ul>
		<?php
	}

}