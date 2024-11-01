<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @var array $icons
 * @version 1.0.0
 */
?>
<span class="sswps-card-icons-container">
	<?php foreach ( $icons as $icon => $url ): ?>
		<img
			class="sswps-card-icon <?php echo esc_attr( $icon ) ?>"
			src="<?php echo esc_url( $url ) ?>"
		/>
	<?php endforeach; ?>
</span>