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
# * FILE: /includes/code/listingTemplateActionAjax.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use ArcaSolutions\ListingBundle\Entity\ListingTemplateTab;
use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;

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
$listingTemplateListingWidgetService = $container->get('listingtemplate.listingwidget.service');
$translator = $container->get('translator');
setting_get('sitemgr_language', $sitemgr_language);
$sitemgrLanguage = substr($sitemgr_language, 0, 2);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'newListingTemplate') {

        try {
            $em = $container->get('doctrine')->getManager();

            $listingTemplate = $container->get('listingtemplate.service')->createNewListingTemplate();
            $customDefaultListingWidgets = $listingTemplateListingWidgetService->getCustomDefaultListingWidgets();
            $listingTemplateListingWidgetService->buildTemplate($listingTemplate,$customDefaultListingWidgets);

            $return = [
                'success'  => true,
                'error'    => null,
                'redirect' => DEFAULT_URL.'/'.SITEMGR_ALIAS.'/content/listing/template/listing-template.php?id='.$listingTemplate->getId().'&new=1',
            ];
        } catch (Exception $e) {
            $return = ['success' => false, 'error' => $e->getMessage()];
        }

        echo json_encode($return);
        exit;
    }

    if ($_POST['action'] === 'verifyListingTemplate') {
        $listingWithType = db_getFromDBBySQL( 'listing', 'SELECT count(id) AS total FROM Listing   WHERE listingtemplate_id = '.  $_POST['template_id'],
            'array', false, SELECTED_DOMAIN_ID )[0];
        //if there is no listing linked to the template, the template is deleted and it is not the only active template
        if(empty($listingWithType['total'])) {
            try{
                $listingTemplateService = $container->get('listingtemplate.service');
                //deleted template
                $return = [
                    'success'    => true,
                ];
            }catch (Exception $e) {
                //error when deleting the template
                $return = [
                    'success'    => false,
                ];
            }
            // if you have a listing linked to the template
        } else {
            $listingTemplate = $container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->findOneBy(['id'=> $_POST['template_id']]);
            //check if the templete is enabled
            if ($listingTemplate->getStatus() == "enabled") {
                $locale = substr($container->get('settings')->getSetting('sitemgr_language'), 0, 2);
                $return = [
                    'error'    => true,
                    'modalEnabled' =>  true,
                    'count'   =>  $container->get('translator')->transChoice('This template has <strong> %listingsCount% listings</strong> linked to it. To proceed, please choose one of the options below:', $listingWithType['total'], ['%listingsCount%' => $listingWithType['total']], 'administrator', $locale),
                ];
            } else {
                $return = [
                    'error'    => true,
                    'modalEnabled' =>  false,
                ];
            }
        }
        echo json_encode($return);
        exit;
    }

    if ($_POST['action'] === 'disableListingTemplate') {
        // disables listing templete
        try {
            $listingTemplateService = $container->get('listingtemplate.service');
            $listingTemplateService->disableListingTemplate($_POST['template_id']);
            $return = [
                'success'    => true,
            ];
        } catch (Exception $e) {
            $return = [
                'success'    => false,
            ];
        }
        echo json_encode($return);
        exit;
    }

    if ($_POST['action'] === 'removeTab') {
        try {
            $listingTemplateTabService = $container->get('listingtemplatetab.service');

            $listingTemplateId = $listingTemplateTabService->deleteListingTemplateTab($_POST['tabId']);

            $return = [
                'success' => true,
                'message' => $translator->trans('Tab successfully deleted.', [], 'administrator', $sitemgrLanguage)
            ];
        } catch (Exception $exception) {
            $return = [
                'success' => false,
                'message' => $translator->trans('Something went wrong!', [], 'widgets', $sitemgrLanguage)
            ];
        }

        echo json_encode($return);
        exit;
    }

    if ($_POST['action'] === 'createTab') {
            $em = $container->get('doctrine')->getManager();

            /** @var ListingTemplate $listingTemplate */
            $listingTemplate = $container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->find($_POST['listingtemplate']);

            $order = $container->get('doctrine')->getRepository('ListingBundle:ListingTemplateTab')->getLastOrder($listingTemplate);

            $listingTemplateTab = new ListingTemplateTab();
            $listingTemplateTab->setTitle('');
            $listingTemplateTab->setOrder($order['order']);
            $listingTemplateTab->setListingTemplate($listingTemplate);
            $em->persist($listingTemplateTab);
            $em->flush();

            $return = [
                'success' => true,
                'tabId'   => $listingTemplateTab->getId()
            ];

        echo json_encode($return);
        exit;
    }
}

