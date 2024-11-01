import {BaseGateway, ProductGateway, stripe} from '@simple-secure-stripe/sswps-exports';
import ProductMessaging from './affirm-product';
import CartMessaging from './affirm-cart';
import CheckoutMessaging from './affirm-checkout';
import CategoryMessaging from './affirm-category';

class AffirmGateway extends BaseGateway {
    constructor(params) {
        super(params);
    }
};

if (typeof sswps_affirm_cart_params !== 'undefined') {
    new CartMessaging(new AffirmGateway(sswps_affirm_cart_params));
}
if (typeof sswps_affirm_product_params !== 'undefined') {
    Object.assign(AffirmGateway.prototype, ProductGateway.prototype);
    new ProductMessaging(new AffirmGateway(sswps_affirm_product_params));
}
if (typeof sswps_local_payment_params !== 'undefined') {
    if (sswps_local_payment_params?.gateways?.sswps_affirm) {
        new CheckoutMessaging(new AffirmGateway(sswps_local_payment_params.gateways.sswps_affirm));
    }
}
if (typeof sswps_bnpl_shop_params !== 'undefined') {
    new CategoryMessaging(stripe, sswps_bnpl_shop_params);
}