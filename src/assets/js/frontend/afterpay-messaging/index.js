import {stripe} from '@simple-secure-stripe/sswps-exports';
import AfterpayCategoryMessage from './afterpay-category';

if (typeof sswps_bnpl_shop_params !== 'undefined') {
    new AfterpayCategoryMessage(stripe, sswps_bnpl_shop_params);
}