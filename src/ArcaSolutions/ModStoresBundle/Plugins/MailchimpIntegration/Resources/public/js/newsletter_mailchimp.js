$(function () {
    $('a[href="#tab-newsletter"]').on('click', function (event) {
        $('#newsletter-message-wrapper').html('');
        $('#newsletter-widget').show();
        $('#newsletter-form').hide();
    });

    $("div#newsletter-widget>div.linkWidget").click(function () {
        $(this).removeClass('linkWidget').addClass('addWidget');
    });
});
