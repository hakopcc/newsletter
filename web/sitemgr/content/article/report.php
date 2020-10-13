<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/article/report.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (ARTICLE_FEATURE != "on") { exit; }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track("Accessed Manage Articles Reports section");

	$url_redirect = DEFAULT_URL."/".SITEMGR_ALIAS."/content/".ARTICLE_FEATURE_FOLDER;
	$url_base = DEFAULT_URL."/".SITEMGR_ALIAS."";
	$sitemgr = 1;

	extract($_GET);
	extract($_POST);

    # ----------------------------------------------------------------------------------------------------
    # OBJECTS
    # ----------------------------------------------------------------------------------------------------
	if ($id) {
        $article = new Article($id);
	} else {
		header($url_redirect);
		exit;
	}

    $articleLevel = new ArticleLevel();
    $levelName = string_ucwords($articleLevel->getName($article->getNumber('level')));

    $status = new ItemStatus();
    $statusName = $status->getStatus($article->getString('status'));

    # ----------------------------------------------------------------------------------------------------
    # REPORT DATA
    # ----------------------------------------------------------------------------------------------------
    $reports = retrieveArticleReport($id);

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

                <section class="section-heading">
                    <div class="section-heading-content">
                        <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".ARTICLE_FEATURE_FOLDER."/"?>" class="heading-back-button"><i class="fa fa-angle-left"></i> <?=system_showText(LANG_SITEMGR_NAVBAR_ARTICLE);?></a>
                        <h1 class="section-heading-title"><?=string_ucwords(system_showText(LANG_SITEMGR_REPORT_TRAFFICREPORT))?> - <?=$article->getString("title")?></h1>
                    </div>
                </section>

                <section class="row tab-options">
                    <div class="tab-content">
                        <div class="tab-pane active">
                            <div class="container">
                                <div class="col-md-12 form-horizontal">
                                    <div class="table-responsive">
                                        <?php if (count($reports) > 0) { ?>
                                            <?php include(INCLUDES_DIR."/tables/table_article_reports.php"); ?>
                                        <?php } else { ?>
                                            <p class="alert alert-info"><?=system_showText(LANG_SITEMGR_REPORT_NORECORD)?></p>
                                        <?php } ?>
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
            </div>
        </div>
    </main>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/modules.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
