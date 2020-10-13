<form name="form_specialties" id="form_specialties">
    <input type="hidden" name="templateWidgetId" value="<?= $templateWidgetId ?>" />
    <input type="hidden" name="tabId" value="<?= $tabId ?>" />
    <input type="hidden" name="fieldType" value="dropdown">
    <input type="hidden" name="required" id="specialtiesRequired" value="<?=(!empty($content['required']))?$content['required']:'disabled'?>">
    <div class="row specialties-labels">
        <div class="col-md-8">
            <label for="specialtiesFieldTitle" class="form-label"><?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_LABEL_NAME)?> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_LABEL)?>"></i></label>
            <input type="text" id="specialtiesFieldTitle" name="fieldTitle" value="<?=$content['fieldTitle']?>" class="form-control" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_MEDICAL_SPECIALTY?>" required>
        </div>
        <div class="col-md-4">
            <label for="specialtiesPlaceholder" class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_PLACEHOLDER)?></label>
            <input type="text" id="specialtiesPlaceholder" name="placeholder" value="<?=$content['placeholder']?>" class="form-control" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_ABOUT_FIELD?>">
        </div>
    </div>
</form>
<div class="widget-group">
    <form name="form_specialtiesoptions" id="form_specialtiesoptions">
        <div class="widget-field-list" id="specialties-widget">
            <div class="widget-fields-labels"><span class="form-label"><?=system_showText(LANG_SITEMGR_OPTIONS)?></span></div>
            <?php
            if(empty($content['dropdownOptions'])) { ?>
                <div class="row widget-field-item is-loaded specialties-option">
                    <div class="col-md-12">
                        <div class="form-group">
                            <input type="text" name="value" value="" class="form-control editInformation" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_CARDIOLOGIST?>" required>
                        </div>
                    </div>
                    <button type="button" class="widget-field-remove showAlert" data-ref="specialties-widget" <?=count($content['dropdownOptions']) > 1 ? 'style="display: inline;"' : ''?>><i class="fa fa-close"></i></button>
                </div>
            <?php } else {
                foreach($content['dropdownOptions'] as $option) { ?>
                    <div class="row widget-field-item is-loaded specialties-option">
                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="text" name="value" value="<?=$option['value']?>" class="form-control editInformation" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_CARDIOLOGIST?>" required>
                            </div>
                        </div>
                        <button type="button" class="widget-field-remove showAlert" data-ref="specialties-widget" <?=count($content['dropdownOptions']) > 1 ? 'style="display: inline;"' : ''?>><i class="fa fa-close"></i></button>
                    </div>
            <?php }
            }?>
        </div>
        <div class="widget-template-placeholder showAlert" data-ref="specialties-widget"><i class="fa fa-plus-circle"></i></div>
    </form>
</div>
