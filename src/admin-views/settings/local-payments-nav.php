<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
global $current_section;
$tabs = apply_filters( 'sswps/local_gateways_tab', [] );
ksort($tabs);
?>
<div class="sswps-admin__advanced-nav local-gateways">
	<?php foreach ( $tabs as $id => $tab ) : ?>
		<a class="sswps-admin__advanced-nav-link nav-link
			<?php
			if ( $current_section === $id ) {
				echo 'sswps-admin__advanced-nav-link--active nav-link-active';
			}
			?>
		"
		href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $id ); ?>"><?php echo esc_html( $tab ); ?></a>
	<?php endforeach; ?>
</div>
<div class="clear"></div>
