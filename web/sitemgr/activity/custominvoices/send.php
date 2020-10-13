<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/activity/custominvoices/send.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

     # ----------------------------------------------------------------------------------------------------
    # VALIDATE FEATURE
    # ----------------------------------------------------------------------------------------------------
    if (PAYMENT_FEATURE != "on" || ( (CREDITCARDPAYMENT_FEATURE != "on") && (PAYMENT_INVOICE_STATUS != "on") ) || CUSTOM_INVOICE_FEATURE != "on" )
    {
        header("Location:".DEFAULT_URL."/".SITEMGR_ALIAS."");
        exit;
    }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
    permission_hasSMPerm();

    $url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/activity/custominvoices";
	$url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
	$sitemgr = 1;
    $url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

    # ----------------------------------------------------------------------------------------------------
    # CODE
    # ----------------------------------------------------------------------------------------------------
    include(INCLUDES_DIR."/code/custominvoice-send.php");

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

                <form role="form" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post" enctype="multipart/form-data">
                    <section class="section-heading">
                        <div class="section-heading-content">
                            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/activity/custominvoices/"?>" class="heading-back-button"><i class="fa fa-angle-left"></i> <?=ucfirst(system_showText(LANG_SITEMGR_CUSTOMINVOICE_PLURAL));?></a>
                            <h1 class="section-heading-title"><?=system_showText(LANG_SITEMGR_SEND)?> <?=system_showText(LANG_SITEMGR_CUSTOMINVOICE)?></h1>
                        </div>
                        <div class="section-heading-actions">
                            <button type="submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SEND);?></button>
                        </div>
                    </section>

                    <section class="row tab-options">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <div class="container">
                                    <?php include(INCLUDES_DIR."/forms/form-custominvoice-send.php"); ?>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="row footer-action">
                        <div class="container">
                            <div class="col-xs-12 text-right">
                                <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/activity/custominvoices"?>" class="btn btn-default"><?=system_showText(LANG_CANCEL)?></a>
                                <button type="submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SEND);?></button>
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
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
