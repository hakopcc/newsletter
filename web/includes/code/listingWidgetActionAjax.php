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
# * FILE: /includes/code/listingWidgetActionAjax.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
use ArcaSolutions\ListingBundle\Entity\ListingTemplateTab;
use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use Doctrine\ORM\Mapping\ClassMetadata;

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
$listingWidgetService = $container->get('listingwidget.service');
$translator = $container->get('translator');
setting_get('sitemgr_language', $sitemgr_language);
$sitemgrLanguage = substr($sitemgr_language, 0, 2);
$em = $container->get('doctrine')->getManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['removeWidget'])) {
        $return = [
            'success' => false,
            'errorMessage' => $translator->trans('Something went wrong!', [], 'widgets', /** @Ignore */
                $sitemgrLanguage),
        ];

        if (($_POST['listingTemplateWidgetId'] !== 'null' && $listingTemplateListingWidgetService->deleteListingWidgetFromListingTemplate($_POST['listingTemplateWidgetId'])) || $_POST['listingTemplateWidgetId'] === 'null') {

            $return = [
                'success' => true,
                'message' => $translator->trans('Widget successfully deleted.', [], 'widgets', /** @Ignore */
                    $sitemgrLanguage),
            ];
        }
    }

    // Prepare content to be saved on the Page_Widget table
    if (!empty($_POST['contentArr'])) {
        $contentArr = json_decode($_POST['contentArr'], true);
        $templateWidgetId = null;
        $page = null;
        $widget = null;
        $theme = null;
        $widgetContent = [];
        $minRange = null;
        $attributes = [];
        $saveWidgetForAllPages = null;

        foreach ($contentArr as $content) {
            switch ($content['name']) {
                case 'templateWidgetId':
                    $templateWidgetId = $content['value'];
                    break;
                case 'tabId':
                    $tabId = $content['value'];
                    break;
                case 'customHtml':
                    $widgetContent[$content['name']] = $_POST['customHtml'] ?: '';
                    break;
                case 'fieldType':
                    $fieldType = $content['value'];
                    break;
                case 'minRange':
                    $attributes['minRange'] = $content['value'];
                    $widgetContent[$content['name']] = $content['value'];
                    break;
                case 'maxRange':
                    $attributes['maxRange'] = $content['value'];
                    $widgetContent[$content['name']] = $content['value'];
                    break;
                case 'dropdownOptions':
                    $attributes['dropdownOptions'] = $content['value'];
                    $widgetContent[$content['name']] = $content['value'];
                    break;
                case 'descriptionType':
                    $attributes['descriptionType'] = $content['value'];
                    $widgetContent[$content['name']] = $content['value'];
                    break;
                case 'icon':
                    $attributes['icon'] = $content['value'];
                    $widgetContent[$content['name']] = $content['value'];
                    break;
                default:
                    $widgetContent[$content['name']] = $content['value'];
                    break;
            }
        }

        $return = [
            'success' => false,
            'errorMessage' => [
                $translator->trans('Something wrong', [], 'widgets', /** @Ignore */
                    $sitemgrLanguage),
            ],
        ];

        if (empty($tabId)) {
            echo json_encode($return);
            exit;
        }

        if ($templateWidgetId) {
            $returnWidget = $listingTemplateListingWidgetService->saveWidgetContent($templateWidgetId, json_encode($widgetContent), json_encode($attributes));
        } else {
            /** @var ListingTemplate $listingTemplate */
            $listingTemplate = $container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->find($_POST['templateId']);

            /** @var ListingWidget $listingWidget */
            $listingWidget = $container->get('doctrine')->getRepository('WysiwygBundle:ListingWidget')->find($_POST['widgetId']);

            /** @var ListingTemplateTab $listingTab */
            $listingTab = $container->get('doctrine')->getRepository('ListingBundle:ListingTemplateTab')->find($tabId);

            if($listingTab === null && !empty($tabId)) {
                $locale = substr($container->get('settings')->getSetting('sitemgr_language'), 0, 2);

                $lastOrder = $container->get('doctrine')->getRepository('ListingBundle:ListingTemplateTab')->getLastOrder($listingTemplate);

                $listingTab = new ListingTemplateTab($tabId);
                $listingTab->setTitle($container->get('translator')->trans('New Tab', [], 'administrator', $locale));
                $listingTab->setListingTemplate($listingTemplate);
                $listingTab->setOrder($lastOrder['order']);
                $em->persist($listingTab);

                $metadata = $em->getClassMetaData(ListingTemplateTab::class);
                $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
                $em->flush();
                $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_IDENTITY);
            }

            $returnWidget = $listingTemplateListingWidgetService->saveListingWidget(
                json_encode($widgetContent), $listingTemplate, $listingWidget, $listingTab, null, $fieldType, json_encode($attributes)
            );
            $isNew = true;
        }

        if ($returnWidget) {
            $namesOption = [
                ListingWidget::CHECK_LIST,
                ListingWidget::RANGE,
                ListingWidget::SPECIALTIES,
                ListingWidget::CALL_TO_ACTION,
                ListingWidget::MORE_DETAILS,
                ListingWidget::DESCRIPTION,
                ListingWidget::LINKED_LISTINGS,
                ListingWidget::RELATED_LISTINGS
            ];
            if (in_array($returnWidget->getListingWidget()->getTitle(), $namesOption, true)) {
                $option =[];
                $option['Template'] = $returnWidget->getListingTemplate()->getTitle();
                $option['Widget name'] = $returnWidget->getListingWidget()->getTitle();
                $widgetEdit = json_decode($returnWidget->getContent());
                switch ($returnWidget->getListingWidget()->getTitle()) {
                    case ListingWidget::CHECK_LIST :
                        if (!empty($widgetEdit->hideTitle)) {
                            $option['Hide label'] = 'yes';
                        } else {
                            $option['Hide label'] = 'no';
                        }

                        $countOptions = 1;
                        foreach ($widgetEdit->groupFields as $fields) {
                            $option['Option '.$countOptions++] = $fields->title;
                        }

                        $fontAwesomeIcons = system_getFontAwesomeIcons();
                        if (!empty($fontAwesomeIcons[$widgetEdit->icon])) {
                            $option['Icon'] = explode('; ',$fontAwesomeIcons[$widgetEdit->icon])[1];
                        } else {
                            $option['Icon'] = '';
                        }
                        $container->get('mixpanel.helper')->trackEvent((empty($templateWidgetId)?'Added':'Edited').' a Checklist widget', $option);
                        break;
                    case ListingWidget::RANGE :
                        $fontAwesomeIcons = system_getFontAwesomeIcons();
                        if (!empty($fontAwesomeIcons[$widgetEdit->icon])) {
                            $option['Icon'] = explode('; ',$fontAwesomeIcons[$widgetEdit->icon])[1];
                        } else {
                            $option['Icon'] = '';
                        }

                        if (!empty($widgetEdit->hideTitle)) {
                            $option['Hide label'] = 'yes';
                        } else {
                            $option['Hide label'] = 'no';
                        }

                        $option['Range'] = 'from ' .$widgetEdit->minRange. ' to ' .$widgetEdit->maxRange. '';
                        $container->get('mixpanel.helper')->trackEvent((empty($templateWidgetId)?'Added':'Edited').' a Range widget', $option);
                        break;
                    case ListingWidget::SPECIALTIES :
                        $countOptions = 1;
                        foreach ($widgetEdit->dropdownOptions as $fields) {
                            $option['Option '.$countOptions++] = $fields->value;
                        }

                        if ($widgetEdit->required === 'disabled') {
                            $option['Required'] = 'no';
                        } else {
                            $option['Required'] = 'yes';
                        }
                        $container->get('mixpanel.helper')->trackEvent((empty($templateWidgetId)?'Added':'Edited').' a Specialties widget', $option);
                        break;
                    case ListingWidget::CALL_TO_ACTION :
                        $option['Alignment'] = $widgetEdit->alignment;
                        $option['Button label'] = $widgetEdit->buttonLabel;

                        if ($widgetEdit->required === 'disabled') {
                            $option['Required'] = 'no';
                        } else {
                            $option['Required'] = 'yes';
                        }
                        $container->get('mixpanel.helper')->trackEvent((empty($templateWidgetId)?'Added':'Edited').' a Call to action widget', $option);
                        break;
                    case ListingWidget::MORE_DETAILS :
                        if (!empty($widgetEdit->hideTitle)) {
                            $option['Hide label'] = 'yes';
                        } else {
                            $option['Hide label'] = 'no';
                        }

                        if ($widgetEdit->required === 'disabled') {
                            $option['Required'] = 'no';
                        } else {
                            $option['Required'] = 'yes';
                        }

                        $countOptions = 1;
                        foreach ($widgetEdit->groupFields as $fields) {
                            $option['Details '.$countOptions++] = $fields->title;
                        }
                        $container->get('mixpanel.helper')->trackEvent((empty($templateWidgetId)?'Added':'Edited').' a More details widget', $option);
                        break;
                    case ListingWidget::DESCRIPTION :
                        if (!empty($widgetEdit->hideTitle)) {
                            $option['Hide label'] = 'yes';
                        } else {
                            $option['Hide label'] = 'no';
                        }

                        if ($widgetEdit->required === 'disabled') {
                            $option['Required'] = 'no';
                        } else {
                            $option['Required'] = 'yes';
                        }
                        $option['Type'] = $widgetEdit->descriptionType;
                        $container->get('mixpanel.helper')->trackEvent((empty($templateWidgetId)?'Added':'Edited').' a Description widget', $option);
                        break;
                    case ListingWidget::LINKED_LISTINGS :
                        $container->get('mixpanel.helper')->trackEvent((empty($templateWidgetId)?'Added':'Edited').' a Linked listings widget', $option);
                        break;
                    case ListingWidget::RELATED_LISTINGS :
                        $option['Display option'] = ($widgetEdit->filter=="categorylocation"?"category location":$widgetEdit->filter);
                        $option['Order criteria 1'] =  str_replace("_"," ",$widgetEdit->order1);
                        $option['Order criteria 2'] =  str_replace("_"," ",$widgetEdit->order2);
                        $option['Amount'] = $widgetEdit->quantity;
                        $option['Columns'] = $widgetEdit->columns;

                        $container->get('mixpanel.helper')->trackEvent((empty($templateWidgetId)?'Added':'Edited').' a Related listings widget', $option);
                        break;
                }
            }

            $return = [
                'success' => true,
                'isNewWidget' => $isNew ?: false,
                'newWidgetId' => $returnWidget->getId(),
                'message' => $translator->trans('Widget successfully saved.', [], 'widgets', /** @Ignore */
                    $sitemgrLanguage),
            ];
        }
    }

    if (!empty($return)) {
        echo json_encode($return);
    }
}
