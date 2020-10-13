<?php

$fontAwesomeIcons = system_getFontAwesomeIcons();

$content = json_decode($content, true);

?>

<div class="modal-dialog listing-template-widget-modal" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-header-content">
                <h4 class="modal-title" id="myModalLabel"><?=system_showText(LANG_SITEMGR_LABEL_DESCRIPTION)?></h4>
            </div>
            <div class="modal-header-action">
                <div class="toggle-required-widget">
                    <?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_REQUIRED)?>
                    <div class="switch-button <?=$content['required'] === 'disabled' ? 'is-disable' : 'is-enable'?>" onclick="toggleItem(this, 'descriptionRequired')">
                        <span class="toggle-item"></span>
                    </div>
                </div>
            </div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
            <div class="widget-form">
                <?php include INCLUDES_DIR . '/forms/listing-widget/form-description.php' ?>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default btn-lg" data-dismiss="modal"><?=system_showText(LANG_CANCEL)?></button>
            <button type="button" class="btn btn-primary btn-lg" onclick="saveListingWidget('description', true)"><?=system_showText(LANG_SITEMGR_SAVE)?></button>
        </div>
    </div>
</div>
