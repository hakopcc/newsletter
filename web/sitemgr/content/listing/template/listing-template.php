<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /ed-admin/content/listing/template/listing-template.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------

    include '../../../../conf/loadconfig.inc.php';

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSMSession();
    permission_hasSMPerm();

    $url_redirect = DEFAULT_URL.'/'.SITEMGR_ALIAS.'/content/listing/template';

    # ----------------------------------------------------------------------------------------------------
    # CODE
    # ----------------------------------------------------------------------------------------------------
    $container = SymfonyCore::getContainer();

    $translator = $container->get('translator');

    $widgetService = $container->get('widget.service');

    $listingWidgetService = $container->get('listingwidget.service');

    $listingTemplateListingWidgetService = $container->get('listingtemplate.listingwidget.service');

    # ----------------------------------------------------------------------------------------------------
    # DELETE
    # ----------------------------------------------------------------------------------------------------

    //Delete item
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['action'] === 'reset') {
            $listingTemplateId = $_POST['resetListingTemplateId'];
            $saveReturn = $listingTemplateListingWidgetService->resetListingTemplate($_POST['resetListingTemplateId']);
            $success = $saveReturn['success'];
        } elseif ($_POST['action'] === 'saveListingTemplate') {
            try {
                $listingTemplateId = $_POST['id'];
                $listingTemplateListingWidgetService->saveListingTemplateListingWidgets($listingTemplateId, $_POST);
                $alertMessage = $container->get('translator')->trans('Changes successfully saved.', [], 'messages', $sitemgrLanguage);
                $success = true;
            } catch (Exception $exception) {
                $success = false;
                $alertMessage = $translator->trans('Something went wrong!', [], 'widgets', $sitemgrLanguage);
            }
        } else {
            header("Location: $url_redirect/index.php");
        }
    }

    if (!empty($_GET['id'])) {
        $listingTemplate = $container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->find($_GET['id']);
        if(!empty($listingTemplate)){
            $listingTemplateId = $_GET['id'];
        }else{
            header("Location: $url_redirect");
            exit();
        }

    } elseif ($listingTemplateId === null) {
        header("Location: $url_redirect/index.php");
    }
    $activeTemplates = $container->get('listingtemplate.service')->getAllActivesTemplates();
    $listingTemplate = $container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->find($listingTemplateId);

    $response = $container->get('listingtemplate.service')->getAllListingWidgetsByListingTemplate($listingTemplate->getId());

    $listingWidgets = $response['listingWidgets'];
    $listingTabs = $response['listingTabs'];

    $categories = null;

    if($listingTemplate->getCategories() !== null) {
        $categories = $listingTemplate->getCategories()->getValues();
    }

    if ( $categories )
    {
        for ($i = 0, $iMax = count($categories); $i < $iMax; $i++ )
        {
            $arr_category[$i]['name']  = $categories[$i]->getTitle();
            $arr_category[$i]['value'] = $categories[$i]->getId();
            $arr_return_categories[]   = $categories[$i]->getId();
        }
        if ( $arr_return_categories )
        {
            $return_categories = implode( ',', $arr_return_categories );
        }
    }

    $isListingTemplate = true;
    $module = 'listing';
    $countHeader = 0;
    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    include SM_EDIRECTORY_ROOT . '/layout/header.php';
?>
    <main class="main-dashboard">
        <nav class="main-sidebar">
            <?php include(SM_EDIRECTORY_ROOT."/layout/sidebar-dashboard.php"); ?>
            <div class="sidebar-submenu">
                <?php include(SM_EDIRECTORY_ROOT . "/layout/sidebar.php"); ?>
            </div>
        </nav>
        <div class="main-wrapper">
            <?php include(SM_EDIRECTORY_ROOT."/layout/navbar.php"); ?>
            <div class="main-content wysiwyg" content-full="true">
                <div id="loading_ajax" class="alert alert-loading alert-loading-fullscreen" style="display: none;">
                    <img src="<?= DEFAULT_URL; ?>/<?= SITEMGR_ALIAS ?>/assets/img/loading-128.gif" class="alert-img-center">
                </div>

                <section class="section-heading">
                    <div class="section-heading-content">
                        <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/template/"?>" class="heading-back-button"><i class="fa fa-angle-left"></i> <?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_PLURAL)?></a>
                        <?php if ($_GET['id']) { ?>
                            <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_EDIT));?> <?=$listingTemplate->getTitle()?></h1>
                        <?php } else { ?>
                            <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_ADD))." ".system_showText(LANG_SITEMGR_LISTINGTEMPLATE) ?></h1>
                        <?php } ?>
                    </div>
                    <div class="section-heading-actions">
                        <div class="toggle-listing-template">
                            <?=LANG_SITEMGR_ENABLE_LISTINGTEMPLATE?>
                            <div class="switch-button <?=$listingTemplate->getStatus() === 'enabled' ? 'is-enable' : 'is-disable'?>" onclick="verifyListingTemplate(<?=count($activeTemplates)?>,<?= $listingTemplate->getStatus() === 'enabled' ? 1 : 0?>,this, 'listingTemplateState');">
                                <span class="toggle-item"></span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary action-save" id="listing-save" data-loading-text="<?=LANG_LABEL_FORM_WAIT?>"><?=LANG_SITEMGR_SAVE_CHANGES?></button>
                    </div>
                </section>

                <form role="form" name="listing" id="form_widgets" action="<?= system_getFormAction($_SERVER['PHP_SELF']) ?>?id=<?= $listingTemplateId ?>" method="post">
                    <input id="openWidgetId" type="hidden" value="">
                    <input type="hidden" name="id" id="listingTemplateId" value="<?= $listingTemplateId ?>">
                    <input type="hidden" name="selectedDomainId" id="selectedDomainId" value="<?= SELECTED_DOMAIN_ID ?>">
                    <input type="hidden" name="action" value="saveListingTemplate">
                    <input type="hidden" name="serializedPost" id="serializedPost">
                    <input type="hidden" name="serializedTabs" id="serializedTabs">
                    <input type="hidden" name="serializedHeader" id="serializedHeader">
                    <input type="hidden" name="listingTemplateState" id="listingTemplateState" value="<?=$listingTemplate->getStatus() === 'enabled' ? 'enabled' : 'disabled'?>">
                    <input type="submit" style="display: none">

                    <div class="listing-template-config">
                        <div class="form-group">
                            <label for="listing-title"><?=LANG_SITEMGR_LISTING_TEMPLATE_TITLE?></label>
                            <input type="text" name="title" id="listing-title" class="form-control" value="<?=$listingTemplate->getTitle()?>">
                        </div>
                        <div class="form-group">
                            <?php

                            include INCLUDES_DIR . '/forms/form-category.php';

                            ?>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading" role="tab">
                                <div class="heading-title">
                                    <span><?=LANG_SITEMGR_PRICING_OPTIONS?></span>
                                    <button class="btn toggle-pricing-options" data-toggle="collapse" data-parent="#accordion" href="#pricing-options" aria-expanded="true" aria-controls="pricing-options"><i class="fa fa-angle-down"></i></button>
                                </div>
                            </div>
                            <div id="pricing-options" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                                <div class="heading-description">
                                    <?php $manageLevelsPricingUrl = DEFAULT_URL. '/' .SITEMGR_ALIAS. '/configuration/payment/'; ?>
                                    <?=str_replace("[a]", "<a href=\"".$manageLevelsPricingUrl."\" target=\"_blank\">", str_replace("[/a]", "</a>", system_showText(LANG_SITEMGR_ADITIONAL_PRICE)));?>
                                </div>
                                <div class="panel-body">
                                    <div class="toggle-required-widget">
                                        <label><?=LANG_SITEMGR_DISABLE_PRICING_TEMPLATE?> <span class="icon-help8" data-toggle="tooltip" data-placement="right" title="<?=LANG_SITEMGR_DISABLE_PRICING_TEMPLATE_TOOLTIP?>"></span></label>
                                        <div class="switch-button <?=$listingTemplate->getTemplateFree() === 'enabled' ? 'is-enable' : 'is-disable'?>" onclick="toggleItem(this, 'pricing-listing-input', '.pricing-list-input');">
                                            <span class="toggle-item"></span>
                                        </div>
                                        <input type="hidden" name="statusPricing" id="pricing-listing-input" value="<?=$listingTemplate->getTemplateFree() === 'enabled' ? 'enabled' : 'disabled'?>">
                                    </div>
                                    <div class="pricing-list-input">
                                        <label for="priceAdditional"><?=LANG_SITEMGR_ADDITIONAL_PRICING_TEMPLATE?></label>
                                        <input class="form-control" type="number" step="0.01" min="0" id="priceAdditional" name="priceAdditional" <?=$listingTemplate->getTemplateFree() === 'enabled' ? 'disabled' : ''?> value="<?=$listingTemplate->getPrice() ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        /* ModStores Hooks */
                        HookFire("legacy_contentlistingtemplate_listingtemplate_after_render_category", [ "listing_template" => &$listingTemplate ]);
                        ?>
                    </div>

                    <div class="container summary-view-templates">
                        <div class="summary-view-heading <?=!empty($_GET['new']) ? 'is-open' : ''?>">
                            <span><?=LANG_SITEMGR_CHOOSE_SUMMARY_VIEW?></span>
                            <i class="fa fa-angle-down"></i>
                        </div>
                        <div class="summary-view-content" <?=!empty($_GET['new']) ? 'style="display: block;"' : ''?>>
                            <div class="summary-list">
                                <? for ($i = 1; $i <= 11; $i++) {?>
                                  <? if (file_exists(EDIRECTORY_ROOT.'/'.SITEMGR_ALIAS.'/assets/img/listing-summary/'.$i.'.png')) {?>
                                        <label for="summary-item-<?=$i?>" class="summary-item">
                                            <input type="radio" name="summaryItem" id="summary-item-<?=$i?>" value="<?=$i?>" <?=$listingTemplate->getSummaryTemplate() === $i ? 'checked="checked"' : ''?>>
                                            <img src='../../../assets/img/listing-summary/<?=$i?>.png'>
                                        </label>
                                    <? } ?>
                                <? }?>
                            </div>
                        </div>
                    </div>

                    <div class="container listing-template-editor">
                        <div class="listing-template-editor-heading">
                            <span><?=LANG_SITEMGR_EDIT_DETAIL_VIEW?></span>
                            <button type="button" class="btn btn-default btn-md resetListingTemplateButton"><?=LANG_SITEMGR_RESET_TO_DEFAULT?></button>
                        </div>
                        <div class="listing-template-editor-widgets">
                            <div class="listing-widget-container header-widgets" >
                                <div class="listing-widget-list">
                                <?php if(!empty($listingWidgets['header'])) {?>
                                    <?php foreach($listingWidgets['header'] as $headerListingWidget) {
                                        $imgPath = DEFAULT_URL . '/sitemgr/assets/img/listing-widget-placeholder/header/' .system_generateFriendlyURL($headerListingWidget->getListingWidget()->getTitle()). '.jpg';
                                        if (!file_exists(EDIRECTORY_ROOT. '/' .SITEMGR_ALIAS. '/assets/img/listing-widget-placeholder/header/' .system_generateFriendlyURL($headerListingWidget->getListingWidget()->getTitle()). '.jpg')){
                                            $imgPath = DEFAULT_URL . '/sitemgr/assets/img/listing-widget-placeholder/main/custom-content.jpg';
                                        }
                                    ?>

                                        <div class="listing-widget-item"  id="header-<?=$headerListingWidget->getId()?>" data-widgetid="<?=$headerListingWidget->getId()?>">
                                            <a href="javascript:void(0);" data-url="<?= DEFAULT_URL ?>/<?= SITEMGR_ALIAS ?>/content/listing/template/add-header-widget.php?listingTemplateId=<?=$listingTemplateId?>" class="widget-add-before add-new-widget btn-new-widget">
                                                <i class="fa fa-plus-circle"></i>
                                            </a>
                                            <input type="hidden" name="listingWidgetId" value="<?=$headerListingWidget->getListingWidget()->getId()?>">
                                            <input type="hidden" id="listingTemplateListingWidgetIdInput" name="listingTemplateListingWidgetIdInput" value="<?=$headerListingWidget->getId()?>">
                                            <div class="widget-name"><?=/** @Ignore */
                                                $translator->trans($headerListingWidget->getListingWidget()->getTitle(), [], 'widgets', $sitemgrLanguage)?></div>
                                            <?php
                                            /* ModStores Hooks */
                                            HookFire('legacy-sitemgr-content-listing-template_before_render-header-widget-placeholder', [
                                                'listing_widget' => $headerListingWidget,
                                                'image_path' => &$imgPath
                                            ], true);
                                            ?>
                                            <div class="widget-placeholder"><img src="<?=$imgPath?>"></div>
                                            <div class="widget-actions">
                                                <div class="widget-action-item widget-move"><i class="fa fa-bars"></i></div>
                                                <?php if(!empty($headerListingWidget->getListingWidget()->getModal())) { ?>
                                                    <a href="<?=DEFAULT_URL?>/includes/code/widgetActionAjax.php" class="widget-action-item widget-edit editListingWidgetButton"
                                                       data-divid="header-<?=$headerListingWidget->getId()?>" data-widget="<?=$headerListingWidget->getListingWidget()->getId()?>" data-toggle="modal" data-target="#" data-modal="<?=$headerListingWidget->getListingWidget()->getModal()?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                <?php } ?>
                                                <? if ($headerListingWidget->getListingWidget()->getTitle() =="Header"){?>
                                                    <? if ($countHeader > 0) {?>
                                                        <a href="javascript:void(0)" class="widget-action-item widget-remove removeListingWidgetButton"
                                                           data-divid="header-<?=$headerListingWidget->getId()?>" data-toggle="modal" data-target="#remove-widget-modal">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    <? }?>
                                                    <?php
                                                        $countHeader++;
                                                    ?>
                                                <? }else {?>
                                                    <a href="javascript:void(0)" class="widget-action-item widget-remove removeListingWidgetButton"
                                                       data-divid="header-<?=$headerListingWidget->getId()?>" data-toggle="modal" data-target="#remove-widget-modal">
                                                        <i class="fa fa-trash"></i>
                                                    </a>
                                                <? }?>
                                            </div>
                                        </div>

                                    <?php } ?>
                                <?php } ?>
                                </div>
                                <a href="javascript:void(0);" id="addHeader" class="listing-widget-placeholder btn-new-widget"  data-url="<?= DEFAULT_URL ?>/<?= SITEMGR_ALIAS ?>/content/listing/template/add-header-widget.php?listingTemplateId=<?=$listingTemplateId?>">
                                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                    <?=LANG_SITEMGR_ADD_WIDGET?>
                                </a>
                            </div>

                            <ul class="listing-widget-tabs">
                                <div class="widget-tabs-content" id="tabs-widgets">
                                    <?php
                                    if (!empty($listingTabs)) {
                                        $firstTab = true;

                                        foreach($listingTabs as $listingTab) { ?>
                                            <li class="listing-tabs-item <?=$firstTab ? 'is-selected' : ''?>" data-tab="tab-<?=$listingTab->getId()?>">
                                                <input type="hidden" name="tabId" value="<?=$listingTab->getId()?>">
                                                <input type="hidden" name="newTab" value="false">
                                                <input type="text" name="tabTitle" class="tabs-title" data-value="<?=$listingTab->getTitle()?>" value="<?=$translator->trans($listingTab->getTitle())?>">
                                                <?php if(count($listingTabs) !== 1) { ?>
                                                    <a href="javascript:void(0)" data-dismiss="modal" class="tabs-remove removeTabButton"
                                                        data-toggle="modal"
                                                        data-target="#remove-tab-modal"
                                                        data-id="<?=$listingTab->getId() ?>">
                                                        <i class="fa fa-times"></i>
                                                    </a>
                                                <?php } ?>
                                            </li>
                                            <?php $firstTab = false;
                                        }
                                    } ?>
                                </div>
                                <li class="listing-add-tabs new-tab">
                                    <i class="fa fa-plus-circle" aria-hidden="true" id="new-tab" data-label="<?=LANG_SITEMGR_NEW_TAB?>"></i>
                                </li>
                            </ul>

                            <div id="tab-widgets">
                                <?php if(!empty($listingWidgets['tabs'])) {
                                    $activeTab = true;
                                    foreach($listingWidgets['tabs'] as $tab => $listingTabWidgets) { ?>
                                        <div class="listing-widget-body <?=$activeTab ? 'is-active' : ''?>" id="tab-<?=$listingTabs[$tab]->getId()?>">
                                            <div class="listing-widget-container main-widgets <?=empty($listingTabWidgets['sidebar']) ? 'resized-sidebar':'';?>">
                                                <div class="listing-widget-list">
                                                    <?php if(!empty($listingTabWidgets['main'])) { ?>
                                                        <?php foreach($listingTabWidgets['main'] as $mainListingWidget) {
                                                            $imgPath = DEFAULT_URL . '/sitemgr/assets/img/listing-widget-placeholder/main/' .system_generateFriendlyURL($mainListingWidget->getListingWidget()->getTitle()). '.jpg';
                                                            if (!file_exists(EDIRECTORY_ROOT. '/' .SITEMGR_ALIAS. '/assets/img/listing-widget-placeholder/main/' .system_generateFriendlyURL($mainListingWidget->getListingWidget()->getTitle()). '.jpg')){
                                                                $imgPath = DEFAULT_URL . '/sitemgr/assets/img/listing-widget-placeholder/main/custom-content.jpg';
                                                            }

                                                            ?>
                                                            <div class="listing-widget-item" id="main-<?=$mainListingWidget->getId()?>" data-widgetid="<?=$mainListingWidget->getId()?>">
                                                                <a href="javascript:void(0);" data-url="<?= DEFAULT_URL ?>/<?= SITEMGR_ALIAS ?>/content/listing/template/add-main-widget.php?listingTemplateId=<?=$listingTemplateId?>&listingTemplateTabId=<?=$listingTabs[$tab]->getId()?>" class="widget-add-before btn-new-widget">
                                                                    <i class="fa fa-plus-circle"></i>
                                                                </a>

                                                                <input type="hidden" name="listingWidgetId" value="<?=$mainListingWidget->getListingWidget()->getId()?>">
                                                                <input type="hidden" id="listingTemplateListingWidgetIdInput" name="listingTemplateListingWidgetIdInput" value="<?=$mainListingWidget->getId()?>">
                                                                <input type="hidden" name="listingTemplateTabId" value="<?=$listingTabs[$tab]->getId()?>">
                                                                <div class="widget-name"><?=/** @Ignore */
                                                                    $translator->trans($mainListingWidget->getListingWidget()->getTitle(), [], 'widgets', $sitemgrLanguage)?></div>
                                                                <?php
                                                                /* ModStores Hooks */
                                                                HookFire('legacy-sitemgr-content-listing-template_before_render-main-widget-placeholder', [
                                                                    'listing_widget' => $mainListingWidget,
                                                                    'image_path' => &$imgPath
                                                                ], true);
                                                                ?>
                                                                <div class="widget-placeholder"><img src="<?=$imgPath?>"></div>
                                                                <div class="widget-actions">
                                                                    <div class="widget-action-item widget-move"><i class="fa fa-bars" aria-hidden="true"></i></div>
                                                                    <?php if(!empty($mainListingWidget->getListingWidget()->getModal())) { ?>
                                                                        <a href="<?=DEFAULT_URL?>/includes/code/widgetActionAjax.php" class="widget-action-item widget-edit editListingWidgetButton" data-tab="<?=$listingTabs[$tab]->getId()?>"
                                                                        data-divid="main-<?=$mainListingWidget->getId()?>" data-widget="<?=$mainListingWidget->getListingWidget()->getId()?>" data-toggle="modal" data-target="#" data-modal="<?=$mainListingWidget->getListingWidget()->getModal()?>">
                                                                            <i class="fa fa-pencil"></i>
                                                                        </a>
                                                                    <?php } ?>
                                                                    <a href="#" class="widget-action-item widget-remove removeListingWidgetButton"
                                                                    data-divid="main-<?=$mainListingWidget->getId()?>" data-toggle="modal" data-target="#remove-widget-modal">
                                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                                <a href="javascript:void(0);" data-url="<?= DEFAULT_URL ?>/<?= SITEMGR_ALIAS ?>/content/listing/template/add-main-widget.php?listingTemplateId=<?=$listingTemplateId?>&listingTemplateTabId=<?=$listingTabs[$tab]->getId()?>" class="listing-widget-placeholder add-new-widget btn-new-widget">
                                                    <i class="fa fa-plus-circle"></i>
                                                    <?=LANG_SITEMGR_ADD_WIDGET?>
                                                </a>
                                            </div>
                                            <div class="listing-widget-container sidebar-widgets <?=empty($listingTabWidgets['sidebar']) ? 'resized-sidebar':'';?>">
                                                <div class="listing-widget-list">
                                                    <?php if(!empty($listingTabWidgets['sidebar'])) { ?>
                                                        <?php foreach($listingTabWidgets['sidebar'] as $sidebarListingWidget) {
                                                            $imgPath = DEFAULT_URL . '/sitemgr/assets/img/listing-widget-placeholder/sidebar/' .system_generateFriendlyURL($sidebarListingWidget->getListingWidget()->getTitle()). '.jpg';
                                                            if (!file_exists(EDIRECTORY_ROOT. '/' .SITEMGR_ALIAS. '/assets/img/listing-widget-placeholder/sidebar/' .system_generateFriendlyURL($sidebarListingWidget->getListingWidget()->getTitle()). '.jpg')){
                                                                $imgPath = DEFAULT_URL . '/sitemgr/assets/img/listing-widget-placeholder/sidebar/custom-content.jpg';
                                                            }

                                                            ?>
                                                            <div class="listing-widget-item" id="sidebar-<?=$sidebarListingWidget->getId()?>" data-widgetid="<?=$sidebarListingWidget->getId()?>">
                                                                <a href="javascript:void(0);" data-url="<?= DEFAULT_URL ?>/<?= SITEMGR_ALIAS ?>/content/listing/template/add-sidebar-widget.php?listingTemplateId=<?=$listingTemplateId?>&listingTemplateTabId=<?=$listingTabs[$tab]->getId()?>" class="widget-add-before add-new-widget btn-new-widget">
                                                                    <i class="fa fa-plus-circle"></i>
                                                                </a>
                                                                <input type="hidden" name="listingWidgetId" value="<?=$sidebarListingWidget->getListingWidget()->getId()?>">
                                                                <input type="hidden" id="listingTemplateListingWidgetIdInput" name="listingTemplateListingWidgetIdInput" value="<?=$sidebarListingWidget->getId()?>">
                                                                <input type="hidden" name="listingTemplateTabId" value="<?=$listingTabs[$tab]->getId()?>">
                                                                <div class="widget-name"><?=/** @Ignore */
                                                                    $translator->trans($sidebarListingWidget->getListingWidget()->getTitle(), [], 'widgets', $sitemgrLanguage)?></div>
                                                                <?php
                                                                /* ModStores Hooks */
                                                                HookFire('legacy-sitemgr-content-listing-template_before_render-sidebar-widget-placeholder', [
                                                                    'listing_widget' => $sidebarListingWidget,
                                                                    'image_path' => &$imgPath
                                                                ], true);
                                                                ?>
                                                                <div class="widget-placeholder"><img src="<?=$imgPath?>"></div>
                                                                <div class="widget-actions">
                                                                    <div class="widget-action-item widget-move"><i class="fa fa-bars"></i></div>
                                                                    <?php if(!empty($sidebarListingWidget->getListingWidget()->getModal())) { ?>
                                                                            <a href="<?=DEFAULT_URL?>/includes/code/widgetActionAjax.php" class="widget-action-item widget-edit editListingWidgetButton" data-tab="<?=$listingTabs[$tab]->getId()?>"
                                                                               data-divid="sidebar-<?=$sidebarListingWidget->getId()?>" data-widget="<?=$sidebarListingWidget->getListingWidget()->getId()?>" data-toggle="modal" data-target="#widget-checklist" data-modal="<?=$sidebarListingWidget->getListingWidget()->getModal()?>">
                                                                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                                                            </a>
                                                                    <?php } ?>
                                                                    <a href="javascript:void(0)" class="widget-action-item widget-remove removeListingWidgetButton"
                                                                       data-divid="sidebar-<?=$sidebarListingWidget->getId()?>" data-tabRef="tab-<?=$listingTabs[$tab]->getId()?>" data-toggle="modal" data-target="#remove-widget-modal">
                                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                                <a href="javascript:void(0);" data-url="<?= DEFAULT_URL ?>/<?= SITEMGR_ALIAS ?>/content/listing/template/add-sidebar-widget.php?listingTemplateId=<?=$listingTemplateId?>&listingTemplateTabId=<?=$listingTabs[$tab]->getId()?>" class="listing-widget-placeholder btn-new-widget">
                                                    <i class="fa fa-plus-circle"></i>
                                                    <?=LANG_SITEMGR_ADD_WIDGET?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php
                                        $activeTab = false;
                                    } ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>


<?php
    include INCLUDES_DIR.  '/modals/modal-add-category.php';
    include INCLUDES_DIR . '/modals/modal-reset-listingtemplate.php';
    include INCLUDES_DIR . '/modals/widget/modal-widget-add.php';
    include INCLUDES_DIR . '/modals/widget/modal-widget-remove.php';
    include INCLUDES_DIR . '/modals/widget/modal-tab-remove.php';

    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/listing-template.php';

    include SM_EDIRECTORY_ROOT . '/layout/footer.php';
