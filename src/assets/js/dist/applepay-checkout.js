!function(t,e){function i(){e.BaseGateway.call(this,sswps_applepay_checkout_params)}i.prototype=t.extend({},e.BaseGateway.prototype,e.CheckoutGateway.prototype,e.ApplePay.prototype),i.prototype.initialize=function(){e.CheckoutGateway.call(this),t("form.checkout").on("change",".form-row:not(.address-field) .input-text",this.update_payment_request.bind(this)),t(this.container).length&&e.ApplePay.prototype.initialize.call(this)},i.prototype.canMakePayment=function(){e.ApplePay.prototype.canMakePayment.apply(this,arguments).then(function(){if(this.banner_enabled()){var e=t(this.params.button);e.addClass("banner-checkout"),e.on("click",this.start.bind(this)),t(this.banner_container).empty().append(e),t(this.banner_container).show().addClass("active").closest(".sswps-banner-checkout").addClass("active")}}.bind(this))},i.prototype.append_button=function(){t("#place_order").after(this.$button),this.trigger_payment_method_selected()},i.prototype.updated_checkout=function(){t(this.container).length&&e.ApplePay.prototype.initialize.call(this)},i.prototype.start=function(i){t(i.target).is(".banner-checkout")&&(this.set_payment_method(this.gateway_id),this.set_use_new_option(!0),t('[name="terms"]').prop("checked",!0)),e.ApplePay.prototype.start.apply(this,arguments)},i.prototype.on_token_received=function(){e.CheckoutGateway.prototype.on_token_received.apply(this,arguments),this.payment_request_options.requestShipping&&this.maybe_set_ship_to_different(),this.fields.toFormFields({update_shipping_method:!1}),this.checkout_fields_valid()&&this.get_form().trigger("submit")},i.prototype.update_payment_request=function(){t(this.container).length&&e.ApplePay.prototype.initialize.call(this)},new i}(jQuery,window.sswps);
//# sourceMappingURL=applepay-checkout.js.map