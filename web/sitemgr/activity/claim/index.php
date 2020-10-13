<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/activity/claim/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include('../../../conf/loadconfig.inc.php');

	# ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (CLAIM_FEATURE != 'on') {
		header('Location:' .DEFAULT_URL. '/' .SITEMGR_ALIAS);
		exit;
	}

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track('Accessed Manage Claims section');

    $url_redirect = DEFAULT_URL. '/' .SITEMGR_ALIAS. '/activity/claim';
	$url_base     = DEFAULT_URL. '/' .SITEMGR_ALIAS;

	$url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

	// Page Browsing /////////////////////////////////////////

	$claim_fields =
		"*,
		if (status='complete','1',
		if (status='progress','2',
		if (status='incomplete','3',
		if (status='approved','4',
		if (status='denied','5',0))))) as id_status";

    //Search
    if ($search_id) $sql_where[] = ' id = ' .db_formatString($search_id). ' ';
	if ($search_status) $sql_where[] = " status = '$search_status' ";
	if ($search_title) $sql_where[] = ' ( listing_title LIKE ' .db_formatString('%' .$search_title. '%'). ' OR old_title LIKE ' .db_formatString('%' .$search_title. '%'). ' OR new_title LIKE ' .db_formatString('%' .$search_title. '%'). ' ) ';
	if ($search_no_owner == 1) $sql_where[] = ' account_id = 0 ';
	elseif ($search_account_id) $sql_where[] = " account_id = $search_account_id ";
	if ($sql_where) $where .= ' ' .implode(' AND ', $sql_where). ' ';

	$pageObj  = new pageBrowsing('Claim', $screen, RESULTS_PER_PAGE, 'id_status, date_time DESC, id Desc', 'title', $letter, ($where ? $where : false), $claim_fields);
	$claims = $pageObj->retrievePage();

	$paging_url = DEFAULT_URL. '/' .SITEMGR_ALIAS. '/activity/claim/index.php';

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT. '/layout/header.php');
?>
    <main class="main-dashboard" id="view-content-list">
        <nav class="main-sidebar">
            <?php include(SM_EDIRECTORY_ROOT. '/layout/sidebar-dashboard.php'); ?>
            <div class="sidebar-submenu">
                <?php include(SM_EDIRECTORY_ROOT . '/layout/sidebar.php'); ?>
            </div>
        </nav>
        <div class="main-wrapper">
            <?php include(SM_EDIRECTORY_ROOT. '/layout/navbar.php'); ?>

            <div class="content-control header-bar" id="search-all">
                <form class="form-inline" role="search" action="<?=system_getFormAction($_SERVER["PHP_SELF"]);?>" method="get">
                    <div class="control-searchbar">
                        <div class="bulk-check-all">
                            <label class="sr-only">Check all</label>
                            <input type="checkbox" id="check-all">
                        </div>
                        <div class="form-group">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control search hidden-xs" name="search_title" value="<?=$search_title?>" onblur="populateField(this.value, 'search_title');" placeholder="<?=system_showText(LANG_LABEL_SEARCHKEYWORD);?>">
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
            </div>

            <div class="main-content list-item-content" content-full="true">
                <?php
                    require(SM_EDIRECTORY_ROOT. '/registration.php');
                    require(EDIRECTORY_ROOT. '/includes/code/checkregistration.php');
                ?>
                <?php if ($claims) { ?>
                    <div class="list-content">
                        <?php include(INCLUDES_DIR. '/lists/list-claim.php'); ?>

                        <div class="content-control-bottom pagination-responsive">
                            <?php include(INCLUDES_DIR. '/lists/list-pagination.php'); ?>
                        </div>
                    </div>

                    <div class="view-content">
                        <?php include(SM_EDIRECTORY_ROOT . '/activity/claim/view-claim.php'); ?>
                    </div>
                <?php } else { ?>
                <?php include(SM_EDIRECTORY_ROOT. '/layout/norecords.php'); } ?>
            </div>
        </div>
    </main>

    <?php include(INCLUDES_DIR. '/modals/modal-search-claim.php'); ?>

<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT . "/assets/custom-js/claim.php";
	include(SM_EDIRECTORY_ROOT . '/layout/footer.php');
