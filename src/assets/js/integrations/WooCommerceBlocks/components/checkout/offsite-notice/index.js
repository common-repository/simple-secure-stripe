import {__, sprintf} from "@wordpress/i18n";
//import {getSetting} from '@woocommerce/settings'

const data = wc.getSetting('stripeGeneralData');

export const OffsiteNotice = (
    {
        paymentText,
        buttonText = __('', 'simple-secure-stripe')
    }
) => {
    return (
        <div className="sswps-blocks-offsite-notice">
            <div>
                <img src={`${data.assetsUrl}/img/offsite.svg`}/>
                <p>{sprintf(__('After clicking "%1$s", you will be redirected to %2$s to complete your purchase securely.', 'simple-secure-stripe'), buttonText, paymentText)}</p>
            </div>
        </div>
    )
}