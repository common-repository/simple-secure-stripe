<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
/**
 * @version 1.0.0
 *
 * @var string $text
 * @var string $title
 */
use SimpleSecureWP\SimpleSecureStripe\App;
use SimpleSecureWP\SimpleSecureStripe\Plugin;
?>
<div class="sswps-offsite-notice-container">
    <div class="sswps-offsite-notice">
        <img src="<?php echo esc_url( App::get( Plugin::class )->assets_url( 'img/offsite.svg' ) ); ?>"/>
        <p><?php
            /* translators: 1: click target, 2: destination link. */
            printf( esc_html__( 'After clicking "%1$s", you will be redirected to %2$s to complete your purchase securely.', 'simple-secure-stripe' ), esc_html( $text ), esc_html( $title ) );
        ?></p>
    </div>
</div>
