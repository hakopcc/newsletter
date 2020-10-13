<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/banner/banner.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (BANNER_FEATURE != "on") { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
    permission_hasSMPerm();

    $url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".BANNER_FEATURE_FOLDER;
    $url_base 	  = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
    $url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));
    $sitemgr 	  = 1;

    # ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

    mixpanel_track(($id ? "Edited an existing banner" : "Added a new banner"));

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include(EDIRECTORY_ROOT."/includes/code/banner.php");

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
                    require(SM_EDIRECTORY_ROOT."/registration.php");
                    require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
                ?>

                <form role="form" name="banner" id="banner" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post" enctype="multipart/form-data">
                    <?php if ($id) { ?>
                        <input type="hidden" name="operation" value="update">
                        <input type="hidden" name="id" value="<?=$id?>">
                    <?php } else { ?>
                        <input type="hidden" name="operation" value="add">
                    <?php } ?>
                    <input type="hidden" name="sitemgr" id="sitemgr" value="<?=$sitemgr?>">
                    <input type="hidden" name="id" id="id" value="<?=$id?>">
                    <?=system_getFormInputSearchParams((($_POST)?($_POST):($_GET)));?>
                    <input type="hidden" name="letter" value="<?=$letter?>">
                    <input type="hidden" name="screen" value="<?=$screen?>">
                    <input type="hidden" name="domain_id" id="domain_id" value="<?=SELECTED_DOMAIN_ID;?>">

                    <section class="section-heading">
                        <div class="section-heading-content">
                            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".BANNER_FEATURE_FOLDER."/index.php?".($url_search_params ? "&$url_search_params" : '')?>" class="heading-back-button"><i class="fa fa-angle-left"></i> <?=system_showText(LANG_SITEMGR_NAVBAR_BANNER);?></a>
                            <?php if ($id) { ?>
                                <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_EDIT));?> <?=$thisBannerObject->getString("caption")?></h1>
                            <?php } else { ?>
                                <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_ADD))." ".system_showText(LANG_SITEMGR_BANNER_SING) ?></h1>
                            <?php } ?>
                        </div>
                        <div class="section-heading-actions">
                            <a href="javascript:void(0);" data-tour class="text-info tutorial-text hidden-xs hidden-sm"><?= system_showText(LANG_LABEL_TUTORIAL); ?>
                                <i class="icon-help8"></i>
                            </a>
                            <button type="submit" name="submit_button" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
                        </div>
                    </section>

                    <section class="row tab-options">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <div class="container">
                                    <? include(INCLUDES_DIR."/forms/form-banner.php"); ?>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="row footer-action">
                        <div class="container">
                            <div class="col-xs-12 text-right">
                                <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".BANNER_FEATURE_FOLDER."/"?>" class="btn btn-default btn-xs"><?=system_showText(LANG_CANCEL)?></a>
                                <span class="separator"> <?=system_showText(LANG_OR)?> </span>
                                <button type="submit" name="submit_button" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
                            </div>
                        </div>
                    </section>
                </form>

                <aside class="tutorial-tour">
                    <h1><?=system_showText(LANG_LABEL_TUTORIAL_FIELDS);?></h1>
                    <div class="nano">
                        <ul class="list-unstyled nano-content">
                            <?php foreach ($arrayTutorial as $key => $title) { ?>
                                <li><span class="tour-step <?=(!$key ? "active" : "")?>" data-step="<?=$key?>" ><i class="icon-chevron15"></i> <?=$title["field"]?></span></li>
                            <?php } ?>
                            <li><span class="tour-step-end"><?=system_showText(LANG_LABEL_TUTORIAL_END)?></span></li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </main>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	$customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/modules.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
