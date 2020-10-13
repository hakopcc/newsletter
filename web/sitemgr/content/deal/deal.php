<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/deal/deal.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # VALIDATION
    # ----------------------------------------------------------------------------------------------------
    if ( PROMOTION_FEATURE != "on" || CUSTOM_PROMOTION_FEATURE != "on" || CUSTOM_HAS_PROMOTION != "on"){
        exit;
    }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
    permission_hasSMPerm();

    $url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".PROMOTION_FEATURE_FOLDER.'/deal.php';
    $url_base 	  = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
    $url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));
    $sitemgr 	  = 1;

    # ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

    mixpanel_track(($id ? "Edited an existing deal" : "Added a new deal"));

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include(EDIRECTORY_ROOT."/includes/code/promotion.php");

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/header.php");
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
            <div class="main-content" content-full="true">
                <?php if (system_blockListingCreation($id)) { ?>
                    <?php include INCLUDES_DIR.'/views/upgrade_plan_banner.php'; ?>
                <?php } else { ?>
                    <?php
                        require(SM_EDIRECTORY_ROOT."/registration.php");
                        require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
                    ?>
                    <form role="form" name="promotion" id="promotion" class="form-content-blocked" action="<?= system_getFormAction($_SERVER["PHP_SELF"]) ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="sitemgr" id="sitemgr" value="<?= $sitemgr ?>">
                        <input type="hidden" name="id" id="id" value="<?= $id ?>">
                        <input type="hidden" name="listing_id" value="<?= $listing_id ?>">
                        <?= system_getFormInputSearchParams((($_POST) ? ($_POST) : ($_GET))); ?>
                        <input type="hidden" name="letter" value="<?= $letter ?>">
                        <input type="hidden" name="screen" value="<?= $screen ?>">

                        <section class="section-heading">
                            <div class="section-heading-content">
                                <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".PROMOTION_FEATURE_FOLDER."/index.php?".($url_search_params ? "&$url_search_params" : '')?>" class="heading-back-button"><i class="fa fa-angle-left"></i> <?=system_showText(LANG_SITEMGR_NAVBAR_PROMOTION);?></a>
                                <?php if ($id) { ?>
                                    <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_EDIT));?> <?=$promotion->getString("name");?></h1>
                                <?php } else { ?>
                                    <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_ADD))." ".system_showText(LANG_SITEMGR_PROMOTION_SING) ?></h1>
                                <?php } ?>
                            </div>
                            <div class="section-heading-actions">
                                <a href="javascript:void(0);" data-tour class="text-info tutorial-text hidden-xs hidden-sm"><?= system_showText(LANG_LABEL_TUTORIAL); ?>
                                    <i class="icon-help8"></i>
                                </a>
                                <button type="button" name="submit_button" value="Submit" onclick="document.promotion.submit();" class="btn btn-primary action-save" data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                            </div>
                        </section>

                        <section class="row tab-options new-structure-form-block">
                            <div class="tab-content">
                                <div class="tab-pane active">
                                    <div class="container">
                                        <?php include(INCLUDES_DIR."/forms/form-promotion.php"); ?>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="row footer-action">
                            <div class="container">
                                <div class="col-xs-12 text-right">
                                    <a href="<?= DEFAULT_URL."/".SITEMGR_ALIAS."/content/".PROMOTION_FEATURE_FOLDER."/" ?>" class="btn btn-default btn-xs"><?= system_showText(LANG_CANCEL) ?></a>
                                    <span class="separator"> <?= system_showText(LANG_OR) ?> </span>
                                    <button type="button" name="submit_button" value="Submit" onclick="document.promotion.submit();" class="btn btn-primary action-save" data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                                </div>
                            </div>
                        </section>
                    </form>

                    <aside class="tutorial-tour">
                        <h1><?= system_showText(LANG_LABEL_TUTORIAL_FIELDS); ?></h1>
                        <div class="nano">
                            <ul class="list-unstyled nano-content">
                                <?php foreach ($arrayTutorial as $key => $title) { ?>
                                    <li><span class="tour-step <?= (!$key ? "active" : "") ?>" data-step="<?= $key ?>"><i class="icon-chevron15"></i> <?= $title["field"] ?></span></li>
                                <?php } ?>
                                <li><span class="tour-step-end"><?= system_showText(LANG_LABEL_TUTORIAL_END) ?></span></li>
                            </ul>
                        </div>
                    </aside>
                    <?php
                        include(INCLUDES_DIR."/modals/modal-crop.php");
                        if (!empty(UNSPLASH_ACCESS_KEY)) {
                            include(INCLUDES_DIR . "/modals/modal-unsplash.php");
                            JavaScriptHandler::registerFile(DEFAULT_URL . '/assets/js/lib/unsplash.js');
                        }
                        $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/modules.php";
                    ?>
                <?php } ?>
            </div>
        </div>
    </main>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
