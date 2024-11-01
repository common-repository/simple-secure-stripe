(function($) {
	function minimalist() {
		this.index = 1;
		this.total_steps = $('.sswps-steps').data('steps');
		this.updateSteppers();
		this.updateStyles();
		$(document.body).on('click', '.sswps-back', this.prev.bind(this))
			.on('click', '.sswps-next', this.next.bind(this))
			.on('updated_checkout', this.updated_checkout.bind(this));
	}

	minimalist.prototype.next = function(e) {
		e.preventDefault();
		this.index++;
		$('.sswps-minimalist-form .field-container[data-index="' + this.index + '"]').removeClass('field-container--hidden');
		$('.sswps-minimalist-form .field-container[data-index="' + (this.index - 1) + '"]').addClass('field-container--hidden');
		this.updateSteppers();
	}

	minimalist.prototype.prev = function(e) {
		e.preventDefault();
		this.index--;
		$('.sswps-minimalist-form .field-container[data-index="' + (this.index + 1) + '"]').addClass('field-container--hidden');
		$('.sswps-minimalist-form .field-container[data-index="' + this.index + '"]').removeClass('field-container--hidden');
		this.updateSteppers();
	}

	minimalist.prototype.updateText = function() {
		var text = $('.sswps-step').data('text');
		$('.sswps-step').text(text.replace('%s', this.index));
	}

	minimalist.prototype.updateSteppers = function() {
		if (this.index == 1) {
			$('.sswps-back').hide();
		} else if (this.index == this.total_steps) {
			$('.sswps-next').hide();
		} else {
			$('.sswps-next').show();
			$('.sswps-back').show();
		}
		this.updateText();
	}

	minimalist.prototype.updated_checkout = function() {
		$('.sswps-minimalist-form .field-container[data-index="' + this.index + '"]').removeClass('field-container--hidden');
		this.updateSteppers();
		this.updateStyles();
	}

	minimalist.prototype.updateStyles = function() {
		if (sswps.credit_card) {
			var width = $('ul.payment_methods').outerWidth();
			if ($('ul.payment_methods').outerWidth() < 400) {
				var options = {
					style: {
						base: {
							fontSize: '18px'
						}
					}
				};
				sswps.credit_card.cardNumber.update(options);
				sswps.credit_card.cardExpiry.update(options);
				sswps.credit_card.cardCvc.update(options);
				$('ul.payment_methods').addClass('sswps-sm');
			}
		}
	}

	new minimalist();
}(jQuery))