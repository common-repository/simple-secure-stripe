<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php

global $current_section;
$tabs       = apply_filters( 'sswps/settings_nav_tabs', [] );
$last       = count( $tabs );
$idx        = 0;
$tab_active = false;
?>
<div class="sswps-admin__nav sswps-settings-nav">
	<?php foreach ( $tabs as $id => $tab ) : $idx ++ ?>
        <a class="sswps-admin__nav-tab <?php if ( $current_section === $id || ( ! $tab_active && $last === $idx ) ) {
			echo 'sswps-admin__nav-tab--active';
			$tab_active = true;
		} ?>"
           href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $id ) ); ?>"><?php echo esc_html( $tab ); ?></a>
	<?php endforeach; ?>
</div>
<div class="clear"></div>
