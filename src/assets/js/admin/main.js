(function ($) {
    $(document.body).on('click', '#sswps-signup', function (e) {
        e.preventDefault();
        submitSigupData($(e.currentTarget));
    }).on('click', '.sswps-notice .dismiss', removeNotice);

    function getLoaderHtml() {
        return '<div class="sswps-loader">' +
            '<div></div>' +
            '<div></div>' +
            '<div></div>' +
            '</div>';
    }

    function removeLoader(el) {
        $(el).find('.sswps-loader').remove();
    }

    function addSuccessNotice(msg) {
        addNotice('<span class="dashicons dashicons-yes"></span><div>' + msg + '</div>', 'success');
    }

    function addErrorNotice(msg, className) {
        addNotice('<span class="dashicons dashicons-info"></span><div>' + msg + '</div>', 'error');
    }

    function addNotice(msg, className) {
        $(document.body).append('<div class="sswps-notice ' + className + '">' + msg + '<div class="dismiss"><span class="dashicons dashicons-dismiss"></span></div></div>');
        setTimeout(removeNotice.bind(null, {
            currentTarget: $('.sswps-notice').last()[0]
        }), 5000);
    }

    function removeNotice(e) {
        $(e.currentTarget).closest('.sswps-notice').remove();
    }

}(jQuery));