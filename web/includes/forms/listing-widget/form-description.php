<form name="form_description" id="form_description">
    <input type="hidden" name="templateWidgetId" value="<?= $templateWidgetId ?>" />
    <input type="hidden" name="tabId" value="<?= $tabId ?>" />
    <input type="hidden" name="fieldType" value="textarea">
    <input type="hidden" name="required" id="descriptionRequired" value="<?=(!empty($content['required']))?$content['required']:'disabled'?>">
    <div class="widget-group">
        <div class="row widget-field-item field-options-group is-loaded">
            <div class="col-md-12 form-group">
                <label for="descriptionTypeShort"><input type="radio" name="descriptionType" value="short" <?=empty($content['descriptionType']) || $content['descriptionType'] === 'short' ? 'checked' : ''?> id="descriptionTypeShort"> <?=system_showText(LANG_SITEMGR_TEMPLATE_FIELDSHORTDESC)?> <small>(140 <?=system_showText(LANG_SITEMGR_WIDGET_CHARACTERS)?>)</small></label>
                <label for="descriptionTypeLong"><input type="radio" name="descriptionType" value="long" <?=$content['descriptionType'] === 'long' ? 'checked' : ''?> id="descriptionTypeLong"> <?=system_showText(LANG_SITEMGR_TEMPLATE_FIELDLONGDESC)?> <small>(<?=system_showText(LANG_SITEMGR_LABEL_UNLIMITED)?>)</small></label>
            </div>
        </div>
        <div class="row widget-field-item is-loaded description-options">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="descriptionFieldTitle" class="form-label"><?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_LABEL_NAME);?><i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_LABEL)?>"></i></label>
                    <input type="text" class="form-control" name="fieldTitle" value="<?=$content['fieldTitle']?>" id="descriptionFieldTitle" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_DESCRIPTION?>" required>
                    <div class="text-block hide-label-checkbox">
                        <label for="descriptionHideTitle"><input type="checkbox" name="hideTitle" id="descriptionHideTitle" value="true" <?=$content['hideTitle'] === 'true' ? 'checked' : ''?>> <?=system_showText(LANG_SITEMGR_WIDGET_HIDE_LABEL)?></label> <i class="fa fa-question-circle"></i>
                        <div class="hide-label-image">
                            <img src="<?='/'.SITEMGR_ALIAS.'/assets/img/hide-section-label.gif'?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="descriptionPlaceholder" class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_PLACEHOLDER)?></label>
                    <input type="text" class="form-control" name="placeholder" id="descriptionPlaceholder" value="<?=$content['placeholder']?>" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_ABOUT_FIELD?>">
                </div>
            </div>
        </div>
    </div>
</form>
