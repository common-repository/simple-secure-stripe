(function ($, sswps) {

    /**
     * @constructor
     */
    function GPay() {
        sswps.BaseGateway.call(this, sswps_googlepay_product_params);
        window.addEventListener('hashchange', this.hashchange.bind(this));
    }

    /**
     * [prototype description]
     * @type {[type]}
     */
    GPay.prototype = $.extend({}, sswps.BaseGateway.prototype, sswps.ProductGateway.prototype, sswps.GooglePay.prototype);

    /**
     * @return {[type]}
     */
    GPay.prototype.initialize = function () {
        if (!$(this.container).length) {
            return setTimeout(this.initialize.bind(this), 1000);
        }
        sswps.ProductGateway.call(this);
        this.createPaymentsClient();
        this.isReadyToPay().then(function () {
            $(document.body).on('change', '[name="quantity"]', this.maybe_calculate_cart.bind(this));
            $(this.container).show();
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

    GPay.prototype.maybe_calculate_cart = function () {
        this.disable_payment_button();
        if (!this.is_variable_product() || this.variable_product_selected()) {
            this.cart_calculation().then(function (data) {
                this.enable_payment_button();
            }.bind(this)).catch(function () {
                this.enable_payment_button();
            }.bind(this));
        }
    }

    GPay.prototype.found_variation = function () {
        sswps.ProductGateway.prototype.found_variation.apply(this, arguments);
        if (this.can_pay) {
            this.maybe_calculate_cart();
        }
    }

    GPay.prototype.cart_calculation = function () {
        return sswps.ProductGateway.prototype.cart_calculation.apply(this, arguments).then(function (data) {
            this.update_from_cart_calculation(data);
        }.bind(this))
    }

    /**
     * @return {[type]}
     */
    GPay.prototype.create_button = function () {
        var container = document.querySelectorAll('.sswps-googlepay-product-container');
        if (container && container.length > 1) {
            if (this.$button) {
                this.$button.remove();
            }
            $.each(container, function (idx, node) {
                var $button = $(this.paymentsClient.createButton(this.get_button_options()));
                $button.addClass('gpay-button-container');
                if (this.is_rectangle_button()) {
                    $button.find('button').removeClass('new_style');
                }
                $(node).append($button);
            }.bind(this));
            this.$button = $('.sswps-googlepay-product-container').find('.gpay-button-container');
        } else {
            sswps.GooglePay.prototype.create_button.apply(this, arguments);
            $('#sswps-googlepay-container').append(this.$button);
        }

        // check for variations
        if (this.is_variable_product()) {
            if (!this.variable_product_selected()) {
                this.disable_payment_button();
            } else {
                this.enable_payment_button();
            }
        }
    }

    /**
     * @return {[type]}
     */
    GPay.prototype.get_button = function () {
        return this.$button.find('button');
    }

    /**
     * @return {[type]}
     */
    GPay.prototype.start = function () {
        if (this.get_quantity() > 0) {
            if (!this.needs_shipping()) {
                this.add_to_cart();
            }
            sswps.GooglePay.prototype.start.apply(this, arguments);
        } else {
            this.submit_error(this.params.messages.invalid_amount);
        }
    }

    GPay.prototype.update_payment_data = function (data) {
        return sswps.GooglePay.prototype.update_payment_data.call(this, data, this.get_add_to_cart_data());
    }

    new GPay();

}(jQuery, sswps))