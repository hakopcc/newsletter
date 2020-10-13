<?php

$fontAwesomeIcons = system_getFontAwesomeIcons([], false);

$content = json_decode($content, true);

?>

<div class="modal-dialog listing-template-widget-modal" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-header-content">
                <h4 class="modal-title" id="myModalLabel"><?=system_showText(LANG_SITEMGR_WIDGET_CHECK_LIST)?></h4>
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
            <div class="widget-form">
                <?php include INCLUDES_DIR . '/forms/listing-widget/form-checklist.php' ?>
                <?php include INCLUDES_DIR . '/forms/listing-widget/alert-custom-widgets.php' ?>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default btn-lg" data-dismiss="modal"><?=system_showText(LANG_CANCEL)?></button>
            <button type="button" class="btn btn-primary btn-lg buttonSave" onclick="saveListingWidget('checklist')"><?=system_showText(LANG_SITEMGR_SAVE)?></button>
        </div>
    </div>
</div>
