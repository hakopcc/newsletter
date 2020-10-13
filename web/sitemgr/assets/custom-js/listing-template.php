
<?php
//SCRIPTS FOR MANAGE TEMPLATES PAGE
if (string_strpos($_SERVER['PHP_SELF'], '/' .SITEMGR_ALIAS. '/content/' .LISTING_FEATURE_FOLDER. '/template/index.php') !== false) { ?>

    <script type="text/javascript" src="<?= DEFAULT_URL ?>/scripts/listingCards.js"></script>
    <script type="text/javascript" src="<?= DEFAULT_URL ?>/scripts/addNewListingTemplate.js"></script>

    <script>

        $(document).ready(function () {
            /**
             * 09/04/2020
             * Mateus Cabana
             * Function click button only active template
             */
            $('.disable').on('click', function (e) {
                notify.error('<?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_NOT_DELETE);?>');
            });

            $('.delete').on('click', function (e) {
                e.preventDefault();
                var idElement = $(this).data('ref');
                $.ajax({
                    url: DEFAULT_URL + "/includes/code/listingTemplateActionAjax.php",
                    data: 'action=verifyListingTemplate&template_id=' + idElement + '&domain_id=' + DOMAIN_ID ,
                    cache: false,
                    processData: false,
                    type: "POST"
                }).done(function (data) {
                    $("#custId").val(idElement)
                    var objData = jQuery.parseJSON(data);
                    //deleted template
                    if(objData.success){
                        $('#delete-id').val(idElement)
                        $('#modal-delete').modal('show');
                    }//error when deleting templete
                    else if(objData.success == false){
                        notify.error('<?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_DELETE_ERROR);?>');
                    }//if it has a listing attached to the template
                    else if (objData.error) {
                        //if the template is enabled
                        if (objData.modalEnabled){
                            $("#listingText").html( objData.count)
                            $('#remove-linked-listing').modal('show');
                        }//if the template is disabled
                        else{
                            $('#remove-not-linked-listing').modal('show');
                        }
                    }
                });
            });

            $('.modify-listing').on('click', function (e) {
                var idElement = $("#custId").val()
                $("#custId").val("")
                if($(this).attr('id') == 'delete-item'){
                    MixpanelHelper.track('Clicked on the "Let\'s do this" option after trying to delete a template');
                }else{
                    MixpanelHelper.track('Clicked on the "Modify" option after trying to delete a template');
                }
                window.location.href = DEFAULT_URL+'/'+SITEMGR_ALIAS+'/content/listing/index.php?search_listingtemplate_id='+idElement;
            });

            $('.disable-listing').on('click', function (e) {
                e.preventDefault();
                var idElement = $("#custId").val()
                $.ajax({
                    url: DEFAULT_URL + "/includes/code/listingTemplateActionAjax.php",
                    data: 'action=disableListingTemplate&template_id=' + idElement + '&domain_id=' + DOMAIN_ID ,
                    cache: false,
                    processData: false,
                    type: "POST"
                }).done(function (data) {
                    $("#custId").val("")
                    var objData = jQuery.parseJSON(data);
                    //success when disabling
                    if(objData.success){
                        MixpanelHelper.track('Disabled a template after trying to delete it');
                        window.location.href = DEFAULT_URL+'/'+SITEMGR_ALIAS+'/content/listing/template/index.php?disableSuccessfully=1';
                    }//error when disable
                    else{
                        notify.error('<?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_DISABLED_ERROR);?>');
                    }
                    $('#remove-linked-listing').modal('hide');
                });
            });

        });

        <?php if ($deletedMessage){ ?>
        <?php if($error){ ?>
        notify.error('<?=$deletedMessage;?>', '', { fadeOut: 0 });
        <?php } else { ?>
        notify.success('<?=$deletedMessage;?>');
        <?php } ?>
        <?php } ?>

        <?php if (isset($_GET['deletedSuccessfully']) && $_GET['deletedSuccessfully'] === '1') { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_DELETED);?>');
        <?php } ?>

        <?php if (isset($_GET['disableSuccessfully']) && $_GET['disableSuccessfully'] === '1') { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_DISABLED);?>');
        <?php } ?>

    </script>

<?php }

//SCRIPTS FOR ADD/EDIT TEMPLATES PAGE
if (string_strpos($_SERVER['PHP_SELF'], '/' .SITEMGR_ALIAS. '/content/' .LISTING_FEATURE_FOLDER. '/template/listing-template.php') !== false) {
    include SM_EDIRECTORY_ROOT.'/content/'.LISTING_FEATURE_FOLDER.'/template/custom-widgets-template.php';
    ?>

    <script type="text/javascript" src="<?= DEFAULT_URL ?>/scripts/listingTemplate.js"></script>

    <script>

        $('.summary-view-heading').on('click', function(){
            if(!$(this).hasClass('is-open')){
                MixpanelHelper.track('Clicked on the indented box Choose the summary view');
            }
            $(this).toggleClass('is-open');
            $(this).next().slideToggle();
        });

    function toggleItem(inputToggle, element, blockedElement) {
        let state = $(inputToggle).hasClass("is-enable");

        if(state){
            if(element=="listingTemplateState"){
                MixpanelHelper.track('Disabled a template usign the toggle button');
            }
            $(inputToggle).removeClass("is-enable").addClass("is-disable");
            $('#' + element).val('disabled');

            if(blockedElement){
                if(element!=='pricing-listing-input'){
                    $(blockedElement).addClass('is-disabled');
                    $(blockedElement+' .form-control').addClass('form-control-blocked');
                    $(blockedElement+' input').prop( "disabled", true );
                }else{
                    $(blockedElement).removeClass('is-disabled');
                    $(blockedElement+' .form-control').removeClass('form-control-blocked');
                    $(blockedElement+' input').prop( "disabled", false );
                }
            }
        } else {
            $(inputToggle).removeClass("is-disable").addClass("is-enable");
            $('#' + element).val('enabled');

                if(blockedElement){
                    if(element!=='pricing-listing-input'){
                        $(blockedElement).removeClass('is-disabled');
                        $(blockedElement+' .form-control').removeClass('form-control-blocked');
                        $(blockedElement+' input').prop( "disabled", false );
                    }else{
                        $(blockedElement).addClass('is-disabled');
                        $(blockedElement+' .form-control').addClass('form-control-blocked');
                        $(blockedElement+' input').prop( "disabled", true );
                    }
                }
            }
        }
        //show modal
        $(document).on('click', '.create-new-category-button', function(){
            MixpanelHelper.track('Clicked on Create a new category',{'Section': 'listing template form'});
            if($('.input-categories-list').length>0 && !$('.checkDisableCategory').hasClass('hide')){
                $('.checkDisableCategory').addClass('hide');
            }
            $('#modal-create-categories').modal('show');
        });

        // Custom Widget Modal functions
        $(document).on('click', '.widget-template-placeholder', function(){
            let ref = $(this).data('ref');
            let template = $('#template-'+ref).html();

            $('#' + ref).append(template);
            $('#' + ref).find('.widget-field-item:not(".is-loaded")').slideDown().addClass('is-loaded');

            if(ref === 'check-list-widget') {
                changeIcons();
            }

            let itemsCount = $('#' + ref + ' .widget-field-item').length;

            if(itemsCount > 1){
                $('#' + ref + ' .widget-field-item .widget-field-remove').fadeIn();
            }
        });

        $(document).on('click', '.widget-field-remove', function(){
            let ref = $(this).data('ref');
            let itemsCount = $('#' + ref + ' .widget-field-item').length;

            $(this).parent().slideUp(function(){
                $(this).remove();
            });

            if(itemsCount <= 2){
                $('#' + ref + ' .widget-field-remove').fadeOut();
            }
        });

        $(document).on('keyup', '#input-label-character-left', function(){
            let maxCharacter = $(this).attr('maxlength');
            let characterCount = $(this).val().length;

            if(characterCount <= maxCharacter){
                let charactersLeft = maxCharacter - characterCount;
                $('#button-label-character-left').text(charactersLeft);
            }
        });

        $(document).ready(function(){
            let itemsCount = $('#check-list-widget .widget-field-item').length;

            if(itemsCount > 1){
                $('#check-list-widget .widget-field-item .widget-field-remove').fadeIn();
            }
        });

        $(document).on('change', '#related-listing-filter', function(){
            let inputOption = $(this).find(':selected').data('order');

            if(inputOption){
                $('#related-listing-order').fadeIn();
            } else {
                $('#related-listing-order').fadeOut();
            }
        });

        $('.toggle-pricing-options').on('click', function(){
            if($(this).hasClass('is-open')){
                $(this).removeClass('is-open');
            } else {
                $(this).addClass('is-open');
            }
        });

        <?php if ($alertMessage){ ?>
            <?php if($success){ ?>
            notify.success('<?=$alertMessage;?>');
            <?php } else { ?>
            notify.error('<?=$alertMessage;?>', '', { fadeOut: 0 });
            <?php } ?>
        <?php } ?>

    </script>

    <?php
    $feedName = 'listing';
    $maxCategoryAllowed = '50';

    include SM_EDIRECTORY_ROOT.'/assets/custom-js/categories.php';
    include SM_EDIRECTORY_ROOT.'/assets/custom-js/widget.php';

} ?>

<script>
    function verifyListingTemplate(actives,status,inputToggle,element) {
        if(actives !=1){
            toggleItem(inputToggle, element)
        }else{
            if(status == 0){
                toggleItem(inputToggle, element)
            }else{
                notify.error('<?=LANG_SITEMGR_LISTINGTEMPLATE_NOT_DISABLE ?>', '', { fadeOut: 0 });
            }
        }
    }
</script>
