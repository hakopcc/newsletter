<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/deal/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # VALIDATION
    # ----------------------------------------------------------------------------------------------------
    if ( PROMOTION_FEATURE != "on" || CUSTOM_PROMOTION_FEATURE != "on" || CUSTOM_HAS_PROMOTION != "on"){
        header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/");
        exit;
    }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track("Accessed Manage Deal section");

	$url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".PROMOTION_FEATURE_FOLDER;
	$url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
	$sitemgr = 1;

	$url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));
    $manageOrder = system_getManageOrderBy($_POST["order_by"] ? $_POST["order_by"] : $_GET["order_by"], "Promotion",  $fields);

	extract($_GET);
	extract($_POST);

    $manageModule = "promotion";
    $manageModuleFolder = PROMOTION_FEATURE_FOLDER;

    # ----------------------------------------------------------------------------------------------------
	# MANAGE MOBULDE BACKEND - SEARCH / BULK UPDATE / DELETE
	# ----------------------------------------------------------------------------------------------------
    include(INCLUDES_DIR."/code/admin-manage-module.php");

	// Page Browsing /////////////////////////////////////////
	unset($pageObj);
	$pageObj = new pageBrowsing("Promotion", $screen, RESULTS_PER_PAGE, ($_GET["newest"] ? "id DESC" : $manageOrder), "name", $letter, $where, $fields);
	$promotions = $pageObj->retrievePage();
	$paging_url = DEFAULT_URL."/".SITEMGR_ALIAS."/content/".PROMOTION_FEATURE_FOLDER."/index.php";

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
                <?php if ($promotions) { ?>
                    <div class="list-content">
                        <?php include(INCLUDES_DIR."/lists/list-module.php"); ?>

                        <div class="content-control-bottom pagination-responsive">
                            <?php include(INCLUDES_DIR."/lists/list-pagination.php"); ?>
                        </div>
                    </div>

                    <div class="view-content">
                        <?php include(SM_EDIRECTORY_ROOT."/content/view-module.php"); ?>
                    </div>
                <?php } else { ?>
                    <?php include(SM_EDIRECTORY_ROOT."/layout/norecords.php"); ?>
                <?php } ?>
            </div>
        </div>
    </main>
<?php
    include(INCLUDES_DIR."/modals/modal-delete.php");
    include(INCLUDES_DIR."/modals/modal-bulk.php");
    include(INCLUDES_DIR."/modals/modal-search-module.php");

	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/general.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
