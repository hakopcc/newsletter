<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/forgot.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	$section = "sitemgr";
	include(INCLUDES_DIR."/code/forgot_password.php");

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/header.php");
?>
    <main class="main-dashboard" has-sidebar="false" id="login-page">
        <div class="main-wrapper">
            <?php include(SM_EDIRECTORY_ROOT."/layout/navbar.php"); ?>
            <div class="main-content" content-full="false">
                <div class="panel panel-default">
					<div class="panel-heading"><?=LANG_SITEMGR_FORGOOTTEN_PASS_1;?></div>
					<div class="panel-body">
						<form name="forgotpassword" role="form" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
				            <?php include(INCLUDES_DIR."/forms/form_forgot_password.php"); ?>
				        </form>
			        </div>
		        </div>
            </div>
        </div>
    </main>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	$customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/forgot-password.php";

	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
