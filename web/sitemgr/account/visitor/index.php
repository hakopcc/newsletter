<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/account/visitor/index.php
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

    mixpanel_track("Accessed Visitor Accounts section");

    if (SOCIALNETWORK_FEATURE != "on") {
        exit;
    }

	$url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
    $url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/account/visitor/index.php";

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$_GET = format_magicQuotes($_GET);
	extract($_GET);
	$_POST = format_magicQuotes($_POST);
    extract($_POST);

    $messageSection = 'visitor';
    $manageModule = 'visitor';

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        //Delete account
        if ($action == "delete") {
            mixpanel_track("Deleted a Visitor Account");
            $account = new Account($id);
            $account->delete();
            $message = 3;
            header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/account/visitor/index.php?message=".$message."&screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : "")."");
            exit;
        } else {
        //Forgot password
            mixpanel_track("Sent forgot password email to a Visitor");
            $section = "members";
            include(INCLUDES_DIR."/code/forgot_password.php");
        }
	}

    //Search (Contact)
    $sql_where[] = "is_sponsor = 'n'";
    if ($search_username) {
        $search_term = explode(" ", $search_username);
        $auxWhere = array();
        foreach ($search_term as $term) {
            $auxWhere[] = "email LIKE ".db_formatString('%'.$term.'%');
            $auxWhere[] = "first_name LIKE ".db_formatString('%'.$term.'%');
            $auxWhere[] = "last_name LIKE ".db_formatString('%'.$term.'%');
            $auxWhere[] = "company LIKE ".db_formatString('%'.$term.'%');
        }

        $sql_where[] = "id IN (SELECT account_id FROM Contact WHERE (".implode($auxWhere, " OR ")."))";
    }

    $where_clause = implode(" AND ", $sql_where);

	$url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

	// Page Browsing ////////////////////////////////////////
	$pageObj = new pageBrowsing("Account", $screen, RESULTS_PER_PAGE, "lastlogin DESC, username", "username", $letter, $where_clause, "*", false, false, true);
	$accounts = $pageObj->retrievePage();

	$paging_url = DEFAULT_URL."/".SITEMGR_ALIAS."/account/visitor/index.php";

	# PAGES DROP DOWN ----------------------------------------------------------------------------------------------
	$pagesDropDown = $pageObj->getPagesDropDown($_GET, $paging_url, $screen, system_showText(LANG_SITEMGR_PAGING_GOTOPAGE)." ", "this.form.submit();");
	# --------------------------------------------------------------------------------------------------------------
    # ----------------------------------------------------------------------------------------------------
    # BULK UPDATE
    # ----------------------------------------------------------------------------------------------------
    include(INCLUDES_DIR."/code/bulkupdate.php");
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
            <?php include(SM_EDIRECTORY_ROOT."/layout/submenu-content.php"); ?>
            <div class="content-control header-bar hidden" id="bulkupdate">
                <?php include(INCLUDES_DIR."/forms/form-bulkupdate.php"); ?>
            </div>

            <div class="main-content list-item-content" content-full="true">

                <?php
                require(SM_EDIRECTORY_ROOT."/registration.php");
                require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
                ?>

                <?php if ($accounts) { ?>
                    <div class="list-content">
                        <?php include(INCLUDES_DIR."/lists/list-accounts.php"); ?>

                        <div class="content-control-bottom pagination-responsive">
                            <?php include(INCLUDES_DIR."/lists/list-pagination.php");?>
                        </div>
                    </div>
                    <div class="view-content">
                        <?php include(SM_EDIRECTORY_ROOT."/account/view-account.php"); ?>
                    </div>
                <?php } else { ?>
                    <?php include(SM_EDIRECTORY_ROOT."/layout/norecords.php"); ?>
                <?php } ?>
            </div>
        </div>
    </main>
<?php
    include(INCLUDES_DIR."/modals/modal-bulk.php");
    include(INCLUDES_DIR."/modals/modal-delete.php");
    include(INCLUDES_DIR."/modals/modal-forgot.php");

	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/manage-account.php";
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
