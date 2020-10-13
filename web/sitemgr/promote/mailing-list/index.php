<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/promote/mailing-list/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (MAIL_APP_FEATURE != "on") {
		header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/");
		exit;
	}

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

	$url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/promote/mailing-list";
	$url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."/promote/mailing-list";
	$sitemgr = 1;

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

    $url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/includes/code/mailapplist.php");

    // Page Browsing /////////////////////////////////////////
	$pageObj  = new pageBrowsing("MailAppList", $screen, false, "date DESC, title", "title", $letter, false);
	$mailappLists = $pageObj->retrievePage();

	$paging_url = DEFAULT_URL."/".SITEMGR_ALIAS."/promote/mailing-list/index.php";

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
                <section class="section-heading">
                    <div class="section-heading-content">
                        <h1 class="section-heading-title"><?=system_showText(LANG_SITEMGR_MAILAPP_EXPORTER);?></h1>
                    </div>
                </section>

                <section class="section-form">
                    <div id="delete_maillist" style="display:none">
                        <form name="MailList_post" id="MailList_post" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                            <input type="hidden" id="hiddenValue" name="hiddenValue">
                        </form>
                    </div>

                    <div class="col-sm-12">
                        <?php if ($mailappLists) { ?>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <?=system_showText(LANG_SITEMGR_MAILAPP_MANAGE);?>
                            </div>
                            <div class="table-responsive">
                                <?php include(INCLUDES_DIR."/tables/table_mailapplist.php"); ?>
                            </div>
                        </div>
                        <?php } ?>

                        <?php include(INCLUDES_DIR."/forms/form_mailapplist.php"); ?>
                    </div>
                </section>
            </div>
        </div>
    </main>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/mailapplist.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
