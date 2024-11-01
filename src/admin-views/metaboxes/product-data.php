<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @var array $gateways
 * @var WC_Product $product_object
 * @var Product_Data $this
 */

use SimpleSecureWP\SimpleSecureStripe\Admin\Metaboxes\Product_Data;
use SimpleSecureWP\SimpleSecureStripe\Constants;
?>
<div
	id="sswps_product_data"
	class="panel woocommerce_stripe_panel woocommerce_options_panel hidden"
>
	<p>
		<?php esc_html_e( 'In this section you can control which gateways are displayed on the product page.', 'simple-secure-stripe' ); ?>
	</p>
	<div class="options_group">
		<input
			type="hidden" id="sswps_update_product"
			name="sswps_update_product"
		/>
		<table class="sswps-product-table wc_gateways">
			<thead>
			<tr>
				<th></th>
				<th><?php esc_html_e( 'Method', 'simple-secure-stripe' ); ?></th>
				<th><?php esc_html_e( 'Enabled', 'simple-secure-stripe' ); ?></th>
				<th><?php esc_html_e( 'Charge Type', 'simple-secure-stripe' ); ?></th>
			</thead>
			<tbody class="ui-sortable">
			<?php foreach ( $gateways as $gateway ) : ?>
				<tr data-gateway_id="<?php echo $gateway->id; ?>">
					<td class="sort">
						<div class="wc-item-reorder-nav">
							<button
								type="button" class="wc-move-up" tabindex="0"
								aria-hidden="false"
								aria-label="<?php /* Translators: %s Payment gateway name. */
								echo esc_attr(
									sprintf(
										__( 'Move the "%s" payment method up', 'simple-secure-stripe' ),
										$gateway->get_method_title()
									)
								); ?>"
							><?php esc_html_e( 'Move up', 'simple-secure-stripe' ); ?></button>
							<button
								type="button" class="wc-move-down" tabindex="0"
								aria-hidden="false"
								aria-label="<?php /* Translators: %s Payment gateway name. */
								echo esc_attr(
									sprintf(
										__( 'Move the "%s" payment method down', 'simple-secure-stripe' ),
										$gateway->get_method_title()
									)
								); ?>"
							><?php esc_html_e( 'Move down', 'simple-secure-stripe' ); ?></button>
							<input
								type="hidden" name="stripe_gateway_order[]"
								value="<?php echo esc_attr( $gateway->id ); ?>"
							/>
						</div>
					</td>
					<td>
						<?php echo esc_html( $gateway->get_method_title() ); ?>
					</td>
					<td>
						<a class="sswps-product-gateway-enabled" href="#">
							<span class="woocommerce-input-toggle woocommerce-input-toggle--<?php if ( ! $this->get_product_option( $gateway->id )->enabled() ) { ?>disabled<?php } else { ?>enabled<?php } ?>"></span>
						</a>
					</td>
					<td class="capture-type">
						<select
							name="stripe_capture_type[]" class="wc-enhanced-select"
							style="width: 100px"
						>
							<option
								value="capture"
								<?php selected( 'capture', $this->get_product_option( $gateway->id )->get_option( 'charge_type' ) ); ?>><?php esc_html_e( 'Capture', 'simple-secure-stripe' ); ?></option>
							<option
								value="authorize"
								<?php selected( 'authorize', $this->get_product_option( $gateway->id )->get_option( 'charge_type' ) ); ?>><?php esc_html_e( 'Authorize', 'simple-secure-stripe' ); ?></option>
						</select>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		woocommerce_wp_select(
			[
				'id'          => Constants::BUTTON_POSITION,
				'value'       => ( ( $position = $product_object->get_meta( Constants::BUTTON_POSITION ) ) ? $position : 'bottom' ),
				'label'       => __( 'Button Position', 'simple-secure-stripe' ),
				'options'     => [
					'bottom' => __( 'Below add to cart', 'simple-secure-stripe' ),
					'top'    => __( 'Above add to cart', 'simple-secure-stripe' ),
				],
				'desc_tip'    => true,
				'description' => __(
					'The location of the payment buttons in relation to the Add to Cart button.',
					'simple-secure-stripe'
				),
			]
		);
		?>
	</div>
	<p>
		<button class="button button-secondary sswps-save-product-data"><?php esc_html_e( 'Save', 'simple-secure-stripe' ); ?></button>
		<span class="spinner"></span>
	</p>
</div>
