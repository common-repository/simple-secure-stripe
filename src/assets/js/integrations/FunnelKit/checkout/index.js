import $ from 'jquery';

$(document.body).on('sswps_get_billing_prefix', (e, prefix) => {
    if ($('[name="billing_same_as_shipping"]').length) {
        if (!$('[name="billing_same_as_shipping"]').is(':checked')) {
            prefix = 'shipping';
        }
    }
    return prefix;
});