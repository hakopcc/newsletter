<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /includes/forms/form-emailconfiguration.php
    # ----------------------------------------------------------------------------------------------------
?>

<form role="form" name="adminemail" id="adminemail" action="<?= system_getFormAction($_SERVER['PHP_SELF']) ?>" method="post" onsubmit="return emailConfigFunction.submitForm(event)">
    <input type="hidden" name="ajaxVerify" id="ajaxVerify" value="1"/>

    <div id="form-smtp" class="panel panel-default">
        <div class="panel-heading"><?= system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_SMTPSERVERINFORMATION) ?></div>
        <div class="panel-body form-horizontal">
            <div class="form-group">
                <label for="host" class="control-label col-sm-3"><?= system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_LABEL_SERVER) ?></label>
                <div class="col-sm-5">
                    <input type="text" class="form-control" name="emailconf_host" id="host" value="<?= $domainConfig['mailer_host'] ?>">
                </div>
                <label for="port" class="control-label col-sm-2"><?= system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_LABEL_PORT) ?></label>
                <div class="col-sm-2">
                    <input type="text" class="form-control" name="emailconf_port" id="port" value="<?= $domainConfig['mailer_port'] ?>">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <div class="radio">
                        <label for="auth1">
                            <input type="radio" name="emailconf_auth" id="auth1" value="plain" <?= $domainConfig['mailer_auth_mode'] === 'plain' ? 'checked=checked' : '' ?> onclick="emailConfigFunction.switchAuth(this.value)"><?= system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_SERVERREQUIRESAUTHENTICATION1) ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-7">
                    <div class="radio">
                        <label for="auth2" class="row col-sm-12">
                            <div class="col-sm-10">
                                <input type="radio" name="emailconf_auth" id="auth2" value="login" <?= $domainConfig['mailer_auth_mode'] === 'login' ? 'checked=checked' : '' ?> onclick="emailConfigFunction.switchAuth(this.value)"><?= system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_SERVERREQUIRESAUTHENTICATION2) ?>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="selectize col-sm-2">
                    <select name="emailconf_protocol" id="protocol" onchange="emailConfigFunction.switchPorts(this.value)">
                        <option value="ssl" <?= $domainConfig['mailer_encryption'] === 'ssl' ? 'selected' : '' ?> >SSL</option>
                        <option value="tls" <?= $domainConfig['mailer_encryption'] === 'tls' ? 'selected' : '' ?> >TLS</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <div class="radio">
                        <label for="auth3">
                            <input type="radio" name="emailconf_auth" id="auth3" value="noauth" <?= $domainConfig['mailer_auth_mode'] === null ? 'checked=checked' : '' ?> onclick="emailConfigFunction.switchAuth(this.value)"><?= system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_SERVERREQUIRESAUTHENTICATION3) ?>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="email" class="control-label col-sm-3">
                    <?= system_showText(LANG_SITEMGR_LABEL_EMAILADDRESS) ?>
                </label>
                <div class="col-sm-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="emailconf_email" id="email" value="<?= $domainConfig['mailer_sender'] ?>" onkeyup="emailConfigFunction.verifyEmail(this.value)" onkeypress="emailConfigFunction.verifyEmail(this.value)">
                        <span class="input-group-addon" id="email_status"><i class="fa fa-times-circle-o"></i></span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="username" class="control-label col-sm-3">
                    <?= system_showText(LANG_SITEMGR_USERNAME) ?>
                </label>
                <div class="col-sm-5">
                    <input type="text" class="form-control" name="emailconf_username" id="username" value="<?= $domainConfig['mailer_user'] ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="control-label col-sm-3">
                    <?= system_showText(LANG_SITEMGR_LABEL_PASSWORD) ?>
                </label>
                <div class="col-sm-5">
                    <input class="form-control" type="password" name="emailconf_password" id="password">
                </div>
            </div>
        </div>
        <?php if (!$step) { ?>
            <div class="panel-footer">
                <button type="submit" name="bt_submit" id="bt_submit" value="Submit" class="btn btn-primary" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?= system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_SAVECONFIGURATION);?></button>
            </div>
        <?php } ?>
    </div>
</form>
