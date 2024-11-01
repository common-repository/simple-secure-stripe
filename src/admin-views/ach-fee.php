<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @var array $data
 * @var string $field_key
 * @var string $key
 * @var Gateways\ACH $this
 */

use SimpleSecureWP\SimpleSecureStripe\Gateways;

/**
 * @var array
 */
$option = $this->get_option( $key ); /* @phpstan-ignore-line */

/**
 * @var array
 */
$fee = $this->get_option( 'fee' ); /* @phpstan-ignore-line */

?>
<tr valign="top">
	<th scope="row" class="titledesc"><label
		for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?></label>
	</th>
	<td class="forminp">
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php echo wp_kses_post( $data['title'] ); ?></span>
			</legend>
			<div class="sswps-fee-container">
				<div class="sswps-fee-col">
					<label><?php esc_html_e( 'Type', 'simple-secure-stripe' ); ?></label>
					<select
						class="select wc-enhanced-select ach-fee"
						name="<?php echo esc_attr( $field_key ); ?>[type]"
						id="<?php echo esc_attr( $field_key ); ?>[type]"
						style="<?php echo esc_attr( $data['css'] ); ?>"
						<?php disabled( $data['disabled'], true ); ?>
						<?php echo $this->get_custom_attribute_html( $data ); // WPCS: XSS ok. ?>
					>
					<?php foreach ( (array) $data['options'] as $option_key => $option_value ) : ?>
						<option value="<?php echo esc_attr( $option_key ); ?>"
							<?php selected( (string) $option_key, esc_attr( $option['type'] ) ); ?>><?php echo esc_attr( $option_value ); ?></option>
					<?php endforeach; ?>
					</select>
				</div>
				<div class="sswps-fee-col">
					<label><?php esc_html_e( 'Taxable', 'simple-secure-stripe' ); ?></label>
					<input
						type="checkbox"
						name="<?php echo esc_attr( $field_key ); ?>[taxable]"
						id="<?php echo esc_attr( $field_key ); ?>[taxable]" value="yes"
						<?php checked( $fee['taxable'], 'yes' ); ?> />
				</div>
				<div class="sswps-fee-col">
					<label><?php esc_html_e( 'Value', 'simple-secure-stripe' ); ?></label>
					<input
						class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>"
						type="text"
						name="<?php echo esc_attr( $field_key ); ?>[value]"
						id="<?php echo esc_attr( $field_key ); ?>[value]"
						style="<?php echo esc_attr( $data['css'] ); ?>"
						value="<?php echo esc_attr( $option['value'] ); ?>"
						placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>"
						<?php disabled( $data['disabled'], true ); ?>
						<?php echo $this->get_custom_attribute_html( $data ); // WPCS: XSS ok. ?>
					/>
				</div>
			</div>
			<?php echo $this->get_description_html( $data ); // WPCS: XSS ok. ?>
		</fieldset>
	</td>
</tr>
