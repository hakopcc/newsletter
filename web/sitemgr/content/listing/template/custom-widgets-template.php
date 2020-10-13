<script id="template-check-list-widget" type="text/template">
    <div class="row widget-field-item checklist-option">
        <span class="check-list-icon-selected"><i class="fa"></i></span>
        <div class="col-md-8">
            <div class="form-group">
                <input type="text" name="title" class="form-control editInformation" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER?>" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <input type="text" name="placeholder" class="form-control editInformation" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_2?>">
            </div>
        </div>
        <button type="button" class="widget-field-remove showAlert" data-ref="check-list-widget"><i class="fa fa-close"></i></button>
    </div>
</script>

<script id="template-more-details-widget" type="text/template">
    <div class="row widget-field-item field-option">
        <div class="col-md-8">
            <div class="form-group">
                <input type="text" name="title" class="form-control editInformation" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_NUMBER_BEDROOMS?>" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <input type="text" name="placeholder" class="form-control editInformation" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_ABOUT_FIELD?>">
            </div>
        </div>
        <button type="button" class="widget-field-remove showAlert" data-ref="more-details-widget"><i class="fa fa-close"></i></button>
    </div>
</script>

<script id="template-specialties-widget" type="text/template">
    <div class="row widget-field-item specialties-option">
        <div class="col-md-12">
            <div class="form-group">
                <input type="text" name="value" class="form-control editInformation" placeholder="<?=LANG_SITEMGR_INPUT_PLACEHOLDER_CARDIOLOGIST?>" required>
            </div>
        </div>
        <button type="button" class="widget-field-remove showAlert" data-ref="specialties-widget"><i class="fa fa-close"></i></button>
    </div>
</script>
