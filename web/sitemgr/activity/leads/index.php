<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/activity/leads/index.php
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

	$url_redirect = DEFAULT_URL."/".SITEMGR_ALIAS."/activity/leads";
	$url_base     = DEFAULT_URL."/".SITEMGR_ALIAS;

	extract($_GET);
	extract($_POST);

    $manageModule = "lead";

    mixpanel_track("Accessed Leads section");

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include(EDIRECTORY_ROOT."/includes/code/lead.php");

	// Page Browsing /////////////////////////////////////////
    if (is_numeric($search_id))  $sql_where[] = " id = ".db_formatNumber($search_id);
	if ($item_id) 				 $sql_where[] = " item_id = '$item_id' ";

	if ($sql_where) {
		$where .= " ".implode(" AND ", $sql_where)." ";
    }

	$pageObj  = new pageBrowsing("Leads", $screen, RESULTS_PER_PAGE, "entered DESC", "first_name", $letter, $where);
	$leadsArr = $pageObj->retrievePage("object");

	$paging_url = DEFAULT_URL."/".SITEMGR_ALIAS."/activity/leads/index.php?item_type=$item_type&item_id=$item_id&item_screen=$item_screen&item_letter=$item_letter";

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

                <?php if ($leadsArr) { ?>
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

	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	$customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/lead.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
