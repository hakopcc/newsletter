<?php

use ArcaSolutions\WysiwygBundle\Services\CardService;

$moduleLevelInfo = $container->get('listinglevel.service')->getAllListingLevels();

$trans = $container->get('translator');
?>

<form id="form_relatedlistings" name="form_relatedlistings">
    <input type="hidden" name="templateWidgetId" value="<?= $templateWidgetId ?>" />
    <input type="hidden" name="tabId" value="<?= $tabId ?>" />
    <input type="hidden" name="fieldType" value="related">
    <input type="hidden" name="cardType" value="vertical-cards">
    <input type="hidden" name="module" value="listing">
    <div class="form-group">
        <label for="relatedListingsFieldTitle" class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_LABEL_DISPLAY_LABEL_NAME);?> <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?=system_showText(LANG_SITEMGR_WIDGET_LABEL_DISPLAY_LABEL);?>"></i></label>
        <input type="text" class="form-control" name="fieldTitle" id="relatedListingsFieldTitle" value="<?=$content['fieldTitle']?>" required>
    </div>
    <div class="widget-group">
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label for="related-listing-filter" class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_LABEL_HOW_DISPLAY_LISTINGS);?></label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <select name="filter" id="related-listing-filter" class="form-control">
                        <option value="category" data-order="false" <?= $content['filter'] === 'category' ? 'selected=selected' : '' ?>><?=system_showText(LANG_SITEMGR_EXPORT_BYCATEGORY);?></option>
                        <option value="location" data-order="false" <?= $content['filter'] === 'location' ? 'selected=selected' : '' ?>><?=system_showText(LANG_SITEMGR_EXPORT_BYLOCATION)?></option>
                        <option value="categorylocation" data-order="true" <?= $content['filter'] === 'categorylocation' ? 'selected=selected' : '' ?>><?=system_showText(LANG_SITEMGR_WIDGET_LABEL_BY_CATEGORY_LOCATION)?></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <label for="card_order1" class="form-label"><?=$trans->trans('Choose two criteria to order the results',[], 'administrator', /** @Ignore */ $sitemgrLanguage)?></label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6" id="card_order1_div">
                    <select data-trans="<?= $trans->trans('Alphabetical', [], 'administrator');?>" data-trans="<?= $trans->trans('Average reviews', [], 'administrator');?>" data-trans="<?= $trans->trans('Level', [], 'administrator');?>" data-trans="<?= $trans->trans('Most viewed', [], 'administrator');?>"
                            data-trans="<?= $trans->trans('Random', [], 'administrator');?>" data-trans="<?= $trans->trans('Recently added', [], 'administrator');?>" data-trans="<?= $trans->trans('Recently updated', [], 'administrator');?>" data-trans="<?= $trans->trans('Upcoming', [], 'administrator');?>"
                            class="form-control navLink selectize-input" id="card_order1" name="order1" data-lastoption="<?= $content['order1'] ?? '' ?>"
                            data-lastlabel="<?=isset($content['order1']) ? /** @Ignore */ $trans->trans(CardService::CRITERIA[$content['order1']], [], 'administrator',/** @Ignore */ $sitemgrLanguage) : ''?>" data-selectize-valuesort
                            required>
                        <option value=""><?= $trans->trans('Choose an Option', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></option>
                        <?php foreach (CardService::CRITERIA as $key => $value) { ?>
                            <?php if($content['order2'] !== $key && (CardService::CRITERIA_MODULES[$key] === null || in_array($content['module'],
                                        CardService::CRITERIA_MODULES[$key], true))) { ?>
                                <option value="<?= $key ?>" <?= isset($content['order1']) && $content['order1'] === $key ? 'selected=selected' : '' ?>>
                                    <?=/** @Ignore */ $trans->trans($value, [], 'administrator', $sitemgrLanguage) ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6" id="card_order2_div">
                    <select class="form-control navLink has-fake-label" id="card_order2" name="order2" data-lastoption="<?= $content['order2'] ?? '' ?>"
                            data-lastlabel="<?=isset($content['custom']['order2']) ? /** @Ignore */ $trans->trans(CardService::CRITERIA[$content['order2']], [], 'administrator',/** @Ignore */ $sitemgrLanguage) : ''?>" data-selectize-valuesort required>
                        <option value=""><?= $trans->trans('Choose an Option', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></option>
                        <?php foreach (CardService::CRITERIA as $key => $value) { ?>
                            <?php if($content['order1'] !== $key && (CardService::CRITERIA_MODULES[$key] === null || in_array($content['module'],
                                        CardService::CRITERIA_MODULES[$key], true))) { ?>
                                <option value="<?= $key ?>" <?= isset($content['order2']) && $content['order2'] === $key ? 'selected=selected' : '' ?>>
                                    <?=/** @Ignore */  $trans->trans($value, [], 'administrator', $sitemgrLanguage) ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
        <div id="itens_count_input_wrapper" class="row form-group">
            <div class="col-md-6">
                <label for="card_itens_count">
                    <?= $trans->trans('How many items would you like to display?', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?> *
                </label>
                <input type="number" min="1"  max="10" class="form-control" id="card_itens_count" name="quantity" value="<?= $content['quantity'] ?? '' ?>" required>
            </div>
            <?php if(empty($sideWidgets) && $content['columns']!= "1") {?>
                <div id="columns_input_wrapper" class="col-md-6">
                    <label for="card_columns">
                        <?= $trans->trans('Number of columns', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?> *
                    </label>
                    <select class="form-control navLink selectize-input" data-selectize id="card_columns" name="columns" required>
                        <option value=""><?= $trans->trans('Choose an Option', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></option>
                        <option value="2" <?= isset($content['columns']) && $content['columns'] == 2 ? 'selected=selected' : '' ?>>
                            2
                        </option>
                        <option value="3" <?= isset($content['columns']) && $content['columns'] == 3 ? 'selected=selected' : '' ?>>
                            3
                        </option>
                        <option value="4" <?= isset($content['columns']) && $content['columns'] == 4 ? 'selected=selected' : '' ?>>
                            4
                        </option>
                    </select>
                </div>
            <?php } else {?>
                <input type="hidden" name="columns" value="1" />
            <?php } ?>
        </div>
        <br>
        <div class="row">
            <div class="col-md-6">
                <label for="" class="form-label"><?=system_showText(LANG_SITEMGR_WIDGET_LABEL_WHICH_FILTER_LISTINGS);?></label>
                <div class="checkbox-group related-listing-checkbox">
                    <?php foreach ($moduleLevelInfo as $level) { ?>
                        <label>
                            <?php
                            if(isset($content['level'])) {
                                if(is_array($content['level'])) {
                                    $isChecked = in_array($level->getValue(), $content['level']);
                                } else {
                                    $isChecked = $level->getValue() == $content['level'];
                                }
                            }
                            ?>
                            <input type="checkbox" name="level" value="<?= $level->getValue() ?>" <?= $isChecked ? 'checked=checked' : '' ?>>
                            <?= string_ucwords($level->getName()) ?>
                        </label>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</form>
