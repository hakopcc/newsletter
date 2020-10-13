<form name="form_linkedlistings" id="form_linkedlistings">
    <input type="hidden" name="templateWidgetId" value="<?= $templateWidgetId ?>" />
    <input type="hidden" name="tabId" value="<?= $tabId ?>" />
    <input type="hidden" name="fieldType" value="listing">
    <div class="form-group">
        <label for="linkedListingFieldTitle" class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_LABEL_ENTRY_FORM_NAME)?> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=system_showText(LANG_SITEMGR_WIDGET_LABEL_ENTRY_FORM)?>"></i></label>
        <input type="text" class="form-control" name="fieldTitle" id="linkedListingFieldTitle" value="<?=$content['fieldTitle']?>" required placeholder="<?=LANG_SITEMGR_INPUT_LABEL_PLACEHOLDER_LIKED_LISTING?>">
    </div>
    <div class="widget-group">
        <div class="form-group">
            <label for="linkedListingTitle" class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_LABEL_DISPLAY_LABEL_NAME)?><i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=system_showText(LANG_SITEMGR_WIDGET_LABEL_DISPLAY_LABEL);?>"></i></label>
            <input type="text" class="form-control" name="widgetTitle" id="linkedListingTitle" value="<?=$content['widgetTitle']?>" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_LIKED_LISTING?>">
        </div>
    </div>
</form>
