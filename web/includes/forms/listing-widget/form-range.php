<?php
$fontAwesomeIcons = system_getFontAwesomeIcons([], false);
?>

<form name="form_range" id="form_range">
    <input type="hidden" name="templateWidgetId" value="<?= $templateWidgetId ?>" />
    <input type="hidden" name="tabId" value="<?= $tabId ?>" />
    <input type="hidden" name="fieldType" value="range">
    <div class="row widget-field-item is-loaded range-field-item">
        <div class="col-md-6">
            <label for="rangeFieldTitle" class="form-label"><?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_LABEL_NAME)?> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_LABEL)?>"></i></label>
            <input type="text" name="fieldTitle" value="<?=$content['fieldTitle']?>" id="rangeFieldTitle" class="form-control" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_RANGE?>" required>
            <div class="text-block hide-label-checkbox">
                <label for="rangeHideTitle"><input type="checkbox" name="hideTitle" id="rangeHideTitle" value="true" <?=$content['hideTitle'] === 'true' ? 'checked' : ''?>> <?=system_showText(LANG_SITEMGR_WIDGET_HIDE_LABEL)?></label> <i class="fa fa-question-circle"></i>
                <div class="hide-label-image">
                    <img src="<?='/'.SITEMGR_ALIAS.'/assets/img/hide-section-label.gif'?>">
                </div>
            </div>
        </div>
        <div class="col-md-2 selectize">
            <label for="rangeIcon" class="form-label"><?=system_showText(LANG_SITEMGR_CHOOSE_ICON)?></label>
            <select name="icon" id="rangeIcon" class="form-control feature-icon">
                <?php foreach ($fontAwesomeIcons as $key => $value) { ?>
                    <option value="<?=$key?>" <?=(empty($content['icon']) and $key === "fa-usd") ? 'selected' : (!empty($content['icon']) and $content['icon'] === $key)? 'selected': ''?>><?=$value?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="" class="form-label"><?=system_showText(LANG_SITEMGR_PRICING_RANGES)?><i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=system_showText(LANG_SITEMGR_RANGE_TIP)?>"></i></label>
            <div class="input-group input-group-range">
                <input type="number" name="minRange" class="form-control" min="0" max="10" value="<?=isset($content['minRange']) ? $content['minRange'] : '1'?>" required>
                <span class="input-group-addon" id="sizing-addon2"><?=strtolower(system_showText(LANG_SITEMGR_LABEL_TO))?></span>
                <input type="number" name="maxRange" class="form-control" min="1" max="10" value="<?=isset($content['maxRange']) ? $content['maxRange'] : '5'?>" required>
            </div>
        </div>
    </div>
</form>
