<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/article/facebook.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (ARTICLE_FEATURE != "on" || CUSTOM_ARTICLE_FEATURE != "on") { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
    permission_hasSMPerm();

    mixpanel_track("Accessed Manage Articles Facebook Comments section");

    # ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

    $url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".ARTICLE_FEATURE_FOLDER;
    $url_base 	  = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
    $url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));
    $sitemgr 	  = 1;

    $errorPage = "$url_redirect/index.php?message=".$message."&screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : "")."";

    # ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	setting_get("commenting_fb", $commenting_fb);
	if (!$commenting_fb){
		header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".ARTICLE_FEATURE_FOLDER."/index.php?message=".$message."&screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : "")."");
		exit;
	}

	if ($id) {
		$itemObj = new Article($id);
		if ((!$itemObj->getNumber("id")) || ($itemObj->getNumber("id") <= 0)) {
			header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".ARTICLE_FEATURE_FOLDER."/index.php?message=".$message."&screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : "")."");
			exit;
		}
	} else {
		header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".ARTICLE_FEATURE_FOLDER."/index.php?message=".$message."&screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : "")."");
		exit;
	}
	$facebookScript = true;

    $detailLink = ARTICLE_DEFAULT_URL."/".$itemObj->getString("friendly_url").".html";
    setting_get("commenting_fb_number_comments", $commenting_fb_number_comments);

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

                <form role="form" name="facebook_form" id="facebook_form" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                    <section class="section-heading">
                        <div class="section-heading-content">
                            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".ARTICLE_FEATURE_FOLDER."/"?>" class="heading-back-button"><i class="fa fa-angle-left"></i> <?=system_showText(LANG_SITEMGR_NAVBAR_ARTICLE);?></a>
                            <h1 class="section-heading-title"><?=string_ucwords(system_showText(LANG_LABEL_FACEBOOK_COMMENTS))?> <?=$itemObj->getString("title")?></h1>
                        </div>
                    </section>

                    <section class="row tab-options">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <div class="container">
                                    <div class="col-md-12 form-horizontal">
                                        <div class="panel panel-form">
                                            <div class="form-group">
                                                <div class="panel-heading"><?=string_ucwords(system_showText(LANG_LABEL_FACEBOOK_COMMENTS))?></div>
                                                <div class="panel-body">
                                                    <div class="help-block">
                                                        <p><?=system_showText(LANG_LABEL_FACEBOOK_TIP1)?></p>
                                                        <p><?=system_showText(LANG_LABEL_FACEBOOK_TIP2)?> <a href="http://developers.facebook.com/tools/comments" target="_blank">developers.facebook.com/tools/comments</a></p>
                                                        <br>
                                                    </div>
                                                    <div class="form-group text-center">
                                                        <div class="fb-comments" data-href="<?=$detailLink?>" data-numposts="<?=$commenting_fb_number_comments?>" data-width="500"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="row footer-action">
                        <div class="container">
                            <div class="col-xs-12 text-right">
                                <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".ARTICLE_FEATURE_FOLDER."/"?>" class="btn btn-default btn-xs"><?=system_showText(LANG_LABEL_BACK)?></a>
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
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/modules.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
