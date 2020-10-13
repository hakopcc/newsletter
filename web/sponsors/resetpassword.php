<?php
	/*==================================================================*\
	######################################################################
	#                                                                    #
	# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
	#                                                                    #
	# This file may not be redistributed in whole or part.               #
	# eDirectory is licensed on a per-domain basis.                      #
	#                                                                    #
	# ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
	#                                                                    #
	# http://www.edirectory.com | http://www.edirectory.com/license.html #
	######################################################################
	\*==================================================================*/

	# ----------------------------------------------------------------------------------------------------
	# * FILE: /sponsors/resetpassword.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSession();

	# ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
	if ($_SERVER['REQUEST_METHOD'] == "POST") {
		$accountObj = new Account(sess_getAccountIdFromSession());
		$member_username = $accountObj->getString("username");

		if ($_POST["password"]) {
			if (validate_MEMBERS_account($_POST, $message, sess_getAccountIdFromSession())) {
				$accountObj->setString("password", $_POST["password"]);
				$accountObj->updatePassword();
				$success_message = system_showText(LANG_MSG_PASSWORD_SUCCESSFULLY_UPDATED);
				$urlRedirect = DEFAULT_URL."/".MEMBERS_ALIAS."/account/index.php";
			}
		} else {
			$message = system_showText(LANG_MSG_PASSWORD_IS_REQUIRED);
		}

	}

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	if ($_GET["key"]) {
		$forgotPasswordObj = new forgotPassword($_GET["key"]);

		if ($forgotPasswordObj->getString("unique_key") && ($forgotPasswordObj->getString("section") == "members")) {
			$accountObj = new Account($forgotPasswordObj->getString("account_id"));
			$member_username = $accountObj->getString("username");

			$forgotPasswordObj->Delete();

			if (!$member_username) {
				$error_message = system_showText(LANG_MSG_WRONG_ACCOUNT);
			}
		} else {
			$error_message = system_showText(LANG_MSG_WRONG_KEY);
		}
	} else {
		$error_message = system_showText(LANG_MSG_WRONG_KEY);
	}

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");

    $cover_title = system_showText(LANG_LABEL_RESET_PASSWORD);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>
	<div class="modal-default modal-sign" is-page="true">
        <div class="modal-content">
            <div class="modal-body">
                <div class="content-tab content-sign-in">
				<?php if($success_message){ ?>
					<div class="alert alert-info">
						<?=$success_message;?>
						<a href="<?=$urlRedirect;?>"><?=system_showText(LANG_BUTTON_MANAGE_ACCOUNT)?></a>
					</div>
				<?php } elseif($error_message && !$message){ ?>
					<div class="alert alert-danger">
						<?=$error_message;?><br>
						<a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/forgot.php"><?=system_showText(LANG_LABEL_FORGOTPASSWORD);?></a>
					</div>
				<?php } else { ?>
					<?php if($message){ ?>
						<div class="alert alert-danger">
							<?=$message;?>
						</div>
					<?php } ?>
					<form name="formResetPassword" method="post" class="modal-form" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>">
						<div class="form-group">
							<label><?=system_showText(LANG_LABEL_USERNAME)?>:</label>
							<b class="form-control-static"><?=$member_username;?></b>
						</div>
						<br>
						<div class="form-group">
							<label for="password"><?=system_showText(LANG_LABEL_PASSWORD)?>:</label>
							<input type="password" name="password" class="input" id="password" maxlength="<?=PASSWORD_MAX_LEN?>" required>
							<small class="help-block"><?=system_showText(LANG_MSG_PASSWORD_MUST_BE_BETWEEN)?> <?=PASSWORD_MIN_LEN?> <?=system_showText(LANG_AND)?> <?=PASSWORD_MAX_LEN?> <?=system_showText(LANG_MSG_CHARACTERS_WITH_NO_SPACES)?></small>
						</div>
						<br>
						<div class="form-group">
							<label for="retype_password"><?=system_showText(LANG_LABEL_RETYPE_PASSWORD)?>:</label>
							<input type="password" name="retype_password" class="input" id="retype_password" required>
						</div>
						<br>
						<button type="submit" class="button button-bg is-primary" full-width="true" value="<?=system_showText(LANG_BUTTON_SUBMIT);?>"><?=system_showText(LANG_BUTTON_SUBMIT);?></button>
					</form>
				<?php } ?>
				</div>
			</div>
		</div>
	</div>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
