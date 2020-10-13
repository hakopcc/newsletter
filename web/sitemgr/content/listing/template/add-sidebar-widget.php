<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /sitemgr/content/listing/template/add-sidebar-widget.php
    # ----------------------------------------------------------------------------------------------------

    use ArcaSolutions\WysiwygBundle\Entity\Widget;
    use ArcaSolutions\WysiwygBundle\Services\CardService;

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include '../../../../conf/loadconfig.inc.php';

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSMSession();
    permission_hasSMPerm();

    # ----------------------------------------------------------------------------------------------------
    # CODE
    # ----------------------------------------------------------------------------------------------------

    /* Gets the container */
    $container = SymfonyCore::getContainer();

    /* Gets the WYSIWYG and Translation services */
    $listingWidgetService = $container->get('listingwidget.service');
    $trans = $container->get('translator');

    /* Gets Lang */
    setting_get('sitemgr_language', $sitemgr_language);
    $sitemgrLanguage = substr($sitemgr_language, 0, 2);

    /* Gets Page and Widgets */
    $sideWidgets = $listingWidgetService->getListingWidgetsByTypeAndSection('detail', 'sidebar');
?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <div class="modal-header-default">
            <h4 class="modal-title" id="myModalLabel"><?= system_showText(LANG_SITEMGR_INSERT_WIDGET) ?></h4>
        </div>
        <div class="modal-header-widget-custom">
            <div class="modal-header-content">
                <a href="javascript:void(0);" class="back-button" data-widgetRef=""><i class="fa fa-angle-left"></i> <?=system_showText(LANG_SITEMGR_INSERT_WIDGET);?></a>
                <h4 class="modal-title" id="editWidgetLabel"></h4>
            </div>
            <div class="modal-header-action">
                <div class="toggle-required-widget">
                    <?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_REQUIRED)?>
                    <div class="switch-button is-disable required-toggle">
                        <span class="toggle-item"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-body">
        <div class="list-widget-templates">
            <div class="widget-input-icon">
                <span class="input-icon"><i class="fa fa-search"></i></span>
                <input type="text" class="form-control widget-templates-search" id="widget-search" placeholder="<?=system_showText(LANG_SITEMGR_SEARCH)?>">
            </div>
            <div class="widget-list">
                <?php
                    foreach ($sideWidgets as $widget) {
                        $classItem = in_array($widget['title'], $container->get('listingwidget.service')->getCustomWidgets(), true) ? 'add-template-widget-button' : 'addListingWidget';
                        $imgPath = '../../../assets/img/listing-widget-placeholder/sidebar/' . system_generateFriendlyURL($widget['title']) . '.jpg';

                        if (!file_exists(EDIRECTORY_ROOT . '/' . SITEMGR_ALIAS . '/assets/img/listing-widget-placeholder/sidebar/' . system_generateFriendlyURL($widget['title']) . '.jpg')) {
                            $imgPath = '../../../assets/img/listing-widget-placeholder/sidebar/custom-content.jpg';
                        }
                ?>
                <div class="widget-item <?=$classItem?>" data-widgetid="<?= $widget['id'] ?>" data-widget="<?= system_generateFriendlyURL($widget['title']) ?>" data-listingtemplateid="<?=$_GET['listingTemplateId']?>" data-title="<?=$trans->trans($widget['title'], [], 'widgets', $sitemgrLanguage) ?>" data-tabid="<?=$_GET['listingTemplateTabId']?>" data-section="<?= $widget['section'] ?>" data-content=<?= $widget['content'] ?>>
                    <div class="widget-title"><?=$trans->trans($widget['title'], [], 'widgets', $sitemgrLanguage);?></div>
                    <div class="widget-placeholder">
                        <img src="<?=$imgPath?>" alt="<?=$trans->trans($widget['title'], [], 'widgets', $sitemgrLanguage);?>">
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <?php include INCLUDES_DIR.'/forms/form-custom-widgets.php' ?>
    </div>
    <div class="modal-footer">
        <div class="modal-footer-default">
            <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">
                <?= system_showText(LANG_SITEMGR_CANCEL) ?>
            </button>
        </div>
        <div class="modal-footer-widget-custom">
            <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">
                <?= system_showText(LANG_SITEMGR_CANCEL) ?>
            </button>
            <button type="button" id="CustomWidgetSave" class="btn btn-primary btn-lg"
                    onclick=""
                    data-widgetid=""
                    data-section=""
                    data-tabid="">
                <?=system_showText(LANG_SITEMGR_SAVE)?>
            </button>
        </div>
    </div>
