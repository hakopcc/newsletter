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
    # * FILE: /includes/form/form_addaccount.php
    # ----------------------------------------------------------------------------------------------------

    if ((string_strlen(trim($message_account)) > 0) || (string_strlen(trim($message_contact)) > 0) ) { ?>
        <!--message errors-->
        <p class="alert alert-warning">
            <?php if (string_strlen(trim($message_contact)) > 0) { ?>
                <?=$message_contact?>
            <?php } ?>
            <?php if ((string_strlen(trim($message_contact)) > 0) && (string_strlen(trim($message_account)) > 0)) { ?>
                <br />
            <?php } ?>
            <?php if (string_strlen(trim($message_account)) > 0) { ?>
                <?=$message_account?>
            <?php } ?>
        </p>
        <!--message errors-->
    <?php } ?>

    <?=system_getHoneypotInput();?>
    <div class="form-box">
        <input class="input custom-input-size" type="text" name="first_name" id="first_name" value="<?=$first_name?>" placeholder="<?=system_showText(LANG_LABEL_FIRST_NAME);?>" required/>
        <input class="input custom-input-size" type="text" name="last_name" id="last_name" value="<?=$last_name?>" placeholder="<?=system_showText(LANG_LABEL_LAST_NAME);?>" required/>
        <input class="input custom-input-size" type="email" name="username" id="username<?=($claimSection ? "_claim" : "")?>" value="<?=$username?>" maxlength="<?=USERNAME_MAX_LEN?>" onblur="populateField(this.value,'email');" placeholder="<?=system_showText(LANG_LABEL_USERNAME);?>" required/>
        <input type="hidden" name="email" id="email" value="<?=$email?>" />
        <input class="input custom-input-size" placeholder="<?=system_showText(LANG_LABEL_PASSWORD);?>" id="password<?=($claimSection ? "_claim" : "")?>" type="password" name="password" maxlength="<?=PASSWORD_MAX_LEN?>" required/>

        <?php if((setting_get("userconsent_status") == "on") || ($showNewsletter)) { ?>
            <div class="consents-block">
                <?php if(setting_get("userconsent_status") == "on") { ?>
                    <!-- check box accept the information-->
                    <label for="termsService-add-account" class="form-remember">
                        <input type="checkbox" id="termsService-add-account" name="termsService" required>
                        <?=sprintf(LANG_SIGNUP_TERMS,
                            "<a rel=\"nofollow\" href=\"".DEFAULT_URL."/".ALIAS_TERMS_URL_DIVISOR."\" target=\"_blank\">",
                            "</a>",
                            "<a rel=\"nofollow\" href=\"".DEFAULT_URL."/".ALIAS_PRIVACY_URL_DIVISOR."\" target=\"_blank\">",
                            "</a>"
                        );?>
                    </label>
                    <label for="signup-consent-add-account" class="form-remember">
                        <input type="checkbox" name="signup" id="signup-consent-add-account" required>
                        <?=sprintf(LANG_CONSENT_TERMS);?>
                    </label>
                <?php } ?>
                <?php if($showNewsletter) { ?>
                    <label for="newsletter" class="form-remember">
                        <input type="checkbox" name="newsletter" id="newsletter" value="y" <?=$newsletter ? "checked" : ""?> />
                        <?=$signupLabel?>
                    </label>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <?php echo(new \reCAPTCHA())->render(); ?>

    <div class="form-button">
        <?php if ($advertise_section) { ?>
            <?php if (PAYMENT_FEATURE == "on" && ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on"))) { ?>
                <button class="button button-bg is-primary" id="check_out_payment_2" type="submit" name="continue" value=""><?=system_showText(LANG_BUTTON_SUBMIT)?></button>
            <?php } ?>
            <button class="button button-bg is-primary" id="check_out_free_2" type="submit" name="checkout" value="<?=system_showText(LANG_BUTTON_CONTINUE)?>"><?=system_showText(LANG_BUTTON_SUBMIT)?></button>
        <?php } else { ?>
            <button class="button button-bg is-primary" type="submit" value="Submit"><?=system_showText(LANG_BUTTON_SIGNUP)?></button>
        <?php } ?>
    </div>

    <?php
    /* ModStores Hooks */
    HookFire("formsignup_after_render_newsletter");
    ?>

    <?php if(setting_get("userconsent_status") != "on") { ?>
        <small class="privacy-policy"><?=sprintf(LANG_SIGNUP_TERMS,
                "<a rel=\"nofollow\" href=\"".DEFAULT_URL."/".ALIAS_TERMS_URL_DIVISOR."\" target=\"_blank\">",
                "</a>",
                "<a rel=\"nofollow\" href=\"".DEFAULT_URL."/".ALIAS_PRIVACY_URL_DIVISOR."\" target=\"_blank\">",
                "</a>"
            );?>
        </small>
    <?php } ?>
