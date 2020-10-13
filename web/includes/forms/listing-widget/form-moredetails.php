<form name="form_moredetails" id="form_moredetails">
    <input type="hidden" name="templateWidgetId" value="<?= $templateWidgetId ?>" />
    <input type="hidden" name="tabId" value="<?= $tabId ?>" />
    <input type="hidden" name="fieldType" value="textfields">
    <input type="hidden" name="required" id="moreDetailsRequired" value="<?=(!empty($content['required']))?$content['required']:'disabled'?>">
    <div class="form-group">
        <label for="moreDetailsFieldTitle" class="form-label"><?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_LABEL_NAME)?> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_LABEL)?>"></i></label>
        <input type="text" class="form-control" name="fieldTitle" id="moreDetailsFieldTitle" value="<?=$content['fieldTitle']?>" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_ABOUT_HOUSE?>" required>
        <div class="text-block hide-label-checkbox">
            <label for="hideTitle"><input type="checkbox" name="hideTitle" id="hideTitle" value="true" <?=$content['hideTitle'] === 'true' ? 'checked' : ''?>> <?=system_showText(LANG_SITEMGR_WIDGET_HIDE_LABEL)?></label> <i class="fa fa-question-circle"></i>
            <div class="hide-label-image">
                <img src="<?='/'.SITEMGR_ALIAS.'/assets/img/hide-section-label.gif'?>">
            </div>
        </div>
    </div>
</form>
<div class="widget-group">
    <div class="row widget-fields-labels">
        <div class="col-md-8">
            <span class="form-label"><?=system_showText(LANG_SITEMGR_DETAIL)?></span>
        </div>
        <div class="col-md-4 custom-left-space">
            <span class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_PLACEHOLDER)?></span>
        </div>
    </div>
    <form name="form_moredetailsfields" id="form_moredetailsfields">
        <div class="widget-field-list" id="more-details-widget">
            <?php
            if(empty($content['groupFields'])) { ?>
                <div class="row widget-field-item is-loaded field-option">
                    <div class="col-md-8">
                        <div class="form-group">
                            <input type="text" name="title" class="form-control editInformation" value="" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_NUMBER_BEDROOMS?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <input type="text" name="placeholder" class="form-control editInformation" value="" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_ABOUT_FIELD?>">
                        </div>
                    </div>
                    <button type="button" class="widget-field-remove showAlert" data-ref="more-details-widget"><i class="fa fa-close"></i></button>
                </div>
            <?php } else {
                foreach($content['groupFields'] as $field) { ?>
                    <div class="row widget-field-item is-loaded field-option">
                        <div class="col-md-8">
                            <div class="form-group">
                                <input type="text" name="title" class="form-control editInformation" value="<?=$field['title']?>" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_NUMBER_BEDROOMS?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="text" name="placeholder" class="form-control editInformation" value="<?=$field['placeholder']?>" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_ABOUT_FIELD?>">
                            </div>
                        </div>
                        <button type="button" class="widget-field-remove showAlert" data-ref="more-details-widget" <?=count($content['groupFields']) > 1 ? 'style="display: inline;"' : ''?>><i class="fa fa-close"></i></button>
                    </div>
                <?php }
            }?>
        </div>
        <div class="widget-template-placeholder showAlert" data-ref="more-details-widget"><i class="fa fa-plus-circle"></i></div>
    </form>
</div>
