<?php 
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
use SimpleSecureWP\SimpleSecureStripe\Constants;
/**
 * @var null|array $installments
 * @version 1.0.0
 */
if ( is_null( $installments ) ) {
	$installments = array( 'none' => array( 'text' => esc_html__( 'Fill out card form for eligibility.', 'simple-secure-stripe' ) ) );
}
?>
<div class="sswps-installment-container">
    <label class="installment-label">
		<?php esc_html_e( 'Pay in installments:', 'simple-secure-stripe' ); ?>
        <div class="sswps-installment-loader__container">
            <div class="sswps-installment-loader" style="display: none">
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </label>
	<?php woocommerce_form_field( Constants::INSTALLMENT_PLAN, array(
		'id'          => Constants::INSTALLMENT_PLAN,
		'type'        => 'select',
		'options'     => wp_list_pluck( $installments, 'text' ),
		'class'       => array( 'sswps-installment-options' ),
		'input_class' => array( 'input-text' )
	), array_keys( $installments )[0] ) ?>
</div>
