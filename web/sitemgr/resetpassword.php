<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/resetpassword.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();

	# ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
	if ($_SERVER['REQUEST_METHOD'] == "POST") {

		if ($_SESSION['SESS_SM_ID']) {
			$smaccountObj = new SMAccount($_SESSION['SESS_SM_ID']);
			$sitemgr_username = $smaccountObj->getString("username");
		} else {
			setting_get("sitemgr_username", $sitemgr_username);
		}

		if ($_POST["password"]) {
			if ($_SESSION['SESS_SM_ID']) {
				$message = validate_password($_POST["password"], $_POST["retype_password"], true);
				if (!$message) {
					$smaccountObj->setString("password", $_POST["password"]);
					$smaccountObj->updatePassword();
					$success_message = system_showText(LANG_SITEMGR_MANAGEACCOUNT_PASSWORDSUCCESSUPDATED);
				}
			} else {
				if (validate_SM_changelogin($_POST, $message)) {
					$pwDBObj = db_getDBObject(DEFAULT_DB, true);
					$sql = "UPDATE Setting SET value = ".db_formatString(md5($_POST["password"]))." WHERE name = 'sitemgr_password'";
					$pwDBObj->query($sql);
					$success_message = system_showText(LANG_SITEMGR_MANAGEACCOUNT_PASSWORDSUCCESSUPDATED);
				}
			}
		} else {
			$message = system_showText(LANG_SITEMGR_MSGERROR_PASSWORDISREQUIRED);
		}

	}

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	if ($_GET["key"]) {

		$forgotPasswordObj = new forgotPassword($_GET["key"]);

		if ($forgotPasswordObj->getString("unique_key") && ($forgotPasswordObj->getString("section") == "sitemgr")) {

			if (!$forgotPasswordObj->getString("account_id")) {
				setting_get("sitemgr_username", $sitemgr_username);
			} else {
				$smaccountObj = new SMAccount($forgotPasswordObj->getString("account_id"));
				$sitemgr_username = $smaccountObj->getString("username");
			}

			$forgotPasswordObj->Delete();

			if (!$sitemgr_username) {
				$error_message = system_showText(LANG_SITEMGR_FORGOTPASS_SORRYWRONGACCOUNT);
			}

		} else {
			$error_message = system_showText(LANG_SITEMGR_FORGOTPASS_SORRYWRONGKEY);
		}

	} else {
		$error_message = system_showText(LANG_SITEMGR_FORGOTPASS_SORRYWRONGKEY);
	}

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/header.php");
?>
    <main class="main-dashboard" has-sidebar="false" id="login-page">
        <div class="main-wrapper">
            <?php include(SM_EDIRECTORY_ROOT."/layout/navbar.php"); ?>
            <div class="main-content" content-full="false">
                <?php
                    require(EDIRECTORY_ROOT."/".SITEMGR_ALIAS."/registration.php");
                    require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
                ?>
                <?php if (!$success_message && !$error_message) { ?>
                    <div class="panel panel-default">
                        <div class="panel-heading"><?=string_ucwords(system_showText(LANG_SITEMGR_RESET))?> <?=string_ucwords(system_showText(LANG_SITEMGR_LABEL_PASSWORD))?></div>
                        <div class="panel-body">
                            <form name="formResetPassword" role="form" method="post" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>">
                                <div class="form-group">
                                    <label class="control-label" for="id-password"><?=system_showText(LANG_LABEL_NEW_PASSWORD)?></label>
                                    <input type="password" autocomplete="off" name="password" id="id-password" required maxlength="<?=PASSWORD_MAX_LEN?>" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label class="label-control" for="retype-password"><?=system_showText(LANG_SITEMGR_LABEL_RETYPEPASSWORD)?></label>
                                    <input type="password" autocomplete="off" name="retype_password" id="retype-password" required class="form-control" />
                                </div>
                                <div class="form-group">
                                    <button type="submit" value="Submit" class="btn btn-primary btn-block"><?=system_showText(LANG_SITEMGR_SUBMIT)?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </main>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	$customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/resetpassword.php";
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
