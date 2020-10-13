<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/account/manager/manager.php
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

    // required because of the cookie var
	$username = "";

	extract($_GET);
	extract($_POST);

	$url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

	# ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
	if ($_SERVER['REQUEST_METHOD'] == "POST") {

        $success = false;

		if (($password == '0' && string_strlen($password) < 4)) {
			$sucess = false;
			$message_smaccount = system_showText(LANG_MSG_ENTER_PASSWORD_WITH_MIN_CHARS)." ".PASSWORD_MIN_LEN." ".system_showText(LANG_LABEL_CHARACTERES).".";
		} else {
			if (validate_smaccount($_POST, $message_smaccount)) {
                $success = true;
				if ($permission) {
					$permissions = $permission;
					$permission = 0;
					foreach ($permissions as $each_permission) {
						$permission += $each_permission;
					}
				} else {
					$permission = 0;
				}
                $_POST["active"] = ($_POST["status"] == "1" ? "y" : "n");
				$_POST["permission"] = $permission;

				$smaccount = new SMAccount($_POST);
				$smaccount->Save();

				if ($password) {
					$smaccount->setString("password", $password);
					$smaccount->updatePassword();
				}

				if ($id) {
                    mixpanel_track("Edited a Site Manager Account");
					$message = 5;
				} else {
                    mixpanel_track("Added a Site Manager Account");
					$newest = "1";
					$message = 6;
				}

				header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/account/manager/manager.php?message=".$message."&id=".$smaccount->getNumber("id")."&screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : "")."");
				exit;

			}
		}

		// removing slashes added if required
		$_POST = format_magicQuotes($_POST);
		$_GET  = format_magicQuotes($_GET);
		extract($_POST);
		extract($_GET);

	}

	# ----------------------------------------------------------------------------------------------------
	# FORMS DEFINES
	# ----------------------------------------------------------------------------------------------------
	if ($id) {
		$smaccount = new SMAccount($id);
        $smaccount->extract();
	}

	extract($_POST);
	extract($_GET);

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
            <div class="main-content" content-full="true">
                <?php
                    require(SM_EDIRECTORY_ROOT."/registration.php");
                    require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
                ?>

                <form role="form" name="smaccount" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                    <?=system_getFormInputSearchParams((($_POST)?($_POST):($_GET)));?>
                    <input type="hidden" name="letter" value="<?=$letter?>" />
                    <input type="hidden" name="screen" value="<?=$screen?>" />
                    <input type="hidden" name="id" value="<?=$id?>" />
                    <input type="hidden" name="status" id="managerStatusState" value="<?=($active == 'y' ? '1' : '2')?>">

                    <section class="section-heading">
                        <div class="section-heading-content">
                            <a href="<?=(DEFAULT_URL."/".SITEMGR_ALIAS."/account/manager/index.php?screen=$screen".(($url_search_params) ? "&$url_search_params" : ""))?>" class="heading-back-button"><i class="fa fa-angle-left"></i> <?=system_showText(LANG_SITEMGR_NAVBAR_SITEMGRACCOUNTS);?></a>
                            <?php if ($id) { ?>
                                <h1 class="section-heading-title"><?= system_showText(LANG_SITEMGR_EDIT);?> <?=$first_name?> <?=$last_name?></h1>
                            <?php } else { ?>
                                <h1 class="section-heading-title"><?= system_showText(LANG_SITEMGR_ADD_SMACCOUNT); ?></h1>
                            <?php } ?>
                        </div>
                        <div class="section-heading-actions">
                            <div class="toggle-listing-template">
                                <?=system_showText(LANG_SITEMGR_ACCOUNT_STATUS);?>
                                <div class="switch-button <?=($active == 'y' ? 'is-enable' : 'is-disable')?>">
                                    <span class="toggle-item"></span>
                                </div>
                            </div>
                            <button type="submit" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_MSG_SAVE_CHANGES);?></button>
                        </div>
                    </section>

                    <section class="section-form">
                        <div class="col-lg-8 col-sm-8 col-xs-12">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <label for="first_name"><?=system_showText(LANG_SITEMGR_LABEL_FIRSTNAME)?></label>
                                            <input type="text" name="first_name" id="first_name" value="<?=$first_name?>" class="form-control" />
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="last_name"><?=system_showText(LANG_SITEMGR_LABEL_LASTNAME)?></label>
                                            <input type="text" name="last_name" id="last_name" value="<?=$last_name?>" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <label for="username"><?=system_showText(LANG_SITEMGR_LABEL_USERNAME)?></label>
                                            <input id="username" type="email" name="username" value="<?=$username?>" class="form-control" onblur="populateField(this.value, 'email');"/>
                                            <input type="text" id="email" name="email" value="<?=$email?>" style="display:none;" />
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="phone"><?=system_showText(LANG_SITEMGR_LABEL_PHONE)?> <small class="small text-muted"><?=system_showText(LANG_SITEMGR_LABEL_SPAN_OPTIONAL)?></small></label>
                                            <input type="tel" name="phone" id="phone" value="<?=$phone?>" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <label for="password"><?=system_showText(LANG_SITEMGR_LABEL_PASSWORD)?></label>
                                            <input type="password" name="password" class="form-control" id="password">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="retpassword"><?=system_showText(LANG_SITEMGR_LABEL_RETYPEPASSWORD)?></label>
                                            <input type="password" name="retype_password" class="form-control" id="retpassword">
                                        </div>
                                    </div>

                                    <hr>
                                    <div class="row">
                                        <?php
                                            unset($account_permission);
                                            if ($_POST['permission']) {
                                                $account_permission = $_POST['permission'];
                                            } elseif ($permission) {
                                                $account_permission = $permission;
                                            }

                                            echo permission_getSMTable($account_permission, $myAdminAccount);
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="panel panel-form-media">
                                <div class="panel-heading">
                                    <?=system_showText(LANG_SITEMGR_LABEL_IPRESTRICTION);?>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label><?=system_showText(LANG_SITEMGR_SMACCOUNT_TIP1)?></label>
                                        <textarea class="form-control" name="iprestriction" id="iprestriction" rows="5"><?=$iprestriction?></textarea>
                                        <p class="help-block">
                                            <?=system_showText(LANG_SITEMGR_SMACCOUNT_TIP3)?>
                                        </p>
                                        <p class="help-block">
                                            <?=system_showText(LANG_SITEMGR_SMACCOUNT_TIP4)?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </section>

                    <section class="row footer-action">
                        <div class="col-xs-12 text-right">
                            <a href="<?=(DEFAULT_URL."/".SITEMGR_ALIAS."/account/manager/index.php?screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : ""))?>" class="btn btn-default"><?=system_showText(LANG_CANCEL);?></a>
                            <span class="separator"> <?=system_showText(LANG_OR);?>  </span>
                            <button type="submit" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_MSG_SAVE_CHANGES);?></button>
                        </div>
                    </section>
                </form>
            </div>
        </div>
    </main>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/manager.php';

	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
