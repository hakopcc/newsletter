<?php
    $categoryLimit = $module === 'listing' ? LISTING_MAX_CATEGORY_ALLOWED : MAX_CATEGORY_ALLOWED;
?>

<input type="hidden" id="module" value="<?=$module?>" />
<input type="hidden" id="categoryLoading" value="false" />
<input type="hidden" name="return_categories" value="<?=$return_categories?>">

<?php if(!empty($advertisePage)){ ?>
    <div class="col-md-12">
        <label for="categories"><?= system_showText(LANG_LABEL_CATEGORY_PLURAL); ?></label>
    </div>
    <div class="col-md-12">
        <div class="input-group custom-members-category advertise-category-custom">
            <div class="input-categories-list" id="categories">
                <?php if(!empty($arr_category)) {
                    foreach($arr_category as $category) { ?>
                        <div class="input-categories-item"><?=$category['name']?> <span class="remove-categories-item" data-id="<?=$category['value']?>"><i class="fa fa-close"></i></span></div>
                    <?php }
                } elseif ($isListingTemplate) { ?>
                    <div class="input-categories-item"><?=LANG_ALL?> <span class="remove-categories-item" data-id="all"><i class="fa fa-close"></i></span></div>
                <?php } ?>
            </div>
            <span class="input-group-btn">
                <button class="button button-bg is-primary button-browse-categories" id="browse-categories" data-onlyparents="<?=!empty($isListingTemplate)?>" type="button"><?=LANG_LABEL_BROWSE?> <i class="fa fa-angle-down"></i></button>
            </span>

            <div class="categories-list categories-list-block sponsors-category-dropdown">
                <div class="categories-list-header">
                    <div class="input-group-icon">
                        <span class="fa fa-search input-icon"></span>
                        <input type="text" class="input category-search" data-ref="categories-container" data-selectparent="false" placeholder="<?=LANG_LABEL_SEARCH?>">
                    </div>
                    <?php if ($isListingTemplate) { ?>
                        <a href="javascript:void(0)" class="create-new-category" id="add-all">+ <?=LANG_SITEMGR_ADD_ALL_CATEGORIES?></a>
                    <?php } ?>
                </div>
                <div class="categories-wrap" id="categories-container">
                </div>
            </div>
        </div>
        <div class="text-right text-danger" id="limitAlert" style="display: none;"><i class="fa fa-exclamation-circle"></i> <?=sprintf(LANG_CATEGORY_LIMIT_REACHED, $categoryLimit)?></div>
    </div>
<?php } else { ?>
    <div class="row">
        <div class="col-xs-12">
            <label for="categories"><?= system_showText(LANG_LABEL_CATEGORY_PLURAL); ?>
                <?php if ($isListingTemplate) { ?>
                    <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title='<?= system_showText(LANG_LABEL_CATEGORY_PLURAL_TOOLTIP); ?>'></i>
                <?php } ?>
            </label>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="input-group custom-members-category">
                <div class="input-categories-list" id="categories">
                    <?php if(!empty($arr_category)) {
                        foreach($arr_category as $category) { ?>
                            <div class="input-categories-item"><?=$category['name']?> <span class="remove-categories-item" data-id="<?=$category['value']?>"><i class="fa fa-close"></i></span></div>
                        <?php }
                    } elseif ($isListingTemplate) { ?>
                        <div class="input-categories-item"><?=LANG_ALL?> <span class="remove-categories-item" data-id="all"><i class="fa fa-close"></i></span></div>
                    <?php } ?>
                </div>
                <span class="input-group-btn">
                    <button class="btn btn-primary button-browse-categories" id="browse-categories" data-onlyparents="<?=!empty($isListingTemplate)?>" type="button"><?=LANG_LABEL_BROWSE?> <i class="fa fa-angle-down"></i></button>
                </span>

                <div class="categories-list categories-list-block sitemgr-category-dropdown">
                    <div class="categories-list-header">
                        <div class="input-group-icon">
                            <span class="fa fa-search input-icon"></span>
                            <input type="text" class="form-control category-search" data-ref="categories-container" data-selectparent="false" placeholder="<?=LANG_LABEL_SEARCH?>">
                        </div>
                        <?php if ($isListingTemplate) { ?>
                            <a href="javascript:void(0)" class="create-new-category" id="add-all">+ <?=LANG_SITEMGR_ADD_ALL_CATEGORIES?></a>
                        <?php } ?>
                    </div>
                    <div class="categories-wrap" id="categories-container">
                    </div>
                </div>
            </div>
            <?php if(!empty($members)) { ?>
                <div class="text-right text-danger" id="limitAlert" style="display: none;"><i class="fa fa-exclamation-circle"></i> <?=sprintf(LANG_CATEGORY_LIMIT_REACHED, $categoryLimit)?></div>
            <?php } ?>
        </div>
    </div>
<?php } ?>

<?php if(empty($members)) { ?>
    <div class="row">
        <div class="col-xs-12 text-right">
            <a href="javascript:void(0)" id="add-categories" class="create-new-category-button" ><i class="fa fa-plus"></i> <?=LANG_SITEMGR_CREATE_NEW_CATEGORY?></a>
        </div>
    </div>
    <div class="text-right text-danger" id="limitAlert" style="display: none;"><i class="fa fa-exclamation-circle"></i> <?=sprintf(LANG_CATEGORY_LIMIT_REACHED, $categoryLimit)?></div>
<?php } ?>
