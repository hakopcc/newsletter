#!/usr/bin/php -q
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
# * FILE: /cron/renewal_reminder.php
# ----------------------------------------------------------------------------------------------------

////////////////////////////////////////////////////////////////////////////////////////////////////
define("BLOCK", 1000);
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
ini_set("html_errors", false);
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
define("EDIRECTORY_ROOT", __DIR__ . "/..");
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$_inCron = true;
include_once(EDIRECTORY_ROOT . "/conf/config.inc.php");
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());

    return ((float)$usec + (float)$sec);
}

$time_start = getmicrotime();
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$host = _DIRECTORYDB_HOST;
$db = _DIRECTORYDB_NAME;
$user = _DIRECTORYDB_USER;
$pass = _DIRECTORYDB_PASS;
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$link = ($GLOBALS["___mysqli_ston"] = mysqli_connect($host,  $user,  $pass));
mysqli_query( $link, "SET NAMES 'utf8'");
mysqli_query( $link, 'SET character_set_connection=utf8');
mysqli_query( $link, 'SET character_set_client=utf8');
mysqli_query( $link, 'SET character_set_results=utf8');
mysqli_select_db($GLOBALS["___mysqli_ston"], $db);
////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////
$sqlDomain = "	SELECT
						D.`id`, D.`database_host`, D.`database_port`, D.`database_username`, D.`database_password`, D.`database_name`, D.`url`
					FROM `Domain` AS D
					LEFT JOIN `Control_Cron` AS CC ON (CC.`domain_id` = D.`id`)
					WHERE ((CC.`running` = 'N' AND ADDDATE(CC.`last_run_date`, INTERVAL 20 MINUTE) <= NOW() OR CC.`last_run_date` = '0000-00-00 00:00:00') OR (ADDDATE(CC.`last_run_date`, INTERVAL 1 DAY) <= NOW() OR CC.`last_run_date` = '0000-00-00 00:00:00'))
					AND CC.`type` = 'renewal_reminder'
					AND D.`status` = 'A'
					ORDER BY
						IF (CC.`last_run_date` IS NULL, 0, 1),
						CC.`last_run_date`,
						D.`id`
					LIMIT 1";

$resDomain = mysqli_query( $link, $sqlDomain);

if (mysqli_num_rows($resDomain) > 0) {
    $rowDomain = mysqli_fetch_assoc($resDomain);
    define("SELECTED_DOMAIN_ID", $rowDomain["id"]);

    $sqlUpdate = "UPDATE `Control_Cron` SET `running` = 'Y', `last_run_date` = NOW() WHERE `domain_id` = " . SELECTED_DOMAIN_ID . " AND `type` = 'renewal_reminder'";
    mysqli_query( $link, $sqlUpdate);

    ////////////////////////////////////////////////////////////////////////////////////////////////////
    $domainHost = $rowDomain["database_host"] . ($rowDomain["database_port"] ? ":" . $rowDomain["database_port"] : "");
    $domainUser = $rowDomain["database_username"];
    $domainPass = $rowDomain["database_password"];
    $domainDBName = $rowDomain["database_name"];
    $domainURL = $rowDomain["url"];

    $linkDomain = ($GLOBALS["___mysqli_ston"] = mysqli_connect($domainHost,  $domainUser,  $domainPass));
    mysqli_query( $linkDomain, "SET NAMES 'utf8'");
    mysqli_query( $linkDomain, 'SET character_set_connection=utf8');
    mysqli_query( $linkDomain, 'SET character_set_client=utf8');
    mysqli_query( $linkDomain, 'SET character_set_results=utf8');
    mysqli_select_db($GLOBALS["___mysqli_ston"], $domainDBName);
    ////////////////////////////////////////////////////////////////////////////////////////////////////
} else {
    exit;
}
////////////////////////////////////////////////////////////////////////////////////////////////////

$_inCron = false;
include_once(EDIRECTORY_ROOT . "/conf/loadconfig.inc.php");
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////

$url = $domainURL;
(SSL_ENABLED == "on" && FORCE_MEMBERS_SSL == "on") ? $url_protocol = "https://" : $url_protocol = "http://";
$default_url = $url_protocol . $url . (EDIRECTORY_FOLDER ? EDIRECTORY_FOLDER : "");
setting_get("sitemgr_email", $sitemgr_email);
setting_get("edir_default_language", $edir_default_language);
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$last_listing_reminder = 0;
if (!setting_get("last_listing_reminder", $last_listing_reminder)) {
    if (!setting_set("last_listing_reminder", "0")) {
        if (!setting_new("last_listing_reminder", "0")) {
            print "Renewal Reminder - last_listing_reminder error - Domain - " . SELECTED_DOMAIN_ID . " - " . date("Y-m-d H:i:s") . "\n";
        }
    }
}
if (!$last_listing_reminder) {
    $last_listing_reminder = 0;
}

unset($allNot);
$sqlNot = "SELECT * FROM Email_Notification WHERE deactivate = '0' AND days > 0 ORDER BY days";
$resultNot = mysqli_query( $linkDomain, $sqlNot);
while ($rowNot = mysqli_fetch_assoc($resultNot)) {
    $allNot[$rowNot["days"]][$edir_default_language]["body"] = $rowNot["body"];
    $allNot[$rowNot["days"]][$edir_default_language]["subject"] = $rowNot["subject"];
    $allNot[$rowNot["days"]][$edir_default_language]["bcc"] = $rowNot["bcc"];
    $allNot[$rowNot["days"]][$edir_default_language]["content_type"] = $rowNot["content_type"];
    $allNot[$rowNot["days"]][$edir_default_language]["body"] = $rowNot["body"];
    $allNot[$rowNot["days"]][$edir_default_language]["subject"] = $rowNot["subject"];
}

if ($allNot && (count($allNot) > 0)) {

    $allNotCount = 0;
    $before_days = 0;
    foreach ($allNot as $days => $this_email_data) {
        if ($allNotCount == 0) {
            $allNotSQL[] = "(DATE_FORMAT(renewal_date, '%Y%m%d') > DATE_FORMAT(NOW(), '%Y%m%d') AND DATE_FORMAT(DATE_SUB(renewal_date, INTERVAL " . $days . " DAY), '%Y%m%d') <= DATE_FORMAT(NOW(), '%Y%m%d') AND reminder != " . $days . ")";
        } else {
            $allNotSQL[] = "(DATE_FORMAT(DATE_SUB(renewal_date, INTERVAL " . $before_days . " DAY), '%Y%m%d') > DATE_FORMAT(NOW(), '%Y%m%d') AND DATE_FORMAT(DATE_SUB(renewal_date, INTERVAL " . $days . " DAY), '%Y%m%d') <= DATE_FORMAT(NOW(), '%Y%m%d') AND reminder != " . $days . ")";
        }
        $before_days = $days;
        $allNotCount++;
    }

    $sql = "" .
        " SELECT " .
        " id, account_id, title, renewal_date, reminder " .
        " FROM " .
        " Listing " .
        " WHERE " .
        " account_id > 0 " .
        " AND " .
        " renewal_date != '0000-00-00' " .
        " AND " .
        " ( " .
        implode(" OR ", $allNotSQL) .
        " ) " .
        " ORDER BY " .
        " id " .
        " LIMIT " .
        $last_listing_reminder . ", " . BLOCK . "";
    $result = mysqli_query( $linkDomain, $sql);
    $num_rows = mysqli_num_rows($result);

    $today_date = explode("-", date("Y-m-d"));
    $today_year = $today_date[0];
    $today_month = $today_date[1];
    $today_day = $today_date[2];

    while ($row = mysqli_fetch_assoc($result)) {

        $renewal_date = explode("-", $row["renewal_date"]);
        $renewal_year = $renewal_date[0];
        $renewal_month = $renewal_date[1];
        $renewal_day = $renewal_date[2];

        $reminder = 0;
        $allNotCount = 0;
        $before_days = 0;
        foreach ($allNot as $days => $this_email_data) {
            if ($allNotCount == 0) {
                if ((date("Ymd", mktime(0, 0, 0, $renewal_month, $renewal_day, $renewal_year)) > date("Ymd",
                            mktime(0, 0, 0, $today_month, $today_day, $today_year))) && (date("Ymd",
                            mktime(0, 0, 0, $renewal_month, $renewal_day - $days, $renewal_year)) <= date("Ymd",
                            mktime(0, 0, 0, $today_month, $today_day, $today_year)))
                ) {
                    $reminder = $days;
                }
            } else {
                if ((date("Ymd",
                            mktime(0, 0, 0, $renewal_month, $renewal_day - $before_days, $renewal_year)) > date("Ymd",
                            mktime(0, 0, 0, $today_month, $today_day, $today_year))) && (date("Ymd",
                            mktime(0, 0, 0, $renewal_month, $renewal_day - $days, $renewal_year)) <= date("Ymd",
                            mktime(0, 0, 0, $today_month, $today_day, $today_year)))
                ) {
                    $reminder = $days;
                }
            }
            $before_days = $days;
            $allNotCount++;
        }

        $contactObj = new Contact($row["account_id"]);

        /*
         * Workaround to assure that the notification is being sent for the correct expiration period
         * Due to some flaw on the crazy logic above, in some cases the code is triggering several notifications for the same listing
         */
        $dateNow = new DateTime(date("Y-m-d"));
        $dateRenewal = new DateTime($row["renewal_date"]);

        $diff = $dateRenewal->diff($dateNow)->format("%a");

        if ($diff == $reminder && $contactObj->getString("email")) {

            $email_data["body"] = $allNot[$reminder][$edir_default_language]["body"];
            $email_data["subject"] = $allNot[$reminder][$edir_default_language]["subject"];
            $email_data["bcc"] = $allNot[$reminder][$edir_default_language]["bcc"];
            $email_data["content_type"] = $allNot[$reminder][$edir_default_language]["content_type"];

            $email_data["subject"] = str_replace("DEFAULT_URL", $default_url, $email_data["subject"]);
            $email_data["body"] = str_replace("DEFAULT_URL", $default_url, $email_data["body"]);

            $email_data["subject"] = str_replace("LISTING_RENEWAL_DATE", $row["renewal_date"], $email_data["subject"]);
            $email_data["body"] = str_replace("LISTING_RENEWAL_DATE", $row["renewal_date"], $email_data["body"]);

            $email_data["subject"] = str_replace("DAYS_INTERVAL", $reminder, $email_data["subject"]);
            $email_data["body"] = str_replace("DAYS_INTERVAL", $reminder, $email_data["body"]);

            $email_data["subject"] = system_replaceEmailVariables($email_data["subject"], $row["id"], "listing", '', '', $default_url);
            $email_data["body"] = system_replaceEmailVariables($email_data["body"], $row["id"], "listing", '', '', $default_url);

            $email_data["subject"] = htmlspecialchars_decode($email_data["subject"]);
            $email_data["body"] = html_entity_decode($email_data["body"]);

            $to = $contactObj->getString("email");
            $bcc = $email_data["bcc"];
            $subject = $email_data["subject"];
            $message = $email_data["body"];
            $content_type = $email_data["content_type"];

            $message = str_replace("\r\n", "\n", $message);
            $message = str_replace("\n", "\r\n", $message);

            SymfonyCore::getContainer()->get('core.mailer')
                ->newMail($subject, $message, $content_type)
                ->setTo($to)
                ->setBcc($bcc)
                ->send();
        }

        $sql = "UPDATE Listing SET reminder = " . $reminder . " WHERE id = " . $row["id"] . "";
        mysqli_query( $linkDomain, $sql);

    }

}

if ($num_rows < BLOCK) {
    if (!setting_set("last_listing_reminder", "0")) {
        print "Renewal Reminder - last_listing_reminder error - Domain - " . SELECTED_DOMAIN_ID . " - " . date("Y-m-d H:i:s") . "\n";
    }
    $last_listing_reminder = 0;
} else {
    if (!setting_set("last_listing_reminder", ($last_listing_reminder + BLOCK))) {
        print "Renewal Reminder - last_listing_reminder error - Domain - " . SELECTED_DOMAIN_ID . " - " . date("Y-m-d H:i:s") . "\n";
    }
    $last_listing_reminder = $last_listing_reminder + BLOCK;
}

$sqlUpdate = "UPDATE `Control_Cron` SET `running` = 'N' WHERE `domain_id` = " . SELECTED_DOMAIN_ID . " AND `type` = 'renewal_reminder'";
mysqli_query( $link, $sqlUpdate);

////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$time_end = getmicrotime();
$time = $time_end - $time_start;
print "Renewal Reminder on Domain " . SELECTED_DOMAIN_ID . " - " . date("Y-m-d H:i:s") . " - " . round($time,
        2) . " seconds.\n";
if (!setting_set("last_datetime_renewalreminder", date("Y-m-d H:i:s"))) {
    if (!setting_new("last_datetime_renewalreminder", date("Y-m-d H:i:s"))) {
        print "last_datetime_renewalreminder error - Domain - " . SELECTED_DOMAIN_ID . " - " . date("Y-m-d H:i:s") . "\n";
    }
}
