import $ from 'jquery';
import AffirmBaseMessage from './base';

class AffirmCartMessaging extends AffirmBaseMessage {

    constructor(...params) {
        super(...params);
        this.initialize();
    }

    initialize() {
        $(document.body).on('updated_wc_div', this.updatedHtml.bind(this));
        $(document.body).on('updated_cart_totals', this.updatedHtml.bind(this));
        this.createMessage();
    }

    updatedHtml() {
        if (this.gateway.has_gateway_data()) {
            this.createMessage();
        }
    }

    getElementContainer() {
        const $el = $('#sswps-affirm-cart-container');
        if (!$el.length) {
            $('.cart_totals table.shop_table > tbody').append('<tr id="sswps-affirm-cart-container"><td colspan="2"><div id="sswps-affirm-cart-msg"></div></td></tr>');
        }
        return document.getElementById('sswps-affirm-cart-msg');
    }
}

export default AffirmCartMessaging;

