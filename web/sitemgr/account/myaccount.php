<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/account/myaccount.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include('../../conf/loadconfig.inc.php');

    # ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();

    mixpanel_track('Accessed My Account section');

    # ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

	$success = false;

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($_POST['ajax'] || $_GET['ajax']) {
            if ($_POST['action'] === 'removePhoto') {
                if (!empty(sess_getSMIdFromSession())) {
                    $smAccountObj = new SMAccount(sess_getSMIdFromSession());

                    $idm = $smAccountObj->getNumber('image_id');
                } elseif (sess_isSitemgrLogged()) {
                    setting_get('sitemgr_imageid', $idm);
                }

                if(!empty($idm)) {
                    $image = new Image($idm, true);
                    if ($image) {
                        $image->Delete();
                    }

                    if (!empty(sess_getSMIdFromSession())) {
                        $smAccountObj->setNumber('image_id', 0);

                        $smAccountObj->Save();
                    } elseif (sess_isSitemgrLogged()) {
                        setting_set('sitemgr_imageid', '');
                    }
                }
            } elseif ($_GET['action'] === 'uploadPhoto') {
                $error = false;
                if (file_exists($_FILES['image']['tmp_name'])) {
                    if (!image_upload_check($_FILES['image']['tmp_name'])) {
                        $error = true;
                        $return = system_showText(LANG_MSG_INVALID_IMAGE_TYPE). '<br />';
                    } else {
                        $imageArray = image_uploadForItem($_FILES['image']['tmp_name'], sess_getSMIdFromSession(). '_smaccount_', 200, 200, true);
                        if ($imageArray['success']) {
                            if (!empty(sess_getSMIdFromSession())) {
                                $smAccountObj = new SMAccount(sess_getSMIdFromSession());
                                $oldImage = $smAccountObj->getNumber('image_id');
                            } elseif (sess_isSitemgrLogged()) {
                                setting_get('sitemgr_imageid', $oldImage);
                            }

                            if ($oldImage) {
                                $imageAux = new Image($oldImage, true);
                                if ($imageAux) {
                                    $imageAux->Delete();
                                }
                            }

                            if (!empty(sess_getSMIdFromSession())) {
                                $smAccountObj->setNumber('image_id', $imageArray['image_id']);
                                $smAccountObj->Save();
                            } elseif (sess_isSitemgrLogged()) {
                                setting_set('sitemgr_imageid', $imageArray['image_id']);
                            }

                            $imageObj = new Image($imageArray['image_id'], true);
                            $return = $imageObj->getTag(true, SITEMGR_ACCOUNT_IMAGE_WIDTH, SITEMGR_ACCOUNT_IMAGE_HEIGHT, '', '', 'Site Manager Image');
                        } else {
                            $error = true;
                            $return = system_showText(LANG_LABEL_ERRORLOGIN);
                        }
                    }
                } else {
                    $error = true;
                    $return = system_showText(LANG_MSG_MAX_FILE_SIZE . ': ' . UPLOAD_MAX_SIZE . 'MB.');
                }
                echo ($error ? 'error' : 'ok') . '||' . $return;
            }
            exit;
        }

		if ($action === 'smaccount') {
			if ((string_strlen($_POST['password']))||(string_strlen($_POST['retype_password']))) {
				$validate_sitemgrcurrentpassword = validate_sitemgrCurrentPassword($_POST, $_SESSION['SESS_SM_ID'], $message_smpassword);
			} else {
				$validate_sitemgrcurrentpassword = true;
			}
			if (validate_smaccount($_POST, $message_smaccount) && $validate_sitemgrcurrentpassword) {
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
				$_POST['permission'] = $permission;

				$smaccount = new SMAccount($_POST['id']);
				$smaccount->makeFromRow($_POST);
				$smaccount->save();

				if ($password) {
					$smaccount->setString('password', $password);
					$smaccount->updatePassword();
				}

				$success = true;
				$message_smaccount = system_showText(LANG_SITEMGR_MANAGEACCOUNT_SUCCESSUPDATED) ;
			}
	    }

		if ($action === 'changelogin' && !DEMO_LIVE_MODE) {
			$validate_sitemgrcurrentpassword = true;
			if ((string_strlen($password))||(string_strlen($retype_password))) {
				setting_get('sitemgr_password', $sitemgr_password);
				if ($sitemgr_password != md5($current_password)) {
					$validate_sitemgrcurrentpassword = false;
					$error_currentpassword = system_showText(LANG_SITEMGR_MSGERROR_CURRENTPASSWORDINCORRECT);
				}
			}

			if ($validate_sitemgrcurrentpassword && validate_SM_changelogin($_POST, $message_changelogin)) {
				if ($username) {
					setting_get('sitemgr_username', $sm_username);
					if ($username != $sm_username) {
						setting_set('sitemgr_username', $username);
						$actions[] = '&#149;&nbsp;'.system_showText(LANG_SITEMGR_MANAGEACCOUNT_USERNAMEWASCHANGED);
					}
				}

                if ($first_name) {
                    setting_get('sitemgr_firstname', $sm_first_name);
                    if ($first_name != $sm_first_name) {
                        setting_set('sitemgr_firstname', $first_name);
                        $actions[] = '&#149;&nbsp;'.system_showText(LANG_SITEMGR_MANAGEACCOUNT_FIRSTNAMEWASCHANGED);
                    }
                }

                if ($last_name) {
                    setting_get('sitemgr_lastname', $sm_last_name);
                    if ($last_name != $sm_last_name) {
                        setting_set('sitemgr_lastname', $last_name);
                        $actions[] = '&#149;&nbsp;'.system_showText(LANG_SITEMGR_MANAGEACCOUNT_LASTNAMEWASCHANGED);
                    }
                }

				if ($password) {
					$pwDBObj = db_getDBObject(DEFAULT_DB, true);
					$sql = 'UPDATE Setting SET value = ' .db_formatString(md5($password))." WHERE name = 'sitemgr_password'";
					$pwDBObj->query($sql);
					$actions[] = '&#149;&nbsp;' .system_showText(LANG_SITEMGR_MANAGEACCOUNT_PASSWORDWASCHANGED);
				}

				if ($actions) {
					$success = true;
					$message_changelogin .= implode('<br />', $actions);
				}
			}
		}
	}

    # ----------------------------------------------------------------------------------------------------
	# FORMS DEFINES
	# ----------------------------------------------------------------------------------------------------
	if ($_SESSION['SESS_SM_ID']) {
		$smaccount = new SMAccount($_SESSION['SESS_SM_ID']);
		$smaccount->extract();
	} else {
		setting_get('sitemgr_username', $username);
		if(!empty(setting_get('sitemgr_firstname', $first_name))) {
            setting_get('sitemgr_firstname', $first_name);
        } else {
            setting_get('install_name', $first_name);
        }
		setting_get('sitemgr_lastname', $last_name);
	}

    $noImage = false;

    if($smaccount !== null && !empty($smaccount->getNumber('image_id')) && $smaccount->getNumber('image_id') !== 'NULL') {
        $imageObj = new Image($smaccount->getNumber('image_id'), true);
        $imageTag = $imageObj->getTag(true, SITEMGR_ACCOUNT_IMAGE_WIDTH, SITEMGR_ACCOUNT_IMAGE_HEIGHT);
    } elseif ($smaccount === null && sess_isSitemgrLogged() && !empty(setting_get('sitemgr_imageid', $imageId))) {
        $imageObj = new Image($imageId, true);
        $imageTag = $imageObj->getTag(true, SITEMGR_ACCOUNT_IMAGE_WIDTH, SITEMGR_ACCOUNT_IMAGE_HEIGHT);
    } else {
        $imageTag = '<img class="user-picture" width="100" height="100" src="'.DEFAULT_URL.'/assets/images/user-image.png">';
        $noImage = true;
    }

    $readonly = '';
    if (DEMO_LIVE_MODE) {
        $readonly = 'readonly';
    }

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
                    require SM_EDIRECTORY_ROOT.'/registration.php';
                    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
                ?>

                <section class="section-heading">
                    <div class="section-heading-content">
                        <h1 class="section-heading-title"><?=system_showText(LANG_SITEMGR_MENU_MYACCOUNT);?></h1>
                    </div>
                    <div class="section-heading-actions">
                        <?php if ($_SESSION['SESS_SM_ID']) { ?>
                            <button type="submit" onclick="document.smaccount.submit();" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_MSG_SAVE_CHANGES);?></button>
                        <?php } else { ?>
                            <button type="button" onclick="document.smaccount.submit();" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_MSG_SAVE_CHANGES);?></button>
                        <?php } ?>
                    </div>
                </section>

                <form role="form" name="smaccount" id="smaccount" action="<?=system_getFormAction($_SERVER['PHP_SELF'])?>" method="post" enctype="multipart/form-data">
                    <section class="section-form">
                        <?php if ($_SESSION['SESS_SM_ID']) { ?>
                            <input type="hidden" name="action" value="smaccount">
                            <input type="hidden" name="id" value="<?=$_SESSION['SESS_SM_ID']?>" />
                            <input type="hidden" name="status" value="<?=($active == 'y' ? '1' : '0')?>">
                            <div class="col-lg-8 col-sm-8 col-xs-12">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="row account-picture-container">
                                                    <div class="account-picture" id="smaccount_image">
                                                        <?=$imageTag?>
                                                    </div>
                                                    <input type="file" name="image" id="image" size="1" class="file-noinput" onchange="uploadSMAccountPicture();">
                                                    <button type="button" id="remove_image" class="btn btn-sm btn-default" <?= $noImage ? 'style="display: none;"' : '' ?> onclick="removeSMAccountPicture()"><?=LANG_LABEL_PROFILE_REMOVEPHOTO?></button>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
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
                                                    <div class="col-sm-4">
                                                        <label for="cpassword"><?=system_showText(LANG_SITEMGR_LABEL_CURRENTPASSWORD)?></label>
                                                        <input type="password" name="current_password" class="form-control" id="cpassword">
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <label for="password"><?=system_showText(LANG_SITEMGR_LABEL_PASSWORD)?></label>
                                                        <input type="password" name="password" class="form-control" id="password">
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <label for="retpassword"><?=system_showText(LANG_SITEMGR_LABEL_RETYPEPASSWORD)?></label>
                                                        <input type="password" name="retype_password" class="form-control" id="retpassword">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name="action" value="changelogin">
                            <div class="col-lg-8 col-sm-8 col-xs-12">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <?php if (empty($readonly)) { ?>
                                                    <div class="row account-picture-container">
                                                        <div class="account-picture" id="smaccount_image">
                                                            <?=$imageTag?>
                                                        </div>
                                                        <input type="file" name="image" id="image" size="1" class="file-noinput" onchange="uploadSMAccountPicture();">
                                                        <button type="button" id="remove_image" class="btn btn-sm btn-default" <?= $noImage ? 'style="display: none;"' : '' ?> onclick="removeSMAccountPicture()"><?=LANG_LABEL_PROFILE_REMOVEPHOTO?></button>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="col-md-9">
                                                <?php include(INCLUDES_DIR. '/forms/form-changelogin.php'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </section>
                </form>
            </div>
        </div>
    </main>
<?php

    $customJS = SM_EDIRECTORY_ROOT. '/assets/custom-js/smaccount.php';

	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT. '/layout/footer.php');
