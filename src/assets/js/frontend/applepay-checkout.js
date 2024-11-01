(function ($, sswps) {

    /**
     * @constructor
     */
    function ApplePay() {
        sswps.BaseGateway.call(this, sswps_applepay_checkout_params);
    }

    /**
     * [prototype description]
     * @type {[type]}
     */
    ApplePay.prototype = $.extend({}, sswps.BaseGateway.prototype, sswps.CheckoutGateway.prototype, sswps.ApplePay.prototype);

    ApplePay.prototype.initialize = function () {
        sswps.CheckoutGateway.call(this);
        $('form.checkout').on('change', '.form-row:not(.address-field) .input-text', this.update_payment_request.bind(this));
        if ($(this.container).length) {
            sswps.ApplePay.prototype.initialize.call(this);
        }
    }

    ApplePay.prototype.canMakePayment = function () {
        sswps.ApplePay.prototype.canMakePayment.apply(this, arguments).then(function () {
            if (this.banner_enabled()) {
                var $button = $(this.params.button);
                $button.addClass('banner-checkout');
                $button.on('click', this.start.bind(this));
                $(this.banner_container).empty().append($button);
                $(this.banner_container).show().addClass('active').closest('.sswps-banner-checkout').addClass('active');
            }
        }.bind(this))
    }

    /**
     * @return {[type]}
     */
    ApplePay.prototype.append_button = function () {
        $('#place_order').after(this.$button);
        this.trigger_payment_method_selected();
    }

    ApplePay.prototype.updated_checkout = function () {
        if ($(this.container).length) {
            sswps.ApplePay.prototype.initialize.call(this);
        }
    }

    /**
     * [Wrapper for main start function]
     * @param  {[@event]} e [description]
     */
    ApplePay.prototype.start = function (e) {
        if ($(e.target).is('.banner-checkout')) {
            this.set_payment_method(this.gateway_id);
            this.set_use_new_option(true);
            $('[name="terms"]').prop('checked', true);
        }
        sswps.ApplePay.prototype.start.apply(this, arguments);
    }

    ApplePay.prototype.on_token_received = function () {
        sswps.CheckoutGateway.prototype.on_token_received.apply(this, arguments);
        if (this.payment_request_options.requestShipping) {
            this.maybe_set_ship_to_different();
        }
        this.fields.toFormFields({update_shipping_method: false});
        if (this.checkout_fields_valid()) {
            this.get_form().trigger('submit');
        }
    }

    ApplePay.prototype.update_payment_request = function () {
        if ($(this.container).length) {
            sswps.ApplePay.prototype.initialize.call(this);
        }
    }

    new ApplePay();

}(jQuery, window.sswps))