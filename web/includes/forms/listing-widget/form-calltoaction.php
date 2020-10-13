<form name="form_calltoaction" id="form_calltoaction">
    <input type="hidden" name="templateWidgetId" value="<?= $templateWidgetId ?>" />
    <input type="hidden" name="tabId" value="<?= $tabId ?>" />
    <input type="hidden" name="fieldType" value="link">
    <input type="hidden" name="required" id="callToActionRequired" value="<?=(!empty($content['required']))?$content['required']:'disabled'?>">
    <div class="form-group">
        <label for="callToActionTitle" class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_LABEL_ENTRY_FORM_NAME)?> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=system_showText(LANG_SITEMGR_WIDGET_LABEL_ENTRY_FORM)?>"></i></label>
        <input type="text" id="callToActionTitle" name="fieldTitle" class="form-control" placeholder="Ex: Button for sales" value="<?=$content['fieldTitle']?>" required>
    </div>
    <div class="widget-group">
        <div class="row widget-field-item is-loaded">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="" class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_LABEL_BUTTON_STYLE)?></label>
                    <div class="form-radio-group">
                        <label for="buttonStyleDefault">
                            <input type="radio" name="buttonStyle" id="buttonStyleDefault" value="default" <?=$content['buttonStyle'] === 'default' || empty($content['buttonStyle']) ? 'checked' : ''?>>
                            <div class="call-action-group">
                                <span class="call-button button-primary"><?=system_showText(LANG_SITEMGR_DEFAULT)?></span>
                            </div>
                        </label>

                        <label for="buttonStyleOutline">
                            <input type="radio" name="buttonStyle" id="buttonStyleOutline" value="outline" <?=$content['buttonStyle'] === 'outline' ? 'checked' : ''?>>
                            <div class="call-action-group">
                                <span class="call-button button-outline"><?=system_showText(LANG_SITEMGR_WIDGET_LABEL_OUTLINE)?></span>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_LABEL_ALIGNMENT)?></label>
                    <div class="form-radio-group">
                        <label for="alignmentExtended">
                            <input type="radio" name="alignment" id="alignmentExtended" value="extended" <?=$content['alignment'] === 'extended' || empty($content['alignment']) ? 'checked' : ''?>>
                            <div class="call-action-group">
                                <span class="call-button button-primary"><?=system_showText(LANG_SITEMGR_WIDGET_LABEL_EXTENDED)?></span>
                            </div>
                        </label>

                        <label for="alignmentCenter">
                            <input type="radio" name="alignment" id="alignmentCenter" value="center" <?=$content['alignment'] === 'center' ? 'checked' : ''?>>
                            <div class="call-action-group">
                                <span class="call-button button-alignment button-center"><?=system_showText(LANG_SITEMGR_COLOR_ALIGN_CENTER)?></span>
                            </div>
                        </label>
                    </div>
                    <div class="form-radio-group">
                        <label for="alignmentLeft">
                            <input type="radio" name="alignment" id="alignmentLeft" value="left" <?=$content['alignment'] === 'left' ? 'checked' : ''?>>
                            <div class="call-action-group">
                                <span class="call-button button-alignment button-left"><?=system_showText(LANG_SITEMGR_COLOR_ALIGN_LEFT)?></span>
                            </div>
                        </label>

                        <label for="alignmentRight">
                            <input type="radio" name="alignment" id="alignmentRight" value="right" <?=$content['alignment'] === 'right' ? 'checked' : ''?>>
                            <div class="call-action-group">
                                <span class="call-button button-alignment button-right"><?=system_showText(LANG_SITEMGR_COLOR_ALIGN_RIGHT)?></span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label for="" class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_LABEL_BUTTON_LABEL)?></label>
                    <input type="text" class="form-control text-center" name="buttonLabel" id="input-label-character-left" maxlength="20" value="<?=$content['buttonLabel']?>" placeholder="<?=LANG_SITEMGR_CALLTOACTION_BUTTON_PLACEHOLDER?>" required>
                    <div class="text-block characters-left"><span id="button-label-character-left"><?=empty($content['buttonLabel']) ?'20': 20 - strlen($content['buttonLabel'])?></span> <?=system_showText(LANG_SITEMGR_CHARACTERSLEFT)?></div>
                </div>
            </div>
        </div>
    </div>
</form>
