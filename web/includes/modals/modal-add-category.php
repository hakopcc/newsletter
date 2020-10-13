<?
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/modals/modal-add-category.php
# ----------------------------------------------------------------------------------------------------

?>

<div class="modal fade" id="modal-create-categories" tabindex="-1" role="dialog" aria-labelledby="modalCreateCategoriesLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalCreateCategoriesLabel"><?=LANG_SITEMGR_LANG_SITEMGR_CREATECATEGORY?></h4>
            </div>
            <div class="modal-body">
                <form role="form" name="category" id="category" method="post" class="wysiwyg" enctype="multipart/form-data">
                    <input type="hidden" id="category_id" name="id" value="" />
                    <input type="hidden" id="parent_id" name="category_id" value="" />
                    <?=system_getFormInputSearchParams((($_POST)?($_POST):($_GET)));?>

                    <div class="form-group">
                        <label for="title"><?=system_showText(LANG_SITEMGR_TITLE)?></label>
                        <input type="text" id="title" name="title" maxlength="50" class="form-control" value="" onblur="easyFriendlyUrl(this.value, 'category_friendly_url', '<?=FRIENDLYURL_VALIDCHARS?>', '<?=FRIENDLYURL_SEPARATOR?>');" required>
                    </div>

                    <?php if (string_strpos($_SERVER['PHP_SELF'], '/content/'.LISTING_FEATURE_FOLDER.'/template/listing-template') === false) { ?>
                        <div class="categories-list categories-list-block create-categories-modal" id="parent-block">
                            <div class="categories-list-header">
                                <label for="modal-category-search"><?=LANG_SITEMGR_LABEL_PARENT?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control category-search" id="modal-category-search" autocomplete="off" data-ref="categories-wrap" data-selectparent="true" placeholder="<?=system_showText(LANG_SITEMGR_SEARCH);?>" value="">
                                    <div class="input-categories-list" id="parentCategory" style="display: none;">
                                    </div>
                                    <span class="input-group-addon open-create-categories" data-ref="categories-wrap"><i class="fa fa-angle-down"></i></span>
                                </div>
                            </div>
                            <div class="categories-wrap" id="categories-wrap">
                            </div>
                        </div>
                    <?php } ?>

                    <div class="advanced-options-categories">
                        <span class="advanced-options-title"><?=LANG_SITEMGR_ADVANCED_OPTIONS?> <i class="fa fa-angle-down"></i></span>
                        <div class="advanced-options-list">
                            <div class="form-group">
                                <label for="category_keywords"><?=ucfirst(system_showText(LANG_LABEL_KEYWORDS))?></label>
                                <input type="text" id="category_keywords" name="keywords" class="form-control tag-input" value="" placeholder="<?=system_showText(LANG_HOLDER_KEYWORDS);?>">
                            </div>

                            <div class="form-group">
                                <label class="checkbox-inline" data-featured>
                                    <input type="checkbox" name="featured">
                                    <?=system_showText(LANG_SITEMGR_FEATURED)?>
                                    <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title='<?=system_showText(LANG_SITEMGR_FEATURED_TOOLTIP)?>'></i>
                                </label>
                                <label class="checkbox-inline checkDisableCategory">
                                    <input type="checkbox" name="clickToDisable">
                                    <?=system_showText(LANG_SITEMGR_DISABLE_CATEGORY)?>
                                    <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?=system_showText(LANG_SITEMGR_DISABLED_CATEGORY_TOOLTIP)?>"></i>
                                </label>
                            </div>

                            <div class="form-group" id="thumbnail">
                                <label for=""><?=LANG_SITEMGR_THUMBNAIL?> (<?=IMAGE_CATEGORY_FULL_WIDTH;?>px x <?=IMAGE_CATEGORY_FULL_HEIGHT;?>px) <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title='<?=system_showText(LANG_SITEMGR_THUMBNAIL_TOOLTIP)?>'></i></label>

                                <div class="image-thumb">
                                    <span class="btn btn-sm btn-danger delete categoryImageDeleteButton hidden" id="btn-delete-image" data-id="">
                                        <i class="icon-ion-ios7-trash-outline"></i>
                                    </span>
                                    <div id="image-thumb" class="files">
                                        <input type="hidden" name="image_id" value="">
                                    </div>
                                </div>

                                <div class="drag-file-block" id="category-thumbnail">
                                    <input type="file" name="image-image" class="file-noinput" onchange="sendCategoryImage('category', '<?= $_SERVER['PHP_SELF'] ?>', 'uploadImage');">
                                    <div class="drag-file-recommended"><?=LANG_LABEL_RECOMMENDED_DIMENSIONS?> <?=IMAGE_CATEGORY_FULL_WIDTH?>x<?=IMAGE_CATEGORY_FULL_HEIGHT?>px. <?=LANG_MSG_MAX_FILE_SIZE?> 1.5 MB.</div>
                                </div>
                            </div>

                            <div class="form-group" id="icon">
                                <label for=""><?=system_showText(LANG_LABEL_ICON)?> (<?=ICON_CATEGORY_WIDTH;?>px x <?=ICON_CATEGORY_HEIGHT;?>px) <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title='<?=system_showText(LANG_SITEMGR_ICON_TOOLTIP)?>'></i></label>

                                <div class="image-thumb">
                                    <span class="btn btn-sm btn-danger delete iconImageDeleteButton hidden" id="btn-delete-icon" data-id="">
                                        <i class="icon-ion-ios7-trash-outline"></i>
                                    </span>
                                    <div id="icon-thumb" class="files">
                                        <input type="hidden" name="icon_id" value="">
                                    </div>
                                </div>

                                <div class="drag-file-block" id="category-icon">
                                    <input type="file" name="icon-image" class="file-noinput"
                                           onchange="sendCategoryIcon('category', '<?= $_SERVER['PHP_SELF'] ?>', 'uploadIcon');">
                                    <div class="drag-file-recommended"><?=LANG_LABEL_RECOMMENDED_DIMENSIONS?> <?=ICON_CATEGORY_WIDTH?>x<?=ICON_CATEGORY_HEIGHT?>px. <?=LANG_MSG_MAX_FILE_SIZE?> 1.5 MB.</div>
                                </div>
                            </div>

                            <?php
                            HookFire('formcategory_after_render_category', [
                                'id'             => &$id,
                                'table_category' => &$table_category,
                            ]);
                            ?>

                            <span class="section-modal-categorie-title"><?=LANG_SITEMGR_SEO_ADVANCED_OPTIONS?></span>

                            <div class="form-group">
                                <label for="page_title"><?=system_showText(LANG_SITEMGR_LABEL_PAGETITLE)?></label>
                                <input type="text" id="page_title" name="page_title" class="form-control" value="" required>
                            </div>

                            <div class="form-group">
                                <label for="category_friendly_url"><?=system_showText(LANG_LABEL_FRIENDLY_URL)?></label>
                                <input type="text" id="category_friendly_url" name="friendly_url" class="form-control" value="" onblur="easyFriendlyUrl(this.value, 'category_friendly_url', '<?=FRIENDLYURL_VALIDCHARS?>\n', '<?=FRIENDLYURL_SEPARATOR?>');" required>
                            </div>

                            <div class="form-group">
                                <label for="category_seo_keywords"><?=system_showText(LANG_SITEMGR_LABEL_METAKEYWORDS)?></label>
                                <input type="text" id="category_seo_keywords" name="seo_keywords" class="form-control tag-input" value="" placeholder="<?=system_showText(LANG_HOLDER_KEYWORDS);?>">
                            </div>

                            <div class="form-group">
                                <label for="category_seo_description"><?=system_showText(LANG_SITEMGR_LABEL_METADESCRIPTION)?></label>
                                <textarea id="category_seo_description" name="seo_description" rows="5" cols="1" class="form-control textarea-counter" data-chars="250" data-msg="<?=system_showText(LANG_MSG_CHARS_LEFT)?>"></textarea>
                            </div>

                            <div class="form-group">
                                <label><?=string_ucwords(system_showText(LANG_SITEMGR_CONTENT));?></label>
                                <div>
                                    <?php // TinyMCE Editor Init

                                    // calling CKEditor
                                    setting_get('sitemgr_language', $lang);
                                    system_addCKEditor('category_content', '', 30, 15, $lang);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=system_showText(LANG_SITEMGR_CANCEL)?></button>
                <button type="button" id="saveCategory" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_JS_LOADING)?>" data-content="<?=system_showText(LANG_SITEMGR_SAVE_CHANGES)?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES)?></button>
            </div>
        </div>
    </div>
</div>
