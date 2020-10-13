<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/activity/invoices/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (PAYMENT_FEATURE != "on") {
        header("Location:".DEFAULT_URL."/".SITEMGR_ALIAS."");
        exit;
    }
	if (PAYMENT_INVOICE_STATUS != "on") {
        header("Location:".DEFAULT_URL."/".SITEMGR_ALIAS."");
        exit;
    }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track("Accessed Invoices section");

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$_GET  = format_magicQuotes($_GET);
    extract($_GET);
	extract($_POST);

    $url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

	$url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/activity/invoices";
	$url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."";

    # ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------

    $invoiceStatusObj = new InvoiceStatus();

    $sql_where[] = " hidden = 'n'";
	if ($invoiceStatusObj->getDefault()) {
        $sql_where[] = " status != '".$invoiceStatusObj->getDefault()."' ";
    }

	if ($sql_where) {
        $where = " ".implode(" AND ", $sql_where)." ";
    }

    include(INCLUDES_DIR."/code/transaction_manage.php");

    // Page Browsing /////////////////////////////////////////
	$pageObj  = new pageBrowsing("Invoice", $screen, RESULTS_PER_PAGE, "date DESC", "", "", $where);
	$invoices = $pageObj->retrievePage("array");

    $paging_url = DEFAULT_URL."/".SITEMGR_ALIAS."/activity/invoices/index.php";

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/header.php");
?>
    <main class="main-dashboard" id="view-content-list">
        <nav class="main-sidebar">
            <?php include(SM_EDIRECTORY_ROOT."/layout/sidebar-dashboard.php"); ?>
            <div class="sidebar-submenu">
                <?php include(SM_EDIRECTORY_ROOT . "/layout/sidebar.php"); ?>
            </div>
        </nav>
        <div class="main-wrapper">
            <?php include(SM_EDIRECTORY_ROOT."/layout/navbar.php"); ?>

            <div class="content-control header-bar" id="search-all">
                <form role="form" name="searchTop" class="form-inline" role="search" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="get">
                    <div class="control-searchbar">
                        <div class="bulk-check-all">
                            <label class="sr-only">Check all</label>
                            <input type="checkbox" id="check-all">
                        </div>
                        <div class="form-group">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control search" name="search_id" value="<?=$search_id?>" onblur="populateField(this.value, 'search_id');" placeholder="<?=system_showText(LANG_SITEMGR_LABEL_INVOICEID);?>">
                                <div class="input-group-btn">
                                    <!-- Button and dropdown menu -->
                                    <button class="btn btn-default hidden-xs" onclick="$('#search').submit();"><?=system_showText(LANG_SITEMGR_SEARCH);?></button>
                                    <button type="button" class="btn btn-default dropdown-toggle"  data-toggle="modal" data-target="#modal-search">
                                        <span class="hidden-xs caret"></span>
                                        <i class="visible-xs icon-ion-ios7-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="header-bar-action">
                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/activity/transactions"?>" class="action-button <?=(string_strpos($_SERVER["PHP_SELF"], "transactions/index.php") !== false ? "is-active" : "")?>"><?=(system_showText(LANG_SITEMGR_TRANSACTIONS))?></a>
                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/activity/invoices/"?>" class="action-button <?=(string_strpos($_SERVER["PHP_SELF"], "/invoices/index.php") !== false ? "is-active" : "")?>"><?=ucfirst(system_showText(LANG_SITEMGR_INVOICE_PLURAL))?></a>
                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/activity/custominvoices/"?>" class="action-button <?=(string_strpos($_SERVER["PHP_SELF"], "/custominvoices/index.php") !== false ? "is-active" : "")?>"><?=ucfirst(system_showText(LANG_SITEMGR_CUSTOMINVOICE_PLURAL))?></a>
                </div>
            </div>

            <div class="content-control header-bar hidden" id="bulkupdate">
                <?php include(INCLUDES_DIR."/forms/form-bulkupdate-transaction.php"); ?>
            </div>

            <div class="main-content list-item-content" content-full="true">
                <?php
                    require(SM_EDIRECTORY_ROOT."/registration.php");
                    require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
                ?>

                <?php if ($invoices) { ?>
                    <div class="list-content">
                        <?php include(INCLUDES_DIR."/lists/list-invoices.php"); ?>

                        <div class="content-control-bottom pagination-responsive">
                            <?php include(INCLUDES_DIR."/lists/list-pagination.php"); ?>
                        </div>
                    </div>

                    <div class="view-content">
                        <?php include(SM_EDIRECTORY_ROOT."/activity/invoices/view-invoice.php"); ?>
                    </div>
                <?php } else { ?>
                    <?php include(SM_EDIRECTORY_ROOT."/layout/norecords.php"); ?>
                <?php } ?>
            </div>
        </div>
    </main>
<?php
    include(INCLUDES_DIR."/modals/modal-delete.php");
    include(INCLUDES_DIR."/modals/modal-settings.php");
    include(INCLUDES_DIR."/modals/modal-bulk.php");
    include(INCLUDES_DIR."/modals/modal-search-invoice.php");

	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $modalSettingsPath = DEFAULT_URL."/".SITEMGR_ALIAS."/activity/invoices/settings.php";
    $pageSection = 'invoice';
	$customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/general.php";
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
