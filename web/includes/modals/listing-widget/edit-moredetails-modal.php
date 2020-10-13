<?php

$content = json_decode($content, true);

?>

<div class="modal-dialog listing-template-widget-modal" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-header-content">
                <h4 class="modal-title" id="myModalLabel"><?=system_showText(LANG_SITEMGR_WIDGET_MORE_DETAILS)?></h4>
            </div>
            <div class="modal-header-action">
                <div class="toggle-required-widget">
                    <?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_REQUIRED)?>
                    <div class="switch-button <?=$content['required'] === 'disabled' ? 'is-disable' : 'is-enable'?>" onclick="toggleItem(this, 'moreDetailsRequired')">
                        <span class="toggle-item"></span>
                    </div>
                </div>
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
            <div class="widget-form">
                <?php include INCLUDES_DIR . '/forms/listing-widget/form-moredetails.php' ?>
                <?php include INCLUDES_DIR . '/forms/listing-widget/alert-custom-widgets.php' ?>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default btn-lg" data-dismiss="modal"><?=system_showText(LANG_CANCEL)?></button>
            <button type="button" class="btn btn-primary btn-lg buttonSave" onclick="saveListingWidget('moredetails')"><?=system_showText(LANG_SITEMGR_SAVE)?></button>
        </div>
    </div>
</div>
