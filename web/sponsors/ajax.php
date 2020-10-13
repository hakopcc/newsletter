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
# * FILE: /sponsors/ajax.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include("../conf/loadconfig.inc.php");

header("Content-Type: text/html; charset=".EDIR_CHARSET, true);
header("Accept-Encoding: gzip, deflate");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check", false);
header("Pragma: no-cache");

sess_validateSession();

extract($_POST);

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
if ($ajax_type == "setItemAsViewed") {

    if ($type == "review") {
        $itemObj = new Review($id);
    } elseif ($type == "lead") {
        $itemObj = new Lead($id);
    }
    $itemObj->setString("new", "n");
    $itemObj->save();

} elseif ($ajax_type == "lead_reply") {

    extract($_POST);
    $isAjax = true;
    include(EDIRECTORY_ROOT."/includes/code/lead.php");

} elseif ($ajax_type == "load_dashboard") {

    $acctId = sess_getAccountIdFromSession();

    if ($item_id) {
        $itemObj = new $item_type($item_id);

        //Prepare code for dashboard
        include(INCLUDES_DIR."/code/member_dashboard.php");

        //Build dashboard
        include(INCLUDES_DIR."/views/view_member_dashboard.php");

        JavaScriptHandler::render();
    }

} elseif ($ajax_type == "review_reply") {

    if (string_strlen(trim($_POST["reply"])) > 0) {

        setting_get("review_approve", $review_approve);
        $responseapproved = 0;
        if (!$review_approve == "on") {
            $responseapproved = 1;
        }

        $reviewObj = new Review($_POST["idReview"]);
        $reviewObj->setString("response", trim($_POST["reply"]));
        $reviewObj->setString("responseapproved", $responseapproved);
        $reviewObj->save();

        /* send e-mail to sitemgr */
        setting_get("sitemgr_rate_email", $sitemgr_rate_email);
        $sitemgr_rate_emails = explode(",", $sitemgr_rate_email);

        $reviewObj = new Review($_POST["idReview"]);

        $domain = new Domain(SELECTED_DOMAIN_ID);
        $domain_url = DEFAULT_URL;
        $domain_url = str_replace($_SERVER["HTTP_HOST"], $domain->getstring("url"), $domain_url);

        // site manager warning message /////////////////////////////////////
        $emailSubject = "[".EDIRECTORY_TITLE."] ".system_showText(LANG_NOTIFY_NEWREPLY);
        $sitemgr_msg = system_showText(LANG_LABEL_SITE_MANAGER).",<br><br>"
            ."".system_showText(LANG_NOTIFY_NEWREPLY_1)." <strong>".$reviewObj->getString("review_title",
                true)."</strong> ".system_showText(LANG_NOTIFY_NEWREPLY_2).".</strong><br><br>"
            ."".system_showText(LANG_NOTIFY_NEWREPLY_3).":<br>"
            ."<a href=\"".$domain_url."/".SITEMGR_ALIAS."/activity/reviews-comments/\" target=\"_blank\">".$domain_url."/".SITEMGR_ALIAS."/activity/reviews-comments/</a><br><br>"
            ."</div>
                    </body>
                </html>";

        system_notifySitemgr($sitemgr_rate_emails, $emailSubject, $sitemgr_msg);

        /* */

        if (!$review_approve == "on") {
            if ($reviewObj->getString("item_type") == "listing") {
                $itemObj = new Listing($reviewObj->getNumber("item_id"));
                $contactObj = new Contact($itemObj->getNumber("account_id"));

                if ($emailNotificationObj = system_checkEmail(SYSTEM_APPROVE_REPLY)) {
                    $subject = $emailNotificationObj->getString("subject");
                    $subject = system_replaceEmailVariables($subject, $itemObj->getNumber("id"), "listing");
                    $subject = html_entity_decode($subject);

                    $body = $emailNotificationObj->getString("body");
                    $body = system_replaceEmailVariables($body, $itemObj->getNumber("id"), "listing");
                    $body = html_entity_decode($body);
                }
            }

            if ($emailNotificationObj && $itemObj && $contactObj) {
                SymfonyCore::getContainer()->get('core.mailer')
                    ->newMail($subject, $body, $emailNotificationObj->getString("content_type"))
                    ->setTo($contactObj->getString("email"))
                    ->setBcc($emailNotificationObj->getString("bcc"))
                    ->send();
            }
        }

        echo json_encode(["status" => "ok", "newReply" => trim($_POST["reply"])]);
    } else {
        echo json_encode(["status" => "error"]);
    }

} elseif ($ajax_type == "getunpaidItems") {

    include(INCLUDES_DIR."/code/billing.php");

    $toPayItems[] = "listings";
    $toPayItems[] = "events";
    $toPayItems[] = "banners";
    $toPayItems[] = "classifieds";
    $toPayItems[] = "articles";
    $toPayItems[] = "custominvoices";

    $countUnpaid = 0;

    foreach ($toPayItems as $toPayItem) {

        if ($bill_info[$toPayItem]) {

            if ($toPayItem == "custominvoices") {
                $countUnpaid++;
            } else {
                foreach ($bill_info[$toPayItem] as $id => $info) {
                    if ($info["needtocheckout"] == "y") {
                        $countUnpaid++;
                    }
                }
            }
        }

    }

    echo $countUnpaid;

} elseif ($ajax_type == "getFacebookImage") {

    $dbObj = db_getDBObject(DEFAULT_DB, true);
    $sql = " SELECT facebook_uid FROM Profile WHERE account_id = ".$_POST["id"];
    $result = $dbObj->query($sql);

    $row = mysqli_fetch_assoc($result);
    $uid = $row["facebook_uid"];

    $imgURL = "https://graph.facebook.com/".$uid."/picture?type=large";

    $ch = curl_init($imgURL);
    curl_setopt($ch, CURLOPT_URL, $imgURL);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_REFERER, $ref);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $data = curl_exec($ch);

    curl_close($ch);
    $filename = EDIRECTORY_ROOT."/custom/domain_".SELECTED_DOMAIN_ID."/tmp/temp.".time();

    $fp = fopen($filename, "w+");
    fwrite($fp, $data);
    fclose($fp);

    $info = getimagesize($filename);

    @unlink($filename);
    image_getNewDimension(PROFILE_MEMBERS_IMAGE_WIDTH, PROFILE_MEMBERS_IMAGE_HEIGHT, $info[0], $info[1], $newWidth,
        $newHeight);

    echo $imgURL."[FBIMG]".$newWidth."[FBIMG]".$newHeight;

}else if($ajax_type == "saveConsent") {
    if (setting_get('userconsent_status')=="on") {
        $container = SymfonyCore::getContainer();
        $consentService = $container->get('consent.service');
        $consentBody = [
            'payment' => 'on'
        ];
        $consent = $consentService->getConsent(sess_getAccountIdFromSession(),$consentBody);
        if($consent){
            $consentService->updateAccountConsent($consent);
        }else{
            $consentService->insertAccountConsent(sess_getAccountIdFromSession(),$consentBody);
        }
        exit;
    } else {
        exit;
    }
    exit;
}else if($ajax_type == 'sendEmailUpgrade'){
    $symfonyCore = SymfonyCore::getContainer();
    //send email
    $listing = new Listing($id);
    $account = new Contact(sess_getAccountIdFromSession());
    /* Send Mail for Admins */
    $name = setting_get('sitemgr_firstname');
    /*message bogy*/
    $message = system_showText(LANG_LABEL_HELLO);
    if(!empty($name)){
        $message .= " ".$name;
    }
    $message .= " ".system_showText(LANG_LABEL_GOOD_NEWS) ."! ". system_showText(LANG_LABEL_THE_SPONSOR) . ' '.$account->getString('first_name').((!empty($account->getString('last_name'))) ? ' '.$account->getString('last_name') : ''). ' ' . system_showText(LANG_LABEL_INTERESTED_UPGRADE) . " " .$listing->title . " ";
    $message .= "\n\n";
    $message .= system_showText(LANG_LABEL_WHAT_DO_NEXT). "\n\n" . system_showText(LANG_LABEL_EASY_PEASY);
    $message .= "\n\n";
    $message .= system_showText(LANG_LABEL_REGARDS);
    $emailMessage = str_replace(["\n\n","[link]","[linkclose]"],["<br><br>","<a href=".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/listing.php?id=".$id.">","</a>"],$message);
    /*subject*/
    $subject = system_showText(LANG_LABEL_LISTING_UPDATE);
    /*send message*/
    system_notifySitemgr([], $subject, $emailMessage);
    $leadObj = new Lead();
    $leadObj->setNumber("item_id", 0);
    $leadObj->setNumber("member_id", sess_getAccountIdFromSession());
    $leadObj->setString("type", "general");
    $leadObj->setString("first_name", $account->first_name);
    $leadObj->setString("last_name", $account->last_name);
    $leadObj->setString("email", $account->email);
    $leadObj->setString("phone", $account->phone);
    $leadObj->setString("subject", $subject);
    $leadObj->setString("message", $emailMessage);
    $leadObj->setString("new", "y");
    $leadObj->save();

}
/* ModStores Hooks */
HookFire("ajax_enhanced_lead", [
    "ajax_type"  => &$ajax_type,
    "listing_id" => &$listing_id
]);
