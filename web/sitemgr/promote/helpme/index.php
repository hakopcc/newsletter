<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/promote/helpme/index.php
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

    mixpanel_track("Accessed Help me to Promote section");

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
            <div class="main-content wysiwyg" content-full="true">
                <?php
                    require SM_EDIRECTORY_ROOT.'/registration.php';
                    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
                ?>

                <section class="section-heading">
                    <div class="section-heading-content">
                        <h1 class="section-heading-title"><?=system_showText(LANG_SITEMGR_PROMOTE_HELP)?> <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="<?=system_showText(LANG_SITEMGR_PROMOTE_TITLE);?>"></span></h1>
                    </div>
                </section>

                <br>

                <section class="well well-intro">
                    <div class="row">
                        <div class="col-sm-1 col-xs-3 text-center">
                            <i class="icon-document50"></i>
                        </div>
                        <div class="col-sm-11 col-xs-9">
                            <h2>Whitepapers</h2>
                            <p><?=system_showText(LANG_SITEMGR_PROMOTE_1);?></p>
                            <a data-mixpanel-event='Clicked on link "See All Resources" from Help me to promote section' href="http://www.<?=$sitemgr_language == "pt_br" ? "edirectory.com.br/recursos-guia/" : "edirectory.com/directory-resources/"?>" target="_blank" class="btn btn-info"><?=system_showText(LANG_SITEMGR_PROMOTE_6);?></a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-1 col-xs-3 text-center">
                            <i class="icon-ion-ios7-search-strong"></i>
                        </div>
                        <div class="col-sm-5 col-xs-9">
                            <h2><?=system_showText(LANG_SITEMGR_CONTENT_SEOCENTER)?></h2>
                            <p><?=system_showText(LANG_SITEMGR_PROMOTE_2);?></p>
                            <a data-mixpanel-event='Clicked on link "SEO Center" from Help me to promote section' href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/promote/seo-center/"?>" class="btn btn-info"><?=system_showText(LANG_SITEMGR_CONTENT_SEOCENTER)?></a>
                        </div>
                        <?php if (MAIL_APP_FEATURE == "on") { ?>
                            <div class="col-sm-1 col-xs-3 text-center">
                                <i class="icon-paper113"></i>
                            </div>
                            <div class="col-sm-5 col-xs-9">
                                <h2><?=system_showText(LANG_SITEMGR_MAILAPP_NEWSLETTERS)?></h2>
                                <p><?=system_showText(LANG_SITEMGR_PROMOTE_3);?></p>
                                <a data-mixpanel-event='Clicked on link "Newsletters" from Help me to promote section' href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/promote/newsletter/"?>" class="btn btn-info"><?=system_showText(LANG_SITEMGR_MAILAPP_NEWSLETTERS)?></a>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="row">
                        <?php if ((CREDITCARDPAYMENT_FEATURE === 'on' || PAYMENT_INVOICE_STATUS === 'on') && PAYMENTSYSTEM_FEATURE === 'on') { ?>
                            <div class="col-sm-1 col-xs-3 text-center">
                                <i class="icon-cent1"></i>
                            </div>
                            <div class="col-sm-5 col-xs-9">
                                <h2><?=system_showText(LANG_SITEMGR_PROMO_PACK)?></h2>
                                <p><?=system_showText(LANG_SITEMGR_PROMOTE_5);?></p>
                                <a data-mixpanel-event='Clicked on link "Promotions & Packages" from Help me to promote section' href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/promote/promotions/"?>" class="btn btn-info"><?=system_showText(LANG_SITEMGR_PROMO_PACK)?></a>
                            </div>
                        <?php } ?>
                        <div class="col-sm-1 col-xs-3 text-center">
                            <i class="icon-medal41"></i>
                        </div>
                        <div class="col-sm-5 col-xs-9">
                            <h2><?=system_showText(LANG_SITEMGR_AWARD_BADGE)?></h2>
                            <p><?=system_showText(LANG_SITEMGR_PROMOTE_4);?></p>
                            <a data-mixpanel-event='Clicked on link "Awards & Badges" from Help me to promote section' href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/promote/awards/"?>" class="btn btn-info"><?=system_showText(LANG_SITEMGR_AWARD_BADGE)?></a>
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
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
