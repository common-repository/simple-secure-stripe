(function ($, sswps) {

    function Utils() {
    }

    Utils.prototype.is_currency_supported = function () {
        return this.params.currencies.indexOf(this.get_currency()) > -1;
    }

    /**
     * Product page integration
     * @constructor
     */
    function AfterpayProduct(params) {
        sswps.BaseGateway.call(this, params);
        sswps.ProductGateway.call(this);
        $(document.body).on('change', '[name="quantity"]', this.mount_message.bind(this, true));
    }

    AfterpayProduct.prototype = $.extend({}, sswps.BaseGateway.prototype, sswps.ProductGateway.prototype, Utils.prototype, sswps.Afterpay.prototype);

    AfterpayProduct.prototype.initialize = function () {
        if (!this.msgElement && this.is_currency_supported()) {
            this.create_element();
            this.mount_message();
            this.add_eligibility('#sswps-afterpay-product-msg', this.get_product_price());
        }
    }

    AfterpayProduct.prototype.get_product_price = function (cents) {
        var qty = $('[name="quantity"]').val();
        if (!qty) {
            qty = 0;
        }
        if (cents) {
            return this.get_product_data().price_cents * parseInt(qty);
        }
        return this.get_product_data().price * parseInt(qty);
    }

    AfterpayProduct.prototype.create_element = function () {
        this.msgElement = this.elements.create('afterpayClearpayMessage', $.extend({}, this.params.msg_options, {
            amount: this.get_product_price(true),
            currency: this.get_currency()
        }));
    }

    AfterpayProduct.prototype.found_variation = function () {
        sswps.ProductGateway.prototype.found_variation.apply(this, arguments);
        this.mount_message(true);
    }

    AfterpayProduct.prototype.mount_message = function (update) {
        if (this.msgElement) {
            if (update) {
                this.msgElement.update({
                    amount: this.get_product_price(true),
                    currency: this.get_currency(),
                    isEligible: this.is_eligible(this.get_product_price())
                })
            }
            var $el = $('#sswps-afterpay-product-msg');
            if (!$el.length) {
                if ($('.summary .price').length) {
                    $('.summary .price').append('<div id="sswps-afterpay-product-msg"></div>');
                } else {
                    if ($('.price').length) {
                        $($('.price')[0]).append('<div id="sswps-afterpay-product-msg"></div>');
                    }
                }
            }
            this.msgElement.mount('#sswps-afterpay-product-msg');
        }

    }

    /**
     * Cart page integration
     * @constructor
     */
    function AfterpayCart(params) {
        sswps.BaseGateway.call(this, params);
        sswps.CartGateway.call(this);
    }

    AfterpayCart.prototype = $.extend({}, sswps.BaseGateway.prototype, sswps.CartGateway.prototype, Utils.prototype, sswps.Afterpay.prototype);

    AfterpayCart.prototype.initialize = function () {
        if (!this.msgElement && $(this.container).length && this.is_currency_supported()) {
            this.create_element();
            this.mount_message();
            this.add_eligibility('#sswps-afterpay-cart-container', this.get_total_price());
        }
    }

    AfterpayCart.prototype.create_element = function () {
        this.msgElement = this.elements.create('afterpayClearpayMessage', $.extend({}, this.params.msg_options, {
            amount: this.get_total_price_cents(),
            currency: this.get_currency()
        }));
    }

    AfterpayCart.prototype.mount_message = function (update) {
        if (update && this.msgElement) {
            this.msgElement.update({
                amount: this.get_total_price_cents(),
                currency: this.get_currency(),
                isEligible: true
            })
        }
        var $el = $('#sswps-afterpay-cart-container');
        if (!$el.length) {
            $('.cart_totals table.shop_table > tbody').append('<tr id="sswps-afterpay-cart-container"><td colspan="2"><div id="sswps-afterpay-cart-msg"></div></td></tr>');
        }
        this.msgElement.mount('#sswps-afterpay-cart-msg');
    }

    AfterpayCart.prototype.updated_html = function () {
        if (!$(this.container).length || !this.is_currency_supported()) {
            return;
        }
        this.mount_message(true);
        this.add_eligibility('#sswps-afterpay-cart-container', this.get_total_price());
    }

    if (typeof sswps_afterpay_product_params !== 'undefined') {
        new AfterpayProduct(sswps_afterpay_product_params);
    } else if (typeof sswps_afterpay_cart_params !== 'undefined') {
        new AfterpayCart(sswps_afterpay_cart_params);
    }

})(jQuery, window.sswps);