!function(t,a){function e(){a.BaseGateway.call(this,sswps_googlepay_cart_params),a.CartGateway.call(this),window.addEventListener("hashchange",this.hashchange.bind(this))}e.prototype=t.extend({},a.BaseGateway.prototype,a.CartGateway.prototype,a.GooglePay.prototype),e.prototype.initialize=function(){this.createPaymentsClient(),this.isReadyToPay().then(function(){t(this.container).show().addClass("active").parent().addClass("active"),this.add_cart_totals_class()}.bind(this))},e.prototype.create_button=function(){a.GooglePay.prototype.create_button.apply(this,arguments),t("#sswps-googlepay-container").append(this.$button)},e.prototype.updated_html=function(){this.can_pay&&(this.create_button(),t(this.container).show().addClass("active").parent().addClass("active"),this.add_cart_totals_class())},e.prototype.payment_data_updated=function(a,e){"SHIPPING_ADDRESS"===e.callbackTrigger&&t(document.body).trigger("wc_update_cart")},new e}(jQuery,window.sswps);
//# sourceMappingURL=googlepay-cart.js.map