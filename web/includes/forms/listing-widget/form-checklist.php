<form name="form_checklist" id="form_checklist">
    <input type="hidden" name="templateWidgetId" value="<?= $templateWidgetId ?>" />
    <input type="hidden" name="tabId" value="<?= $tabId ?>" />
    <input type="hidden" name="fieldType" value="checklist">
    <div class="row check-list-labels">
        <div class="col-md-10">
            <label for="fieldTitle" class="form-label"><?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_LABEL_NAME)?> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_LABEL)?>"></i></label>
            <input type="text" class="form-control" name="fieldTitle" id="fieldTitle" value="<?=$content['fieldTitle']?>" required>
            <div class="text-block hide-label-checkbox">
                <label for="hideTitle"><input type="checkbox" name="hideTitle" id="hideTitle" value="true" <?=$content['hideTitle'] === 'true' ? 'checked' : ''?>> <?=system_showText(LANG_SITEMGR_WIDGET_HIDE_LABEL)?></label> <i class="fa fa-question-circle"></i>
                <div class="hide-label-image">
                    <img src="<?='/'.SITEMGR_ALIAS.'/assets/img/hide-section-label.gif'?>">
                </div>
            </div>
        </div>
        <div class="col-md-2 selectize">
            <label for="icon" class="form-label"><?=system_showText(LANG_SITEMGR_CHOOSE_ICON)?></label>
            <select name="icon" id="icon" onchange="changeIcons()" class="form-control feature-icon">
                <?php foreach ($fontAwesomeIcons as $key => $value) { ?>
                    <option value="<?=$key?>" <?=(empty($content['icon']) and $key === "fa-check") ? 'selected' : (!empty($content['icon']) and $content['icon'] === $key)? 'selected': ''?>><?=$value?></option>
                <?php } ?>
                <option name="symbol" value="1"><?=system_showText(LANG_SITEMGR_CHOOSE_ICON)?></option>
            </select>
        </div>
    </div>
</form>
<div class="widget-group">
    <form name="form_checklist_options" id="form_checklist_options">
        <div class="widget-field-list" id="check-list-widget">
            <div class="row widget-fields-labels">
                <div class="col-md-8">
                    <span class="form-label"><?=system_showText(LANG_SITEMGR_OPTIONS)?></span>
                </div>
                <div class="col-md-4 custom-left-space">
                    <span class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_PLACEHOLDER)?></span>
                </div>
            </div>
            <?php if (empty($content['groupFields'])) { ?>
                <div class="row widget-field-item is-loaded checklist-option">
                    <span class="check-list-icon-selected"><i class="fa fa-check"></i></span>
                    <div class="col-md-8">
                        <div class="form-group">
                            <input type="text" name="title" class="form-control editInformation" value="" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <input type="text" name="placeholder" class="form-control editInformation" value="" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_2?>">
                        </div>
                    </div>
                    <button type="button" class="widget-field-remove showAlert" data-ref="check-list-widget"><i class="fa fa-close"></i></button>
                </div>
            <?php } else {
                foreach($content['groupFields'] as $field) { ?>
                    <div class="row widget-field-item is-loaded checklist-option">
                        <span class="check-list-icon-selected"><i class="fa <?=$content['icon']?>"></i></span>
                        <div class="col-md-8">
                            <div class="form-group">
                                <input type="text" name="title" class="form-control editInformation" value="<?=$field['title']?>" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="text" name="placeholder" class="form-control editInformation" value="<?=$field['placeholder']?>" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_2?>">
                            </div>
                        </div>
                        <button type="button" class="widget-field-remove showAlert" data-ref="check-list-widget" <?=count($content['groupFields']) > 1 ? 'style="display: inline;"' : ''?>><i class="fa fa-close"></i></button>
                    </div>
                <?php }
            } ?>
        </div>
    </form>
    <div class="widget-template-placeholder showAlert" data-ref="check-list-widget"><i class="fa fa-plus-circle"></i></div>
</div>
