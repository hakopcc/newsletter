<?

/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
#                                                                    #
# This file may not be redistributed in whole or part.               #
# eDirectory is licensed on a per-domain basis.                      #
#                                                                    #
# ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
#                                                                    #
# http://www.edirectory.com | http://www.edirectory.com/license.html #
######################################################################
\*==================================================================*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/code/listingWidgetGetAjax.php
# ----------------------------------------------------------------------------------------------------

use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
if (isset($_POST["domain_id"]) && is_numeric($_POST["domain_id"])) {
    define("SELECTED_DOMAIN_ID", $_POST["domain_id"]);
}
$loadSitemgrLangs = true;

include '../../conf/loadconfig.inc.php';

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSMSession();

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
$container = SymfonyCore::getContainer();

$translator = $container->get('translator');
setting_get('sitemgr_language', $sitemgr_language);
$sitemgrLanguage = substr($sitemgr_language, 0, 2);

$listingTemplateListingWidgetService = $container->get('listingtemplate.listingwidget.service');
$listingWidgetService = $container->get('listingwidget.service');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if($_GET['action'] === 'add') {
        $listingWidget = $listingWidgetService->getOriginalListingWidget((int)$_GET['widgetId']);
        $i = 0;

        try {
            $i = random_int(20, 9999);
        } catch (Exception $e) {
        }

        /* @var ListingWidget $widget */
        $listingWidgetId = $listingWidget->getId();
        $listingTemplateWidgetId = '';

        $section = $_GET['section'];
        $tabId = $_GET['tabId'];
        $listingTemplateId = $_GET['listingTemplateId'];
        $listingWidgetModal = $listingWidget->getModal();
        $listingWidgetTitle = /** @Ignore */
            $translator->trans($listingWidget->getTitle(), [], 'widgets', $sitemgrLanguage);
        $listingWidgetTitleImg = $listingWidget->getTitle();
        $listingWidgetType = $listingWidget->getType();

        include INCLUDES_DIR . '/lists/list-listing-widgets.php';
    } elseif ($_GET['action'] === 'edit') {
        // Get Original Widget Content to translate (Widget Table)
        /* @var $originalWidget ListingWidget */
        $originalWidget = $container->get('doctrine')->getRepository('WysiwygBundle:ListingWidget')->find($_GET['widgetId']);

        // Get widget information if is already saved on database (Page_Widget Table)
        if ($_GET['templateWidgetId']) {
            $templateWidget = $container->get('doctrine')->getRepository('WysiwygBundle:ListingTemplateListingWidget')->find($_GET['templateWidgetId']);
            $returnArray['templateWidgetId'] = $templateWidget->getId();
            $returnArray['templateWidgetClass'] = system_generateFriendlyURL($templateWidget->getListingWidget()->getTitle());
        } else {
            // Use the default information to start editing a new widget
            $templateWidget = $originalWidget;
        }

        $labelsArray = json_decode($originalWidget->getContent(), true);
        // LABELS EXCEPTIONS THAT NEED A DIFFERENT TRANSLATION

        foreach ($labelsArray as $key => $label) {
            $transLabelsArray[$key] = /** @Ignore */
                $translator->trans($label, [], 'widgets', $sitemgrLanguage);
        }

        // Create return array
        $returnArray['widgetTitle'] = $translator->trans(/** @Ignore */ $originalWidget->getTitle(), [], 'widgets', $sitemgrLanguage);
        $returnArray['content'] = $templateWidget->getContent();
        $returnArray['tabId'] = $_GET['tab'];
        $returnArray['trans'] = json_encode(!empty($transLabelsArray) ? $transLabelsArray : []);

        extract($returnArray, null);
        include INCLUDES_DIR.'/modals/listing-widget/'.$_GET['modalFullName'].'.php';
    }
}
