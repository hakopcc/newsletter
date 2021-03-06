<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/promote/promotions/package.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

	$url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/promote/promotions";
	$url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
	$sitemgr = 1;
	$item_form = 1;

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

    mixpanel_track(($id ? "Edited an existing package" : "Added a new package"));

	$url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include(EDIRECTORY_ROOT."/includes/code/package.php");

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
                <?php
                    require SM_EDIRECTORY_ROOT.'/registration.php';
                    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
                ?>

                <form name="package" role="form" id="package" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="sitemgr" id="sitemgr" value="<?=$sitemgr?>">
                    <input type="hidden" name="id" value="<?=$id?>">

                    <section class="section-heading">
                        <div class="section-heading-content">
                            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/promote/promotions/"?>" class="heading-back-button"><i class="fa fa-angle-left"></i> <?=system_showText(LANG_SITEMGR_PROMO_PACK);?></a>
                            <?php if ($id) { ?>
                                <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_EDIT));?> <?=system_showText(LANG_SITEMGR_PACKAGE_SING)?> <?=$title?></h1>
                            <?php } else { ?>
                                <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_ADD))." ".system_showText(LANG_SITEMGR_PACKAGE_SING) ?></h1>
                            <?php } ?>
                        </div>
                        <div class="section-heading-actions">
                            <button type="submit" name="submit_button" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
                        </div>
                    </section>

                    <section class="section-form row">
                        <div class="container">
                            <?php include(INCLUDES_DIR."/forms/form-package.php"); ?>
                        </div>
                    </section>

                    <section class="row footer-action">
                        <div class="container">
                            <div class="col-xs-12 text-right">
                                <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/promote/promotions/" ?>" class="btn btn-default btn-xs"><?=system_showText(LANG_CANCEL)?></a>
                                <span class="separator"> <?=system_showText(LANG_OR)?> </span>
                                <button type="submit" name="submit_button" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
                            </div>
                        </div>
                    </section>
                </form>
            </div>
        </div>
    </main>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/package.php";
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
