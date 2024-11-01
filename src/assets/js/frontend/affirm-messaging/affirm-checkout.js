import $ from 'jquery';
import AffirmBaseMessage from "./base";

class AffirmCheckoutMessage extends AffirmBaseMessage {

    constructor(...params) {
        super(...params);
        this.initialize();
    }

    initialize() {
        $(document.body).on('updated_checkout', this.updatedCheckout.bind(this));
        if (this.gateway.has_gateway_data()) {
            this.createMessage();
        }
    }

    updatedCheckout() {
        this.createMessage();
    }

    createMessage() {
        if (this.gateway.has_gateway_data()) {
            super.createMessage();
        }
    }

    getElementContainer() {
        if (!$('#sswps-affirm-message-container').length) {
            $('label[for="payment_method_sswps_affirm"]').append('<div id="sswps-affirm-message-container"></div>');
        }
        return document.getElementById('sswps-affirm-message-container');
    }
}

export default AffirmCheckoutMessage;