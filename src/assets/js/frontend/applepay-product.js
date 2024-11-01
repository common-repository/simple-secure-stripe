(function ($, sswps) {

    function ApplePay() {
        sswps.BaseGateway.call(this, sswps_applepay_product_params);
        this.old_qty = this.get_quantity();
    }

    /**
     * [prototype description]
     * @type {[type]}
     */
    ApplePay.prototype = $.extend({}, sswps.BaseGateway.prototype, sswps.ProductGateway.prototype, sswps.ApplePay.prototype);

    ApplePay.prototype.initialize = function () {
        if (!$('.sswps_product_payment_methods ' + this.container).length) {
            setTimeout(this.initialize.bind(this), 1000);
            return;
        }
        this.container = '.sswps_product_payment_methods ' + this.container;
        sswps.ProductGateway.call(this);
        sswps.ApplePay.prototype.initialize.call(this);
    }

    /**
     * @return {[type]}
     */
    ApplePay.prototype.canMakePayment = function () {
        sswps.ApplePay.prototype.canMakePayment.call(this).then(function () {
            $(document.body).on('change', '[name="quantity"]', this.maybe_calculate_cart.bind(this));
            $(this.container).parent().parent().addClass('active');
            if (!this.is_variable_product()) {
                this.cart_calculation();
            } else {
                if (this.variable_product_selected()) {
                    this.cart_calculation(this.get_product_data().variation.variation_id);
                } else {
                    this.disable_payment_button();
                }
            }
        }.bind(this))
    }

    ApplePay.prototype.cart_calculation = function () {
        return sswps.ProductGateway.prototype.cart_calculation.apply(this, arguments).then(function (data) {
            this.update_from_cart_calculation(data);
        }.bind(this))
    }

    /**
     * @param  {[type]}
     * @return {[type]}
     */
    ApplePay.prototype.start = function (e) {
        if (this.get_quantity() === 0) {
            e.preventDefault();
            this.submit_error(this.params.messages.invalid_amount);
        } else {
            if (!this.needs_shipping()) {
                this.add_to_cart();
            }
            sswps.ApplePay.prototype.start.apply(this, arguments);
        }
    }

    /**
     * @return {[type]}
     */
    ApplePay.prototype.append_button = function () {
        var container = document.querySelectorAll('.sswps-applepay-container');
        if (container && container.length > 1) {
            $.each(container, function (idx, node) {
                $(node).empty();
                $(node).append(this.$button.clone(true));
            }.bind(this));
            this.$button = $('.sswps-applepay-container').find('button');
        } else {
            $('#sswps-applepay-container').append(this.$button);
        }
    }

    ApplePay.prototype.maybe_calculate_cart = function () {
        this.disable_payment_button();
        this.old_qty = this.get_quantity();
        if (!this.is_variable_product() || this.variable_product_selected()) {
            this.cart_calculation().then(function () {
                if (this.is_variable_product()) {
                    this.createPaymentRequest();
                    sswps.ApplePay.prototype.canMakePayment.apply(this, arguments).then(function () {
                        this.enable_payment_button();
                    }.bind(this));
                } else {
                    this.enable_payment_button();
                }
            }.bind(this));
        }
    }

    ApplePay.prototype.found_variation = function (e) {
        sswps.ProductGateway.prototype.found_variation.apply(this, arguments);
        if (this.can_pay) {
            this.maybe_calculate_cart();
        }
    }

    new ApplePay();

}(jQuery, sswps))