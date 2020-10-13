<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/design/email-editor/index.php
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

    mixpanel_track("Accessed section Email Editor");

	$url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/design/email-editor/index.php";

	extract($_GET);
    extract($_POST);

	# ----------------------------------------------------------------------------------------------------
	# ENABLE/DISABLE NOTIFICATION
	# ----------------------------------------------------------------------------------------------------
	if (($deactive == '0') || ($deactive == '1')) {
        $emailObj = new EmailNotification($id);
        $activation = $emailObj->changeStatus();

        $status = $deactive ? 'enabled' : 'disabled';
        mixpanel_track("Email Notification $status",[
            'Email' => $emailObj->getString('email')
        ]);

        header("Location: $url_redirect");
        exit;
    }

	# ----------------------------------------------------------------------------------------------------
	# PAGE BROWSING
	# ----------------------------------------------------------------------------------------------------
	$pageObj  = new pageBrowsing("Email_Notification", $screen, false, "id", "id", $letter);
	$emails = $pageObj->retrievePage();

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
                        <h1 class="section-heading-title"><?=system_showText(LANG_SITEMGR_MENU_EMAILNOTIF)?> <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="<?=system_showText(LANG_SITEMGR_EMAILNOTIFICATION_TITLEMANAGE)?>"></span></h1>
                    </div>
                </section>

                <section class="section-form">
                    <div class="col-sm-12">
                        <div class="panel panel-default">
                            <div class="table-responsive">
                                <?php include(INCLUDES_DIR."/tables/table_notifications.php"); ?>
                            </div>
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
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/email-editor.php";
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
