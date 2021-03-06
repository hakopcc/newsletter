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
# * FILE: /order_classified.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include("./conf/loadconfig.inc.php");

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSessionFront();

extract($_POST);
extract($_GET);

# ----------------------------------------------------------------------------------------------------
# VALIDATE FEATURE
# ----------------------------------------------------------------------------------------------------
if (CLASSIFIED_FEATURE != "on" || CUSTOM_CLASSIFIED_FEATURE != "on") {
    exit;
}
$classiLevelObj = new ClassifiedLevel();
$classiLevelValue = $classiLevelObj->getValues();
if (!in_array($level, $classiLevelValue)) {
    header("Location: " . DEFAULT_URL . "/" . ALIAS_ADVERTISE_URL_DIVISOR . "/");
    exit;
}

if (system_blockListingCreation()) {
    header("Location: " . DEFAULT_URL . "/" . ALIAS_CONTACTUS_URL_DIVISOR . "/");
    exit;
}

if (sess_getAccountIdFromSession()) {
    $accObj = new Account(sess_getAccountIdFromSession());
    $accObj->changeMemberStatus(true);
    header("Location: " . DEFAULT_URL . "/" . MEMBERS_ALIAS . "/" . CLASSIFIED_FEATURE_FOLDER . "/classified.php?level=$level");
    exit;
}

# ----------------------------------------------------------------------------------------------------
# SUBMIT
# ----------------------------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !system_isHoneypotFilled()) {

    $_POST["friendly_url"] = str_replace(".htm", "", $_POST["friendly_url"]);
    $_POST["friendly_url"] = str_replace(".html", "", $_POST["friendly_url"]);
    $_POST["friendly_url"] = trim($_POST["friendly_url"]);
    $_POST["friendly_url"] = system_denyInjections($_POST["friendly_url"]);

    if (!$_POST["friendly_url"]) {
        system_generateFriendlyURL($_POST["title"]);
    }

    $sqlFriendlyURL = "";
    $sqlFriendlyURL .= "SELECT friendly_url FROM Classified WHERE friendly_url = " . db_formatString($_POST["friendly_url"]) . " LIMIT 1";

    $dbMain = db_getDBObject(DEFAULT_DB, true);
    $dbObjFriendlyURL = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
    $resultFriendlyURL = $dbObjFriendlyURL->query($sqlFriendlyURL);
    if (mysqli_num_rows($resultFriendlyURL) > 0) {
        $_POST["friendly_url"] = $_POST["friendly_url"] . FRIENDLYURL_SEPARATOR . uniqid();
    }

    $friendly_url = $_POST["friendly_url"];
    $_POST["retype_password"] = $_POST["password"];

    $validate_account = validate_addAccount($_POST, $message_account);
    $validate_contact = validate_form("contact", $_POST, $message_contact);
    $tmpEMAIL = $_POST["email"];
    unset($_POST["email"]);
    $validate_classified = validate_form("classified", $_POST, $message_classified);
    $_POST["email"] = $tmpEMAIL;
    $validate_discount = is_valid_discount_code($_POST["discount_id"], "classified", $_POST["id"], $message_discount, $discount_error_num);

    if ($validate_account && $validate_contact && $validate_classified && $validate_discount) {

        $account = new Account($_POST);
        $account->save();

        $account->changeMemberStatus(true);

        $contact = new Contact($_POST);
        $contact->setNumber("account_id", $account->getNumber("id"));
        $contact->save();

        if (setting_get('userconsent_status') == "on") {
            //container to consent
            $container = SymfonyCore::getContainer();
            $consentService = $container->get('consent.service');
            $consentService->saveConsent($_POST);
        }
        $profileObj = new Profile($account->getNumber("id"));
        $profileObj->setNumber("account_id", $account->getNumber("id"));
        if (!$profileObj->getString("nickname")) {
            $profileObj->setString("nickname", $_POST["first_name"] . " " . $_POST["last_name"]);
        }
        $profileObj->Save();

        $accDomain = new Account_Domain($account->getNumber("id"), SELECTED_DOMAIN_ID);
        $accDomain->Save();
        $accDomain->saveOnDomain($account->getNumber("id"), $account, $contact, $profileObj);

        if ($_POST["newsletter"]) {
            $_POST["name"] = $_POST["first_name"] . " " . $_POST["last_name"];
            $_POST["type"] = "sponsor";
            arcamailer_addSubscriber($_POST, $success, $account->getNumber("id"));
        }

        unset($_POST["email"]);
        unset($_POST["phone"]);
        unset($_POST["address"]);
        unset($_POST["address2"]);
        $classified = new Classified($_POST);
        $classified->setNumber("account_id", $account->getNumber("id"));
        $status = new ItemStatus();
        $classified->setString("status", $status->getDefaultStatus());
        $classified->setDate("renewal_date", "00/00/0000");
        $classified->Save();
        $return_categories_array[] = $_POST["cat_1_id"];
        $classified->setCategories($return_categories_array);

        /**************************************************************************************************/
        /*                                                                                                */
        /* E-mail notify                                                                                  */
        /*                                                                                                */
        /**************************************************************************************************/
        setting_get("sitemgr_send_email", $sitemgr_send_email);
        setting_get("sitemgr_email", $sitemgr_email);
        $sitemgr_emails = explode(",", $sitemgr_email);
        if ($sitemgr_emails[0]) $sitemgr_email = $sitemgr_emails[0];
        setting_get("sitemgr_account_email", $sitemgr_account_email);
        $sitemgr_account_emails = explode(",", $sitemgr_account_email);
        setting_get("sitemgr_classified_email", $sitemgr_classified_email);
        $sitemgr_classified_emails = explode(",", $sitemgr_classified_email);

        // sending e-mail to user //////////////////////////////////////////////////////////////////////////
        if ($emailNotificationObj = system_checkEmail(SYSTEM_CLASSIFIED_SIGNUP)) {
            $linkActivation = system_getAccountActivationLink($account->getNumber("id"));
            $subject = $emailNotificationObj->getString("subject");
            $body = $emailNotificationObj->getString("body");
            $login_info = trim(system_showText(LANG_LABEL_USERNAME)) . ": " . $_POST["username"];
            $login_info .= ($emailNotificationObj->getString("content_type") == "text/html" ? "<br />" : "\n");
            $login_info .= trim(system_showText(LANG_LABEL_PASSWORD)) . ": " . $_POST["password"];
            $body = str_replace("ACCOUNT_LOGIN_INFORMATION", $login_info, $body);
            $body = system_replaceEmailVariables($body, $classified->getNumber('id'), 'classified');
            $body = str_replace("LINK_ACTIVATE_ACCOUNT", $linkActivation, $body);
            $subject = system_replaceEmailVariables($subject, $classified->getNumber('id'), 'classified');
            $body = html_entity_decode($body);
            $subject = html_entity_decode($subject);
            $email = filter_var($contact->getString("email"), FILTER_VALIDATE_EMAIL);
            if ($email) {
                SymfonyCore::getContainer()->get('core.mailer')
                    ->newMail($subject, $body, $emailNotificationObj->getString("content_type"))
                    ->setTo($email)
                    ->setBcc($emailNotificationObj->getString("bcc"))
                    ->send();
            }
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////

            // site manager warning message /////////////////////////////////////
            $emailSubject = "[".EDIRECTORY_TITLE."] ".system_showText(LANG_NOTIFY_SIGNUPCLASSIFIED);
            $sitemgr_msg = system_showText(LANG_LABEL_SITE_MANAGER).",<br /><br />".system_showText(LANG_NOTIFY_SIGNUPCLASSIFIED_1)."<br /><br />".system_showText(LANG_LABEL_ACCOUNT).":<br /><br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_USERNAME2).": </b>".system_showAccountUserName($account->getString("username"))."<br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_FIRST_NAME).": </b>".$contact->getString("first_name")."<br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_LAST_NAME).": </b>".$contact->getString("last_name")."<br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_COMPANY).": </b>".$contact->getString("company")."<br />";
            if(setting_get('locations_enable')!=="off"){
                $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_ADDRESS).": </b>".$contact->getString("address")." ".$contact->getString("address2")."<br />";
                $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_CITY).": </b>".$contact->getString("city")."<br />";
                $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_STATE).": </b>".$contact->getString("state")."<br />";
                $sitemgr_msg .= "<b>".ucfirst(system_showText(ZIPCODE_LABEL)).": </b>".$contact->getString("zip")."<br />";
                $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_COUNTRY).": </b>".$contact->getString("country")."<br />";
            }
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_PHONE).": </b>".$contact->getString("phone")."<br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_EMAIL).": </b>".$contact->getString("email")."<br />";
            $sitemgr_msg .= "<br /><a href=\"".DEFAULT_URL."/".SITEMGR_ALIAS."/account/sponsor/sponsor.php?id=".$account->getNumber("id")."\" target=\"_blank\">".DEFAULT_URL."/".SITEMGR_ALIAS."/account/sponsor/sponsor.php?id=".$account->getNumber("id")."</a><br /><br />";
            $sitemgr_msg .= "".system_showText(LANG_CLASSIFIED_FEATURE_NAME).":<br /><br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_TITLE).": </b>".$classified->getString("title")."<br />";
            $sitemgr_msg .= "<br /><a href=\"".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".CLASSIFIED_FEATURE_FOLDER."/classified.php?id=".$classified->getNumber("id")."\" target=\"_blank\">".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".CLASSIFIED_FEATURE_FOLDER."/classified.php?id=".$classified->getNumber("id")."</a><br /><br />";

        setting_get("new_classified_email", $new_classified_email);

        if ($new_classified_email) {
            system_notifySitemgr($sitemgr_account_emails, $emailSubject, $sitemgr_msg, true, true, $sitemgr_classified_emails);
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////

        if ($checkout) $payment_method = "checkout";

        sess_registerAccountInSession($account->getString("username"));
        setcookie("username_members_members", $account->getString("username"), time() + 60 * 60 * 24 * 30, "" . EDIRECTORY_FOLDER . "/");
        setcookie("automatic_login_members", "false", time() + 60 * 60 * 24 * 30, "" . EDIRECTORY_FOLDER . "/");


        $host = string_strtoupper(str_replace("www.", "", $_SERVER["HTTP_HOST"]));

        setcookie($host . "_DOMAIN_ID_MEMBERS", SELECTED_DOMAIN_ID, time() + 60 * 60 * 24 * 30, "" . EDIRECTORY_FOLDER . "/");

        //Check if a package was bought
        $queryPackage = "";

        if ($_POST["using_package"] == "y") {

            //Check if exists package
            $packageObj = new Package();
            $array_package_offers = $packageObj->getPackagesByDomainID(SELECTED_DOMAIN_ID, "classified", $classified->level);

            if ((is_array($array_package_offers)) and (count($array_package_offers) > 0) and $array_package_offers[0]) {

                unset($array_info_package);
                $array_info_package["item_type"] = "classified";
                $array_info_package["item_id"] = $classified->getNumber("id");
                $array_info_package["item_name"] = $classified->getString("title");
                $array_info_package["item_friendly_ur"] = $classified->getString("friendly_url");
                $array_info_package["package_id"][0] = $aux_package_id;
                $package_id = package_buying_package($array_info_package, true);
                $queryPackage = "&ispackage=true&package_id=$package_id";

            }
        }

        setting_get("classified_approve_free", $classified_approve_free);

        if ($payment_method == "checkout" && !$classified_approve_free) {
            $classified->setString("status", "A");
            $classified->save();
        }

        if ($payment_method == "checkout") {
            $redirectURL = DEFAULT_URL . "/" . MEMBERS_ALIAS . "/" . CLASSIFIED_FEATURE_FOLDER . "/classified.php?id=" . $classified->getNumber("id") . "&process=signup";
        } elseif ($payment_method == "invoice") {
            $redirectURL = DEFAULT_URL . "/" . MEMBERS_ALIAS . "/signup/invoice.php" . ($queryPackage ? "?" . $queryPackage : "");
        } else {
            $redirectURL = DEFAULT_URL . "/" . MEMBERS_ALIAS . "/signup/payment.php?payment_method=" . $payment_method . $queryPackage;
        }

        /* ModStores Hooks */
        HookFire("orderclassified_before_redirect", [
            "account" => &$account,
            "contact" => &$contact,
            "profileObj" => &$profileObj,
            "accDomain" => &$accDomain,
            "classified" => &$classified
        ]);

        header("Location: " . $redirectURL);
        exit;
    } else {

        if (($pos = string_strrpos($_POST["friendly_url"], FRIENDLYURL_SEPARATOR)) !== false) {
            $_POST["friendly_url"] = string_substr($_POST["friendly_url"], 0, $pos);
        }

        // removing slashes added if required
        $_POST = format_magicQuotes($_POST);
        $_GET = format_magicQuotes($_GET);
        extract($_POST);
        extract($_GET);

    }

}

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
$classifiedLevelObj = new ClassifiedLevel();
$levelValue = $classifiedLevelObj->getValues();

$formloginaction = DEFAULT_URL . "/" . MEMBERS_ALIAS . "/login.php?destiny=" . EDIRECTORY_FOLDER . "/" . MEMBERS_ALIAS . "/" . CLASSIFIED_FEATURE_FOLDER . "/classified.php";

/*
 * TAX SECTION
 */
setting_get("payment_tax_status", $payment_tax_status);
setting_get("payment_tax_value", $payment_tax_value);
setting_get("payment_tax_label", $payment_tax_label);

unset($googleEnabled, $facebookEnabled);

setting_get("foreignaccount_google", $foreignaccount_google);
if ($foreignaccount_google == "on") {
    $googleEnabled = true;
}

if (FACEBOOK_APP_ENABLED == "on") {
    $facebookEnabled = true;
}

$unique_id = system_generatePassword();

$checkoutpayment_class = "isHidden";
$checkoutfree_class = "isHidden";

$labelName = str_replace("[level]", $classifiedLevelObj->showLevel($level), LANG_ADVERTISE_CLASSIFIEDLEVEL);

$advertiseItem = "classified";

//Check if exists package
$packageObj = new Package();
$array_package_offers = $packageObj->getPackagesByDomainID(SELECTED_DOMAIN_ID, "classified", $level);
$hasPackage = false;
if ((is_array($array_package_offers)) && (count($array_package_offers) > 0) && $array_package_offers[0]) {
    $hasPackage = true;
}

# ----------------------------------------------------------------------------------------------------
# HEADER
# ----------------------------------------------------------------------------------------------------
$headertag_title = $headertagtitle;
$headertag_description = $headertagdescription;
$headertag_keywords = $headertagkeywords;
include(EDIRECTORY_ROOT . "/frontend/header.php");

?>
    <section class="top-search">

        <? include(EDIRECTORY_ROOT . "/frontend/coverimage.php"); ?>

        <div class="well well-translucid">
            <div class="container">
                <br>
                <h1><?= system_showText(LANG_MENU_ADVERTISE); ?></h1>
                <br>
            </div>
        </div>
    </section>

    <main>
        <section class="block">
            <div class="container">
                <div class="well">
                    <?php include(INCLUDES_DIR . "/forms/form_advertise.php"); ?>
                </div>
            </div>
        </section>
    </main>

<?php
# ----------------------------------------------------------------------------------------------------
# FOOTER
# ----------------------------------------------------------------------------------------------------
include(EDIRECTORY_ROOT . "/frontend/footer.php");
