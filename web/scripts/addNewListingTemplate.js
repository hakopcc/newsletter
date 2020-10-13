$(document).ready(function () {
    let addNewListingTemplateButtons = $('.addNewListingTemplate');

    addNewListingTemplateButtons.on('click', addNewListingTemplate);

    function addNewListingTemplate() {
        addNewListingTemplateButtons.off('click', addNewListingTemplate);
        $('#loading_ajax').fadeIn('fast');

        $.ajax({
            url: DEFAULT_URL + '/includes/code/listingTemplateActionAjax.php',
            data: 'action=newListingTemplate&domain_id=' + $(this).data('domain'),
            cache: false,
            processData: false,
            type: "POST"
        }).done(function (data) {
            var objData = jQuery.parseJSON(data);

            if (objData.success) {
                window.location.href = objData.redirect;
            } else {
                addNewListingTemplateButtons.on('click', addNewListingTemplate);
                notify.error(LANG_JS_IMPORT_FILE_ERROR, '', { fadeOut: 0 });
            }
        }).fail(function () {
            addNewListingTemplateButtons.on('click', addNewListingTemplate);
            notify.error(LANG_JS_IMPORT_FILE_ERROR, '', { fadeOut: 0 });
        });
    }
});
