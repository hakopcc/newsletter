<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/sites/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include '../../conf/loadconfig.inc.php';

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

	mixpanel_track('Accessed section Manage Sites');

	$url_redirect = DEFAULT_URL. '/' .SITEMGR_ALIAS. '/sites';
	$url_base = DEFAULT_URL. '/' .SITEMGR_ALIAS;

    # ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if ($_POST['id'] === SELECTED_DOMAIN_ID || DEMO_LIVE_MODE || sess_getSMIdFromSession() ) {
            header('Location: ' .DEFAULT_URL. '/' .SITEMGR_ALIAS. '/sites/');
            exit;
        }

		$domain = new Domain($_POST['id']);
        $domain->Delete();

        mixpanel_track('Deleted a Site');

        // Remove domain yaml file
        $symfony = new Symfony('domain.yml');
        $symfony->remove('multi_domain', $domain->getString('url'));

        $message = 1;
		header('Location: ' .DEFAULT_URL. '/' .SITEMGR_ALIAS. '/sites/index.php?message=' .$message);
		exit;
	}

	// Page Browsing /////////////////////////////////////////
    $whereLiveMode = '';
    if (DEMO_LIVE_MODE && strpos($_SERVER['SERVER_NAME'], 'demodirectory.com.br') === false) {
        $whereLiveMode = 'AND id not IN (3, 7, 8)';
    }
	unset($pageObj);
	$pageObj  = new pageBrowsing('Domain', 1, false, 'name', 'name', false, "status='A' $whereLiveMode", '*', false, false, true);
	$domains = $pageObj->retrievePage();

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include SM_EDIRECTORY_ROOT. '/layout/header.php';
?>
    <main class="main-dashboard">
        <nav class="main-sidebar">
            <?php include SM_EDIRECTORY_ROOT. '/layout/sidebar-dashboard.php'; ?>
            <div class="sidebar-submenu">
                <?php include SM_EDIRECTORY_ROOT . '/layout/sidebar.php'; ?>
            </div>
        </nav>
        <div class="main-wrapper">
            <?php include SM_EDIRECTORY_ROOT. '/layout/navbar.php'; ?>
            <div class="main-content" content-full="true">
                <?php
                    require SM_EDIRECTORY_ROOT.'/registration.php';
                    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
                ?>

                <section class="section-heading">
                    <div class="section-heading-content">
                        <h1 class="section-heading-title"><?=system_showText(LANG_SITEMGR_MANAGE_SITES)?> <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="<?=system_showText(LANG_SITEMGR_DOMAIN_TIP)?>"></span></h1>
                    </div>
                </section>

                <section class="row form-thumbnails">
                    <div class="row list">
                        <?php include INCLUDES_DIR. '/lists/list-domains.php'; ?>
                    </div>
                </section>
            </div>
        </div>
    </main>
<?php
    include INCLUDES_DIR. '/modals/modal-delete.php';

	# ----------------------------------------------------------------------------------------------------
	# FOOTER
    # ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
