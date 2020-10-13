<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/forms/form-manager.php
	# ----------------------------------------------------------------------------------------------------

    if (string_strpos($_SERVER['PHP_SELF'], '/' .SITEMGR_ALIAS. '/account/myaccount.php') === false && sess_getSMIdFromSession() != $id || !sess_getSMIdFromSession()) {
        $myAdminAccount = false;
    } else {
        $myAdminAccount = true;
    }

    if (string_strpos($_SERVER['PHP_SELF'], '/' .SITEMGR_ALIAS. '/account/myaccount.php') !== false) {
        $isSitemgrAccount = true;
    } else {
        $isSitemgrAccount = false;
    }
?>
    <div class="col-lg-8 col-sm-8 col-xs-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <?php if ($isSitemgrAccount) { ?>
                        <div class="col-md-3">
                            <div class="row account-picture-container">
                                <div class="account-picture" id="smaccount_image">
                                    <?=$imageTag?>
                                </div>
                                <input type="file" name="image" id="image" size="1" class="file-noinput" onchange="uploadSMAccountPicture();">
                                <button type="button" id="remove_image" class="btn btn-sm btn-default" <?= $noImage ? 'style="display: none;"' : '' ?> onclick="removeSMAccountPicture()"><?=LANG_LABEL_PROFILE_REMOVEPHOTO?></button>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="<?=$isSitemgrAccount ? 'col-md-9' : 'col-md-12';?>">
                        <div class="form-group row">
                            <div class="col-sm-<?=($myAdminAccount ? '12' : '6')?>">
                                <label for="username"><?=system_showText(LANG_SITEMGR_LABEL_USERNAME)?></label>
                                <input id="username" type="email" name="username" value="<?=$username?>" class="form-control" onblur="populateField(this.value, 'email');"/>
                                <input type="text" id="email" name="email" value="<?=$email?>" style="display:none;" />
                            </div>
                            <?php if (!$myAdminAccount) { ?>
                                <div class="col-sm-6">
                                    <label for="status"><?=system_showText(LANG_LABEL_STATUS)?></label>
                                    <select class="form-control status-select" name="status" id="status">
                                        <option value="1" <?=($active == 'y' ? 'selected' : '')?>><?=system_showText(LANG_SITEMGR_LABEL_ENABLED);?></option>
                                        <option value="2" <?=($active == 'n' ? 'selected' : '')?>><?=system_showText(LANG_SITEMGR_LABEL_DISABLED);?></option>
                                    </select>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label for="first_name"><?=system_showText(LANG_SITEMGR_LABEL_FIRSTNAME)?></label>
                                <input type="text" name="first_name" id="first_name" value="<?=$first_name?>" class="form-control" />
                            </div>
                            <div class="col-sm-4">
                                <label for="last_name"><?=system_showText(LANG_SITEMGR_LABEL_LASTNAME)?></label>
                                <input type="text" name="last_name" id="last_name" value="<?=$last_name?>" class="form-control" />
                            </div>
                            <div class="col-sm-4">
                                <label for="phone"><?=system_showText(LANG_SITEMGR_LABEL_PHONE)?> <small class="small text-muted"><?=system_showText(LANG_SITEMGR_LABEL_SPAN_OPTIONAL)?></small></label>
                                <input type="tel" name="phone" id="phone" value="<?=$phone?>" class="form-control" />
                            </div>
                        </div>

                        <div class="form-group row">
                            <?php if (string_strpos($_SERVER['PHP_SELF'], '/' .SITEMGR_ALIAS. '/account/myaccount.php') !== false) { ?>
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
                            <?php } else { ?>
                                <div class="col-xs-6">
                                    <label for="password"><?=system_showText(LANG_SITEMGR_LABEL_PASSWORD)?></label>
                                    <input type="password" name="password" class="form-control" id="password">
                                </div>
                                <div class="col-xs-6">
                                    <label for="retpassword"><?=system_showText(LANG_SITEMGR_LABEL_RETYPEPASSWORD)?></label>
                                    <input type="password" name="retype_password" class="form-control" id="retpassword">
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <?php if (string_strpos($_SERVER['PHP_SELF'], '/' .SITEMGR_ALIAS. '/account/myaccount.php') === false) { ?>
                    <hr>

                    <?php
                        unset($account_permission);
                        if ($_POST['permission']) {
                            $account_permission = $_POST['permission'];
                        } elseif ($permission) {
                            $account_permission = $permission;
                        }

                        echo permission_getSMTable($account_permission, $myAdminAccount);
                    ?>
                <?php } ?>
            </div>
            <?php if (string_strpos($_SERVER['PHP_SELF'], '/' .SITEMGR_ALIAS. '/account/manager/manager.php') === false) { ?>
                <div class="panel-footer">
                    <button type="button" onclick="document.smaccount.submit();" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_MSG_SAVE_CHANGES);?></button>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php if (string_strpos($_SERVER['PHP_SELF'], '/' .SITEMGR_ALIAS. '/account/myaccount.php') === false) { ?>
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
    <?php } ?>
