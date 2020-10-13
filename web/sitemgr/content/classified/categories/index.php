<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/classified/categories/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include('../../../../conf/loadconfig.inc.php');

    # ----------------------------------------------------------------------------------------------------
    # VALIDATE FEATURE
    # ----------------------------------------------------------------------------------------------------
    if (CLASSIFIED_FEATURE != 'on') {
        header('Location: ' .DEFAULT_URL. '/' .SITEMGR_ALIAS. '/');
        exit;
    }

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track('Accessed Manage Classified Categories section');

	$url_redirect = '' .DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .CLASSIFIED_FEATURE_FOLDER. '/categories';
	$url_base = '' .DEFAULT_URL. '/' .SITEMGR_ALIAS. '';
	$sitemgr = 1;
	$table_category = 'ClassifiedCategory';
	$message_no_record = LANG_SITEMGR_CLASSIFIED_CATEGORY_NORECORD;

	$url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

    # ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
    include(INCLUDES_DIR. '/code/add_mult_categories.php');

	# ----------------------------------------------------------------------------------------------------
	# PAGE BROWSING
	# ----------------------------------------------------------------------------------------------------
    $isNullSegment = '';
    if (!($category_id > 0)){
        $isNullSegment = 'ISNULL(category_id) OR ';
    }
	$pageObj  = new pageBrowsing('ClassifiedCategory', $screen, RESULTS_PER_PAGE, 'title, id', 'title', $letter, $isNullSegment . ' category_id = ' .db_formatNumber($category_id), 'id, `title`, enabled');
	$categories = $pageObj->retrievePage('array');

	$paging_url = DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .CLASSIFIED_FEATURE_FOLDER. '/categories/index.php?category_id=' .$category_id;

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT. '/layout/header.php');

?>
    <div id="loading_ajax" class="alert alert-loading alert-loading-fullscreen" style="display: none;">
        <img src="<?= DEFAULT_URL; ?>/<?= SITEMGR_ALIAS ?>/assets/img/loading-128.gif" class="alert-img-center">
    </div>

    <main class="main-dashboard" id="view-content-list">
        <nav class="main-sidebar">
            <?php include(SM_EDIRECTORY_ROOT. '/layout/sidebar-dashboard.php'); ?>
            <div class="sidebar-submenu">
                <?php include(SM_EDIRECTORY_ROOT . '/layout/sidebar.php'); ?>
            </div>
        </nav>
        <div class="main-wrapper">
            <?php include(SM_EDIRECTORY_ROOT. '/layout/navbar.php'); ?>
            <?php include(SM_EDIRECTORY_ROOT. '/layout/submenu-content.php'); ?>
            <div class="main-content list-item-content" content-full="true">
                <?php
                require(SM_EDIRECTORY_ROOT. '/registration.php');
                require(EDIRECTORY_ROOT. '/includes/code/checkregistration.php');

                if ($categories) { ?>
                    <div class="list-content">
                        <?php include(INCLUDES_DIR. '/lists/list-categories.php'); ?>

                        <div class="content-control-bottom pagination-responsive">
                            <?php include(INCLUDES_DIR. '/lists/list-pagination.php'); ?>
                        </div>
                    </div>
                <?php } else {
                    include(SM_EDIRECTORY_ROOT. '/layout/norecords.php');
                } ?>
            </div>
        </div>
        <input type="hidden" id="module" value="classified">
    </main>

<?php
    include INCLUDES_DIR . '/modals/modal-delete-category.php';
    include INCLUDES_DIR . '/modals/modal-add-mult-categories.php';
    include INCLUDES_DIR . '/modals/modal-add-category.php';

    # ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/categories.php';
    include SM_EDIRECTORY_ROOT . '/layout/footer.php';
