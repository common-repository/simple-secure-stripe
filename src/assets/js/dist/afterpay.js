!function(t,e){function s(){}function a(s){e.BaseGateway.call(this,s),e.ProductGateway.call(this),t(document.body).on("change",'[name="quantity"]',this.mount_message.bind(this,!0))}function r(t){e.BaseGateway.call(this,t),e.CartGateway.call(this)}s.prototype.is_currency_supported=function(){return this.params.currencies.indexOf(this.get_currency())>-1},a.prototype=t.extend({},e.BaseGateway.prototype,e.ProductGateway.prototype,s.prototype,e.Afterpay.prototype),a.prototype.initialize=function(){!this.msgElement&&this.is_currency_supported()&&(this.create_element(),this.mount_message(),this.add_eligibility("#sswps-afterpay-product-msg",this.get_product_price()))},a.prototype.get_product_price=function(e){var s=t('[name="quantity"]').val();return s||(s=0),e?this.get_product_data().price_cents*parseInt(s):this.get_product_data().price*parseInt(s)},a.prototype.create_element=function(){this.msgElement=this.elements.create("afterpayClearpayMessage",t.extend({},this.params.msg_options,{amount:this.get_product_price(!0),currency:this.get_currency()}))},a.prototype.found_variation=function(){e.ProductGateway.prototype.found_variation.apply(this,arguments),this.mount_message(!0)},a.prototype.mount_message=function(e){this.msgElement&&(e&&this.msgElement.update({amount:this.get_product_price(!0),currency:this.get_currency(),isEligible:this.is_eligible(this.get_product_price())}),t("#sswps-afterpay-product-msg").length||(t(".summary .price").length?t(".summary .price").append('<div id="sswps-afterpay-product-msg"></div>'):t(".price").length&&t(t(".price")[0]).append('<div id="sswps-afterpay-product-msg"></div>')),this.msgElement.mount("#sswps-afterpay-product-msg"))},r.prototype=t.extend({},e.BaseGateway.prototype,e.CartGateway.prototype,s.prototype,e.Afterpay.prototype),r.prototype.initialize=function(){!this.msgElement&&t(this.container).length&&this.is_currency_supported()&&(this.create_element(),this.mount_message(),this.add_eligibility("#sswps-afterpay-cart-container",this.get_total_price()))},r.prototype.create_element=function(){this.msgElement=this.elements.create("afterpayClearpayMessage",t.extend({},this.params.msg_options,{amount:this.get_total_price_cents(),currency:this.get_currency()}))},r.prototype.mount_message=function(e){e&&this.msgElement&&this.msgElement.update({amount:this.get_total_price_cents(),currency:this.get_currency(),isEligible:!0}),t("#sswps-afterpay-cart-container").length||t(".cart_totals table.shop_table > tbody").append('<tr id="sswps-afterpay-cart-container"><td colspan="2"><div id="sswps-afterpay-cart-msg"></div></td></tr>'),this.msgElement.mount("#sswps-afterpay-cart-msg")},r.prototype.updated_html=function(){t(this.container).length&&this.is_currency_supported()&&(this.mount_message(!0),this.add_eligibility("#sswps-afterpay-cart-container",this.get_total_price()))},"undefined"!=typeof sswps_afterpay_product_params?new a(sswps_afterpay_product_params):"undefined"!=typeof sswps_afterpay_cart_params&&new r(sswps_afterpay_cart_params)}(jQuery,window.sswps);
//# sourceMappingURL=afterpay.js.map