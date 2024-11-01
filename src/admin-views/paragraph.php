<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
use SimpleSecureWP\SimpleSecureStripe\Admin\Settings;
/**
 * @var array $data
 * @var string $field_key
 * @var Settings\Abstract_Settings $this
 */
?>
<tr valign="top">
	<th scope="row" class="titledesc"><label
			for="<?php echo esc_attr( $field_key ); ?>"
		><?php echo wp_kses_post( $data['title'] ); ?><?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?></label>
	</th>
	<td class="forminp">
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php echo wp_kses_post( $data['title'] ); ?></span>
			</legend>
			<p
				class="<?php echo esc_attr( $data['class'] ); ?>"
				<?php echo $this->get_custom_attribute_html( $data ); ?>><?php echo $data['text']; ?></p>
			<?php echo $this->get_description_html( $data ); // WPCS: XSS ok. ?>
		</fieldset>
	</td>
</tr>
