jQuery(document).ready(function ($) {
    $('#opt-in').click(function () {
        var elem = $(this);
        if (!elem.data('locked')) {
            elem.data('locked', true);
            $.getJSON('personal-page/opt-in', [], function (result) {
                if (result.success) {
                    elem.attr('checked', result.optin ? 'checked' : '');
                }
                elem.data('locked', false);
            });
        }
        return false;
    });
});