!function(t){function n(){t(document.body).on("change",".gpay-button-option",this.update_button.bind(this)),this.init()}n.prototype.init=function(){this.create_payments_client(),this.update_button()},n.prototype.create_payments_client=function(){this.paymentsClient=new google.payments.api.PaymentsClient({environment:"TEST"})},n.prototype.update_button=function(){this.$button&&this.$button.remove(),this.$button=t(this.paymentsClient.createButton({onClick:function(){},buttonColor:t(".button-color").val(),buttonType:t(".button-style").val()})),t("#gpay-button").append(this.$button)},new n}(jQuery);
//# sourceMappingURL=admin-googlepay.js.map