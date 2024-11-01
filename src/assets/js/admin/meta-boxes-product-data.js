/**
 * Make sure we have all of the required sswps top level items.
 *
 * @since 1.0.0
 *
 * @type {Object}
 */
window.sswps = window.sswps || {};
window.sswps.product = window.sswps.product || {};
window.sswps.product.metaboxes = window.sswps.product.metaboxes || {};

/**
 * Initialize the sswps.product.metaboxes object in a strict sandbox.
 *
 * @since 1.0.0
 *
 * @param {Object} window The global window object.
 * @param {Object} $ The jQuery object.
 * @param {Object} obj sswps.product.metaboxes
 *
 * @return {void}
 */
(function (window, $, obj) {
	'use strict';
	let $window = $(window);
	let $document = $(document);

	/**
	 * Selectors used for configuration and setup.
	 *
	 * @since 1.0.0
	 *
	 * @type {Object}
	 */
	obj.selectors = {
		gatewaysTable: 'table.wc_gateways',
		enabledGateway: 'table.wc_gateways .sswps-product-gateway-enabled',
		saveProductData: '.sswps-save-product-data',
		settingsSelect: '#stripe_product_data select',
		updateProductField: '#sswps_update_product'
	};

	obj.params = {
		loadingClass: 'woocommerce-input-toggle--loading',
		enabledClass: 'woocommerce-input-toggle--enabled',
		disabledClass: 'woocommerce-input-toggle--disabled'
	};

	/**
	 * Bind events for the product data metabox.
	 *
	 * @since 1.0.0
	 *
	 * @return {void}
	 */
	obj.bindEvents = function () {
		$(obj.selectors.gatewaysTable).find('.wc-move-down, .wc-move-up').on('click', obj.move_gateway);
		$document.on('click', obj.selectors.enabledGateway, obj.enable_gateway);
		$document.on('click', obj.selectors.saveProductData, obj.save);
		$document.on('change', obj.selectors.settingsSelect, obj.setting_changed);
	};

	/**
	 * Initializes sorting on the metabox table.
	 *
	 * @since 1.0.0
	 *
	 * @return {void}
	 */
	obj.initSorting = function () {
		$(obj.selectors.gatewaysTable).sortable({
			items: 'tr',
			axis: 'y',
			cursor: 'move',
			scrollSensitivity: 40,
			forcePlaceholderSize: true,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'wc-metabox-sortable-placeholder',
			start: function (event, ui) {
				ui.item.css('background-color', '#f6f6f6');
			},
			stop: function (event, ui) {
				ui.item.removeAttr('style');
			},
			change: function () {
				obj.setting_changed();
			}
		});
	};

	/**
	 * [Move the payment gateway up or down]
	 * @return {[type]} [description]
	 */
	obj.move_gateway = function (e) {
		let $this = $(e.currentTarget);
		let $row = $this.closest('tr');

		let moveDown = $this.is('.wc-move-down');

		if (moveDown) {
			let $next = $row.next('tr');
			if ($next && $next.length) {
				$next.after($row);
			}
		} else {
			let $prev = $row.prev('tr');
			if ($prev && $prev.length) {
				$prev.before($row);
			}
		}
		obj.setting_changed();
	};

	/**
	 * Marks the product as needing an update.
	 *
	 * @since 1.0.0
	 *
	 * @return {void}
	 */
	obj.setting_changed = function () {
		$(obj.selectors.updateProductField).val('true');
	};

	/**
	 * Enables a gateway.
	 *
	 * @since 1.0.0
	 *
	 * @param  {Event} e
	 *
	 * @return {void}
	 */
	obj.enable_gateway = function (e) {
		e.preventDefault();
		let $el = $(e.currentTarget);
		let $row = $el.closest('tr');
		let $toggle = $el.find('.woocommerce-input-toggle');

		$toggle.addClass(obj.params.loadingClass);
		$.ajax({
			url: sswps_product_params.routes.enable_gateway,
			method: 'POST',
			dataType: 'json',
			data: {
				_wpnonce: sswps_product_params._wpnonce,
				product_id: $('#post_ID').val(),
				gateway_id: $row.data('gateway_id')
			}
		}).done(function (response) {
			$toggle.removeClass(obj.params.loadingClass);
			if (response.enabled) {
				$toggle.addClass(obj.params.enabledClass).removeClass(obj.params.disabledClass);
			} else {
				$toggle.removeClass(obj.params.enabledClass).addClass(obj.params.disabledClass);
			}
		}).fail(function (xhr, errorStatus, errorThrown) {
			$toggle.removeClass(obj.params.loadingClass);
		});
	};

	/**
	 * Saves the metabox data.
	 *
	 * @since 1.0.0
	 *
	 * @param  {Event} e
	 *
	 * @return {void}
	 */
	obj.save = function (e) {
		e.preventDefault();
		let $button = $(e.currentTarget);
		let gateways = [];
		let charge_types = [];
		$('[name^="stripe_gateway_order"]').each(function (idx, el) {
			gateways.push($(el).val());
		});
		$('[name^="stripe_capture_type"]').each(function (idx, el) {
			charge_types.push({
				gateway: $(el).closest('tr').data('gateway_id'),
				value: $(el).val()
			});
		})
		$button.toggleClass('disabled').prop('disabled', true);
		$button.next('.spinner').toggleClass('is-active');
		$.ajax({
			url: sswps_product_params.routes.save,
			method: 'POST',
			dataType: 'json',
			data: {
				_wpnonce: sswps_product_params._wpnonce,
				gateways: gateways,
				charge_types: charge_types,
				product_id: $('#post_ID').val(),
				position: $('#_stripe_button_position').val()
			}
		}).done(function (response) {
			$button.toggleClass('disabled').prop('disabled', false);
			$button.next('.spinner').toggleClass('is-active');
		}).fail(function (xhr, errorStatus, errorthrown) {
			$button.toggleClass('disabled').prop('disabled', false);
			$button.next('.spinner').toggleClass('is-active');
		})
	};

	/**
	 * Handles the bootstrapping of this JS file.
	 *
	 * @since 1.0.0
	 *
	 * @return {void}
	 */
	obj.ready = function () {
		obj.initSorting();
		obj.bindEvents();
	};

	$(obj.ready);
})(window, jQuery, window.sswps.product.metaboxes);