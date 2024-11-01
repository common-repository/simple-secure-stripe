<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
/**
 * @var Settings\API $this
 * @var string $field_key
 * @var array  $data
 */
?>
<tr valign="top">
	<th scope="row" class="titledesc"><label
			for="<?php echo esc_attr( $field_key ); ?>"
		><?php echo wp_kses_post( $data['title'] ); ?><?php echo wp_kses_post( $this->get_tooltip_html( $data ) ); // WPCS: XSS ok. ?></label>
	</th>
	<td
		class="forminp
	<?php
		if ( $data['active'] ) {
			?>
  active<?php } ?>"
	>
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php echo wp_kses_post( $data['title'] ); ?></span>
			</legend>
			<?php echo wp_kses_post( $this->get_description_html( $data ) ); // WPCS: XSS ok. ?>
			<?php if ( $data['active'] ) : ?>
				<button class="sswp-button--secondary sswp-button--connection-test">Connection Test</button>
			<?php endif; ?>
			<label for="<?php echo esc_attr( $field_key ); ?>">
				<a
					href="<?php echo esc_url( $data['connect_url'] ); ?>"
					type="submit"
					class="sswp-button--primary <?php echo esc_attr( $data['active'] ? 'sswp-button--disconnect' : '' ); ?> <?php echo esc_attr( $data['class'] ); ?>"
					name="<?php echo esc_attr( $field_key ); ?>"
					id="<?php echo esc_attr( $field_key ); ?>"
					style="<?php echo esc_attr( $data['css'] ); ?>"
					value="<?php echo esc_attr( $field_key ); ?>" <?php echo $this->get_custom_attribute_html( $data ); // WPCS: XSS ok. ?>
				><span><?php echo wp_kses_post( $data['label'] ); ?></span></a>
			</label>
		</fieldset>
	</td>
</tr>
