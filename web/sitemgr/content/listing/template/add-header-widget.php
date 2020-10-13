<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /sitemgr/content/listing/template/add-header-widget.php
# ----------------------------------------------------------------------------------------------------

use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
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
$listingTemplateListingWidgetService = $container->get('listingtemplate.listingwidget.service');
$trans = $container->get('translator');

/* Gets Lang */
setting_get('sitemgr_language', $sitemgr_language);
$sitemgrLanguage = substr($sitemgr_language, 0, 2);

/* Gets Page and Widgets */
$headerWidgets = $listingWidgetService->getListingWidgetsByTypeAndSection('detail', 'header');

$headerAlreadyAdded = $listingTemplateListingWidgetService->checkIfTemplateContainsHeader($_GET['listingTemplateId']);
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <div class="modal-header-default">
        <h4 class="modal-title" id="myModalLabel"><?= system_showText(LANG_SITEMGR_INSERT_WIDGET) ?></h4>
    </div>
</div>
<div class="modal-body">
    <div class="list-widget-templates">
        <div class="widget-list">
            <?php
            foreach ($headerWidgets as $widget) {

                $classItem = 'addWidget';

                if($widget['title'] !== ListingWidget::HEADER || !$headerAlreadyAdded) { ?>
                    <div class="item thumbnail addListingWidget"
                         data-widgetid="<?= $widget['id'] ?>" data-listingtemplateid="<?=$_GET['listingTemplateId']?>" data-title="<?=$widget['title']?>"
                         data-tabid="<?=$_GET['listingTemplateTabId']?>" data-section="<?= $widget['section'] ?>">
                        <div class="caption">
                            <h4><?= /** @Ignore */
                                $trans->trans($widget['title'], [], 'widgets', /** @Ignore */ $sitemgrLanguage) ?></h4>
                            <? $imgPath = '../../../assets/img/listing-widget-placeholder/header/'.system_generateFriendlyURL($widget['title']).'.jpg';
                            if (!file_exists(EDIRECTORY_ROOT.'/'.SITEMGR_ALIAS.'/assets/img/listing-widget-placeholder/header/'.system_generateFriendlyURL($widget['title']).'.jpg')) {
                                $imgPath = '../../../assets/img/listing-widget-placeholder/header/custom-content.jpg';
                            } ?>
                            <img src="<?= $imgPath ?>"/>
                        </div>
                    </div>
                <?php }
            } ?>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">
        <?= system_showText(LANG_SITEMGR_CANCEL) ?>
    </button>
</div>
