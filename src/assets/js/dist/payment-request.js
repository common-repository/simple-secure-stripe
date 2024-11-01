(()=>{var t,e,a;t=jQuery,e=window.sswps,t(document.body).is(".single-product")&&((a=function(){e.BaseGateway.call(this,sswps_payment_request_params),window.addEventListener("hashchange",this.hashchange.bind(this)),this.old_qty=this.get_quantity()}).prototype=t.extend({},e.BaseGateway.prototype,e.ProductGateway.prototype,e.PaymentRequest.prototype),a.prototype.initialize=function(){if(!t(this.container).length)return setTimeout(this.initialize.bind(this),1e3);e.ProductGateway.call(this),e.PaymentRequest.prototype.initialize.call(this)},a.prototype.canMakePayment=function(){e.PaymentRequest.prototype.canMakePayment.apply(this,arguments).then(function(){t(document.body).on("change",'[name="quantity"]',this.maybe_calculate_cart.bind(this)),t(this.container).parent().parent().addClass("active"),this.is_variable_product()?this.variable_product_selected()?(this.cart_calculation(),t(this.container).removeClass("processingFoundVariation")):this.disable_payment_button():this.cart_calculation()}.bind(this))},a.prototype.maybe_calculate_cart=function(t){this.disable_payment_button(),this.old_qty=this.get_quantity(),this.get_product_data().variation,this.is_variable_product()&&!this.variable_product_selected()||this.cart_calculation().then(function(){this.is_variable_product()?(this.createPaymentRequest(),this.createPaymentRequestButton(),e.PaymentRequest.prototype.canMakePayment.apply(this,arguments).then(function(){this.enable_payment_button()}.bind(this))):this.enable_payment_button()}.bind(this))},a.prototype.cart_calculation=function(){return e.ProductGateway.prototype.cart_calculation.apply(this,arguments).then(function(t){this.update_from_cart_calculation(t),this.paymentRequest.update(this.get_payment_request_update({total:{pending:!1}}))}.bind(this)).catch(function(){}.bind(this))},a.prototype.create_button=function(){t("#sswps-payment-request-container").empty(),e.PaymentRequest.prototype.create_button.apply(this,arguments),this.$button=t("#sswps-payment-request-container")},a.prototype.button_click=function(t){this.$button.is(".disabled")?t.preventDefault():0==this.get_quantity()?(t.preventDefault(),this.submit_error(this.params.messages.invalid_amount)):this.needs_shipping()||this.add_to_cart()},a.prototype.found_variation=function(){e.ProductGateway.prototype.found_variation.apply(this,arguments),this.can_pay&&this.maybe_calculate_cart()},a.prototype.block=function(){t.blockUI({message:this.adding_to_cart?this.params.messages.add_to_cart:null,overlayCSS:{background:"#fff",opacity:.6}})}),t(document.body).is(".woocommerce-cart")&&((a=function(){e.BaseGateway.call(this,sswps_payment_request_params),window.addEventListener("hashchange",this.hashchange.bind(this))}).prototype=t.extend({},e.BaseGateway.prototype,e.CartGateway.prototype,e.PaymentRequest.prototype),a.prototype.initialize=function(){e.CartGateway.call(this),e.PaymentRequest.prototype.initialize.call(this)},a.prototype.canMakePayment=function(){e.PaymentRequest.prototype.canMakePayment.apply(this,arguments).then(function(){t(this.container).addClass("active").parent().addClass("active")}.bind(this))},a.prototype.updated_html=function(){t(this.container).length||(this.can_pay=!1),this.can_pay&&this.initialize()},a.prototype.button_click=function(t){this.paymentRequest.update(this.get_payment_request_update({total:{pending:!1}}))},a.prototype.cart_emptied=function(t){this.can_pay=!1}),t(document.body).is(".woocommerce-checkout")&&((a=function(){e.BaseGateway.call(this,sswps_payment_request_params),window.addEventListener("hashchange",this.hashchange.bind(this))}).prototype=t.extend({},e.BaseGateway.prototype,e.CheckoutGateway.prototype,e.PaymentRequest.prototype),a.prototype.initialize=function(){e.CheckoutGateway.call(this),t("form.checkout").on("change",".form-row:not(.address-field) .input-text",this.update_payment_request.bind(this)),t(this.container).length&&e.PaymentRequest.prototype.initialize.call(this)},a.prototype.canMakePayment=function(){e.PaymentRequest.prototype.canMakePayment.apply(this,arguments).then(function(){if(this.show_icons(),this.banner_enabled()){t(this.banner_container).empty().show().append('<div id="sswps-payment-request-banner"></div>'),t(this.banner_container).show().addClass("active").closest(".sswps-banner-checkout").addClass("active");var e=this.stripe.elements().create("paymentRequestButton",{paymentRequest:this.paymentRequest,style:{paymentRequestButton:{type:this.params.button.type,theme:this.params.button.theme,height:this.params.button.height}}});e.on("click",this.banner_checkout.bind(this)),e.mount("#sswps-payment-request-banner")}}.bind(this))},a.prototype.create_button=function(){this.$button&&this.$button.remove(),this.$button=t('<div id="sswps-payment-request-container"></div>'),t("#place_order").after(this.$button),e.PaymentRequest.prototype.create_button.call(this),this.trigger_payment_method_selected()},a.prototype.updated_checkout=function(){t(this.container).length&&e.PaymentRequest.prototype.initialize.call(this)},a.prototype.banner_checkout=function(e){this.set_payment_method(this.gateway_id),this.set_use_new_option(!0),t('[name="terms"]').prop("checked",!0)},a.prototype.on_token_received=function(){e.CheckoutGateway.prototype.on_token_received.apply(this,arguments),this.fields.toFormFields(),this.payment_request_options.requestShipping&&this.maybe_set_ship_to_different(),this.checkout_fields_valid()&&this.get_form().trigger("submit")},a.prototype.update_payment_request=function(){t(this.container).length&&e.PaymentRequest.prototype.initialize.call(this)},a.prototype.show_icons=function(){t(this.container).length&&t(this.container).find(".sswps-paymentRequest-icon.gpay").show()}),new a})();
//# sourceMappingURL=payment-request.js.map