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
 * @var Settings\Abstract_Settings $this
 * @var array $value
 */
$selections = (array) $value;

if ( ! empty( $data['options'] ) ) {
	$countries = array_intersect_key( WC()->countries->get_countries(), array_flip( $data['options'] ) );
} else {
	$countries = WC()->countries->get_countries();
}

asort( $countries );
?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr( $data['id'] ); ?>"><?php echo esc_html( $data['title'] ); ?><?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?></label>
	</th>
	<td class="forminp">
		<select
			multiple="multiple" name="<?php echo esc_attr( $data['id'] ); ?>[]" style="width:350px"
			data-placeholder="<?php esc_attr_e( 'Choose countries / regions&hellip;', 'simple-secure-stripe' ); ?>"
			aria-label="<?php esc_attr_e( 'Country / Region', 'simple-secure-stripe' ); ?>" class="wc-enhanced-select"
			<?php echo $this->get_custom_attribute_html( $data ); ?>>
			<?php
			if ( ! empty( $countries ) ) {
				foreach ( $countries as $key => $val ) {
					echo '<option value="' . esc_attr( $key ) . '"' . wc_selected( $key, $selections ) . '>' . esc_html( $val ) . '</option>'; // WPCS: XSS ok.
				}
			}
			?>
		</select>
		<?php echo $this->get_description_html( $data ); ?>
		<br/>
		<a class="select_all button" href="#"><?php esc_html_e( 'Select all', 'simple-secure-stripe' ); ?></a>
		<a class="select_none button" href="#"><?php esc_html_e( 'Select none', 'simple-secure-stripe' ); ?></a>
	</td>
</tr>