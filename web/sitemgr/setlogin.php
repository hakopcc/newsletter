<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/setlogin.php
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
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);
	$destiny = $_GET["destiny"] ? $_GET["destiny"] : $_POST["destiny"];
	if ($_SERVER["QUERY_STRING"]) {
		if (string_strpos($_SERVER["QUERY_STRING"], "query=") !== false) {
			$query = string_substr($_SERVER["QUERY_STRING"], string_strpos($_SERVER["QUERY_STRING"], "query=")+6);
		} else {
			$query = $_GET["query"] ? $_GET["query"] : $_POST["query"];
			$query = urldecode($query);
		}
	} else {
		$query = $_GET["query"] ? $_GET["query"] : $_POST["query"];
		$query = urldecode($query);
	}
	if ($destiny) {

        if (EDIRECTORY_FOLDER){
            $url = EDIRECTORY_FOLDER.str_replace(EDIRECTORY_FOLDER, "", $destiny);
        } else {
            $url = $destiny;
        }

		if ($query) $url .= "?".$query;
	} else {
		$url = DEFAULT_URL."/".SITEMGR_ALIAS."/";
	}

	# ----------------------------------------------------------------------------------------------------
	# VALIDATION
	# ----------------------------------------------------------------------------------------------------
	if (DEMO_DEV_MODE || DEMO_LIVE_MODE) {
		header("Location: ".$url);
		exit;
	} else {
		setting_get("sitemgr_first_login", $sitemgr_first_login);
		if ($sitemgr_first_login != "yes") {
			header("Location: ".$url);
			exit;
		}
		$smusername = "";
		if (permission_hasSMPermSection(SITEMGR_PERMISSION_SUPERADMIN)) {
            header("Location: ".$url);
            exit;
		}
	}

	# ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
	if ($_SERVER['REQUEST_METHOD'] == "POST") {

		if ($changelogin) {

			$validate_sitemgrcurrentpassword = true;
			setting_get("sitemgr_password", $sitemgr_password);
			if ($sitemgr_password != md5($current_password)) {
				$validate_sitemgrcurrentpassword = false;
				$error_currentpassword = system_showText(LANG_SITEMGR_MSGERROR_CURRENTPASSWORDINCORRECT);
			}

			$_POST["setlogin"] = true;
			if ($validate_sitemgrcurrentpassword && validate_SM_changelogin($_POST, $message_changelogin)) {

				if ($username) {
					setting_get("sitemgr_username", $sm_username);
					if ($username != $sm_username) {
						setting_set("sitemgr_username", $username);
						$actions[] = '&#149;&nbsp;'.system_showText(LANG_SITEMGR_MANAGEACCOUNT_USERNAMEWASCHANGED);
					}
				}

				if ($password) {
					$pwDBObj = db_getDBObject(DEFAULT_DB, true);
					$sql = "UPDATE Setting SET value = ".db_formatString(md5($password))." WHERE name = 'sitemgr_password'";
					$pwDBObj->query($sql);
					$actions[] = '&#149;&nbsp;'.system_showText(LANG_SITEMGR_MANAGEACCOUNT_PASSWORDWASCHANGED);
				}

				if ($first_name) {
                    setting_set("sitemgr_firstname", $first_name);
                }

                if ($last_name) {
                    setting_set("sitemgr_lastname", $last_name);
                }

				if ($actions) {
					$message_changelogin .= implode("<br />", $actions);
				}

                //Update todo Items
                todo_updateItemsFirstLogin();

				header("Location: ".$url);
				exit;
			}

		}

	}

	# ----------------------------------------------------------------------------------------------------
	# FORMS DEFINES
	# ----------------------------------------------------------------------------------------------------
	setting_get("sitemgr_username", $username);
    $first_name ?: setting_get('install_name', $first_name);

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/header.php");
?>
    <main class="main-dashboard" has-sidebar="false" id="login-page">
        <div class="main-wrapper">
            <?php include(SM_EDIRECTORY_ROOT."/layout/navbar.php"); ?>
            <div class="main-content" content-full="false">
                <?php if (!$_SESSION['SESS_SM_ID']) { ?>
                    <div class="container-fluid row">
                        <div class="col-md-5 col-md-offset-1">
                            <section class="heading">
                                <h1><?=system_showText(LANG_SITEMGR_HOME_WELCOME)?>!</h1>
                                <p><?=nl2br(system_showText(LANG_SITEMGR_SETLOGIN_INFO1))?></p>
                            </section>
                        </div>
                        <div class="col-md-5">
                            <form name="changelogin" role="form" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                                <input type="hidden" name="destiny" value="<?=$destiny?>" />
                                <input type="hidden" name="query" value="<?=urlencode($query)?>" />
                                <div class="panel panel-default panel-set-login">
                                    <div class="panel-heading"><?=string_ucwords(system_showText(LANG_SITEMGR_MANAGEACCOUNT_SITEMGRACCOUNT))?></div>
                                    <div class="panel-body">
                                        <?php include(INCLUDES_DIR."/forms/form-changelogin.php"); ?>
                                    </div>
                                    <div class="panel-footer text-center">
                                        <button type="submit" name="changelogin" value="Submit" class="btn btn-primary"><?=system_showText(LANG_LABEL_ACCOUNT_CHANGEPASS);?></button>
                                    </div>
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
	$customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/setlogin.php";
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
