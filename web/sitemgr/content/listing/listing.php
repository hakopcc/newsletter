<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/listing/listing.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include '../../../conf/loadconfig.inc.php';

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
    permission_hasSMPerm();

    $url_redirect = DEFAULT_URL.'/'.SITEMGR_ALIAS.'/content/'.LISTING_FEATURE_FOLDER.'/listing.php';
    $url_base 	  = DEFAULT_URL.'/'.SITEMGR_ALIAS.'';
    $url_search_params = system_getURLSearchParams(($_POST ? $_POST : $_GET));
    $sitemgr 	  = 1;

    # ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

    $container = SymfonyCore::getContainer();
    $listingLevelService = $container->get('listinglevel.service');
    $listingLevelFieldService = $container->get('listinglevelfield.service');
    $listingTemplateService = $container->get('listingtemplate.service');

    $listingLevelArray = $listingLevelService->getAllListingLevels();
    $listingTemplateArray = $listingTemplateService->getAllListingTemplates();

    mixpanel_track(($id ? 'Edited an existing listing' : 'Added a new listing'));

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include EDIRECTORY_ROOT.'/includes/code/listing.php';

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include SM_EDIRECTORY_ROOT.'/layout/header.php';
?>

    <main class="main-dashboard">
        <div id="loading_ajax" class="alert alert-loading alert-loading-fullscreen" style="display: block;">
            <img src="<?= DEFAULT_URL; ?>/<?= SITEMGR_ALIAS ?>/assets/img/loading-128.gif" class="alert-img-center">
        </div>

        <nav class="main-sidebar">
            <?php include(SM_EDIRECTORY_ROOT. '/layout/sidebar-dashboard.php'); ?>
            <div class="sidebar-submenu">
                <?php include(SM_EDIRECTORY_ROOT . '/layout/sidebar.php'); ?>
            </div>
        </nav>
        <div class="main-wrapper">
            <?php include(SM_EDIRECTORY_ROOT. '/layout/navbar.php'); ?>
            <div class="main-content" content-full="true">
                <?php if (system_blockListingCreation($id)) { ?>
                    <?php include INCLUDES_DIR.'/views/upgrade_plan_banner.php'; ?>
                <?php } else { ?>
                    <?php
                        require SM_EDIRECTORY_ROOT.'/registration.php';
                        require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
                    ?>
                    <form role="form" name="listing" class="form-content-blocked" id="listing" action="<?= system_getFormAction($_SERVER['PHP_SELF']) ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="sitemgr" id="sitemgr" value="<?= $sitemgr ?>">
                        <input type="hidden" name="id" id="id" value="<?= $id ?>">
                        <?= system_getFormInputSearchParams(($_POST ? $_POST : $_GET)); ?>
                        <input type="hidden" name="letter" value="<?= $letter ?>">
                        <input type="hidden" name="screen" value="<?= $screen ?>">

                        <section class="section-heading">
                            <div class="section-heading-content">
                                <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .LISTING_FEATURE_FOLDER. '/index.php?' .($url_search_params ? "&$url_search_params" : '')?>" class="heading-back-button"><i class="fa fa-angle-left"></i> <?=system_showText(LANG_SITEMGR_NAVBAR_LISTING);?></a>
                                <?php if ($id) { ?>
                                    <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_EDIT));?> <?= $listing->getString('title') ?></h1>
                                <?php } else { ?>
                                    <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_ADD)). ' ' .system_showText(LANG_SITEMGR_LISTING_SING) ?></h1>
                                <?php } ?>
                            </div>
                            <div class="section-heading-actions">
                                <a href="javascript:void(0);" data-tour class="text-info tutorial-text hidden-xs hidden-sm"><?= system_showText(LANG_LABEL_TUTORIAL); ?>
                                    <i class="icon-help8"></i>
                                </a>
                                <button type="button" onclick="JS_submit();" name="submit_button" value="Submit" class="btn btn-primary action-save" data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                            </div>
                        </section>

                        <div class="section-form-type">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-4 col-md-offset-2">
                                        <label for="listingLevel"><?=LANG_LISTING_LEVEL?></label>
                                        <select name="level" id="listingLevel" class="form-control" data-id="<?=$id?>">
                                            <option value="" selected disabled><?=LANG_LISTING_LEVEL_OPTION?></option>
                                            <?php foreach ($listingLevelArray as $listingLevelEntity) { ?>
                                                <option value="<?=$listingLevelEntity->getValue()?>" <?=($id && $level == $listingLevelEntity->getValue() || $level == $listingLevelEntity->getValue()) ? 'selected' : ''?>><?=ucfirst($listingLevelEntity->getName())?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    ​
                                    <?php
                                    if(empty($listingtemplate_id) && count($listingTemplateArray)===1){
                                        $listingtemplate_id = $listingTemplateArray[0]->getId();
                                    }
                                    if (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on' && count($listingTemplateArray) > 1) { ?>
                                        <div class="col-md-4">
                                            <label for="listingTemplate"><?=LANG_LISTING_TEMPLATE?></label>
                                            <select name="listingtemplate_id" id="listingTemplate" class="form-control" data-id="<?=$id?>">
                                                <option value="" selected disabled><?=LANG_LISTING_TEMPLATE_OPTION?></option>
                                                <?php foreach ($listingTemplateArray as $listingTemplateEntity) { ?>
                                                    <option <?=($listingTemplateEntity->getStatus() === 'disabled' && $listingtemplate_id != $listingTemplateEntity->getId() ? 'disabled' : '')?> value="<?=$listingTemplateEntity->getId()?>" <?=$listingtemplate_id == $listingTemplateEntity->getId() ? 'selected' : ''?>><?=$listingTemplateEntity->getTitle()?> <?=($listingTemplateEntity->getStatus() === 'disabled' ? '('.system_showText(LANG_SITEMGR_DISABLED).')' : '')?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    <?php } else { ?>
                                        <input type="hidden" id="listingTemplate" name="listingtemplate_id" value="<?= $listingTemplateArray[0]->getId() ?>">
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        ​
                        <div class="blocked-section" id="blockedSection" style="<?=($listingtemplate_id && $level) ? 'display: none;' : 'display: flex;'?>">
                            <div class="block-content">
                                <img src="<?='/'.SITEMGR_ALIAS.'/assets/img/locker-panel.svg'?>" alt="Locker">
                                <?php if (count($listingTemplateArray) > 1) {
                                    echo LANG_LISTING_LOCKER_MSG;
                                } else {
                                    echo LANG_LISTING_LOCKER_MSG_2;
                                } ?>
                            </div>
                        </div>

                        <section class="row tab-options new-structure-form-block">
                            <div class="container">
                                <?php include SM_EDIRECTORY_ROOT.'/layout/nav-tabs-content-listing.php'; ?>
                            </div>
                            <div class="tab-content">
                                <div class="tab-pane active">
                                    <div class="container">
                                        <?php include INCLUDES_DIR.'/forms/form-listing.php'; ?>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="row footer-action">
                            <div class="container">
                                <div class="col-xs-12 text-right">
                                    <a href="<?= DEFAULT_URL.'/'.SITEMGR_ALIAS.'/content/'.LISTING_FEATURE_FOLDER.'/' ?>"
                                    class="btn btn-default btn-xs"><?= system_showText(LANG_CANCEL) ?></a>
                                    <span class="separator"> <?= system_showText(LANG_OR) ?> </span>
                                    <button type="button" onclick="JS_submit();" name="submit_button" value="Submit" class="btn btn-primary action-save" data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                                </div>
                            </div>
                        </section>
                    </form>

                    <aside class="tutorial-tour">
                        <h1><?=system_showText(LANG_LABEL_TUTORIAL_FIELDS)?></h1>
                        <div class="nano">
                            <ul class="list-unstyled nano-content">
                                <?php foreach ($arrayTutorial as $key => $title) { ?>
                                    <li><span class="tour-step <?= (!$key ? 'active' : '') ?>" data-step="<?= $key ?>"><i class="icon-chevron15"></i> <?= $title['field'] ?></span></li>
                                <?php } ?>
                                <li><span class="tour-step-end"><?=system_showText(LANG_LABEL_TUTORIAL_END)?></span></li>
                            </ul>
                        </div>
                    </aside>
                    <?php
                        /* ModStores Hooks */
                        HookFire("legacy-sitemgr-content-listing_before_modal-includes", []);

                        include INCLUDES_DIR.'/modals/modal-add-category.php';
                        include INCLUDES_DIR.'/modals/modal-crop.php';
                        if (!empty(UNSPLASH_ACCESS_KEY)) {
                            include INCLUDES_DIR .'/modals/modal-unsplash.php';
                            JavaScriptHandler::registerFile(DEFAULT_URL . '/assets/js/lib/unsplash.js');
                        }
                        $customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/modules.php';
                    ?>
                <?php } ?>
            </div>
        </div>
    </main>

<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    include SM_EDIRECTORY_ROOT.'/layout/footer.php';
?>
