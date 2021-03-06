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
# * FILE: /cron/daily_maintenance.php
# ----------------------------------------------------------------------------------------------------

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
                        WHERE ((CC.`running` = 'N' AND ADDDATE(CC.`last_run_date`, INTERVAL 1 DAY) <= NOW() OR (CC.`last_run_date` = '0000-00-00 00:00:00') OR ADDDATE(CC.`last_run_date`, INTERVAL 1 DAY) <= NOW() OR CC.`last_run_date` = '0000-00-00 00:00:00')) 
                        AND CC.`type` = 'daily_maintenance'
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

    $sqlUpdate = "UPDATE `Control_Cron` SET `running` = 'Y', `last_run_date` = NOW() WHERE `domain_id` = " . SELECTED_DOMAIN_ID . " AND `type` = 'daily_maintenance'";
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

/* ModStores Hooks */
HookFire( "dailymaintenance_after_load_configurations", [
    "cron_log_id"  => &$cron_log_id,
    "messageLog"   => &$messageLog,
    "domainDBName" => &$domainDBName,
]);

////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "SELECT id FROM Listing WHERE renewal_date < NOW() AND renewal_date != '0000-00-00' AND status != 'E'";
$result = mysqli_query( $linkDomain, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $listingObj = new Listing($row["id"]);
    $listingObj->setString("status", "E");
    $listingObj->Save();
}
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "SELECT id FROM Event WHERE renewal_date < NOW() AND renewal_date != '0000-00-00' AND status != 'E'";
$result = mysqli_query( $linkDomain, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $eventObj = new Event($row["id"]);
    $eventObj->setString("status", "E");
    $eventObj->Save();
}

$sql = "SELECT id FROM Event WHERE ((end_date < DATE_FORMAT(NOW(), '%Y-%m-%d') AND end_date != '0000-00-00' AND recurring = 'N') OR (recurring = 'Y' AND repeat_event = 'N' AND until_date < DATE_FORMAT(NOW(), '%Y-%m-%d'))) AND status = 'A'";
$result = mysqli_query( $linkDomain, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $eventObj = new Event($row["id"]);
    $eventObj->setString("status", "S");
    $eventObj->Save();
}
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "SELECT id FROM Banner WHERE renewal_date < NOW() AND renewal_date != '0000-00-00' AND status != 'E'";
$result = mysqli_query( $linkDomain, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $bannerObj = new Banner($row["id"]);
    $bannerObj->setString("status", "E");
    $bannerObj->Save();
}
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "SELECT id FROM Classified WHERE renewal_date < NOW() AND renewal_date != '0000-00-00' AND status != 'E'";
$result = mysqli_query( $linkDomain, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $classifiedObj = new Classified($row["id"]);
    $classifiedObj->setString("status", "E");
    $classifiedObj->Save();
}
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "SELECT id FROM Article WHERE renewal_date < NOW() AND renewal_date != '0000-00-00' AND status != 'E'";
$result = mysqli_query( $linkDomain, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $articleObj = new Article($row["id"]);
    $articleObj->setString("status", "E");
    $articleObj->Save();
}
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "UPDATE Discount_Code SET status = 'E' WHERE expire_date < NOW() AND expire_date != '0000-00-00' AND status != 'E'";
$result = mysqli_query( $linkDomain, $sql);
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "UPDATE Invoice SET status = 'E' WHERE expire_date < NOW() AND expire_date != '0000-00-00' AND status != 'E' AND status != 'R'";
$result = mysqli_query( $linkDomain, $sql);
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$sql = "SELECT id FROM Invoice WHERE status = 'N'";
$result = mysqli_query( $linkDomain, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $invoice_ids[] = $row["id"];
    }
}
if ($invoice_ids) {
    $invoice_ids = implode(",", $invoice_ids);
    $sql = "DELETE FROM Invoice WHERE id IN ($invoice_ids)";
    $result = mysqli_query( $linkDomain, $sql);
    $sql = "DELETE FROM Invoice_Listing WHERE invoice_id IN ($invoice_ids)";
    $result = mysqli_query( $linkDomain, $sql);
    $sql = "DELETE FROM Invoice_Event WHERE invoice_id IN ($invoice_ids)";
    $result = mysqli_query( $linkDomain, $sql);
    $sql = "DELETE FROM Invoice_Banner WHERE invoice_id IN ($invoice_ids)";
    $result = mysqli_query( $linkDomain, $sql);
    $sql = "DELETE FROM Invoice_Classified WHERE invoice_id IN ($invoice_ids)";
    $result = mysqli_query( $linkDomain, $sql);
    $sql = "DELETE FROM Invoice_Article WHERE invoice_id IN ($invoice_ids)";
    $result = mysqli_query( $linkDomain, $sql);
}

///////////////////////////////  Delete Unused Image Files  ////////////////////////////////////////
$sqlDomain = "SELECT id FROM Domain";
$resultDomain = mysqli_query( $link, $sqlDomain);
if (mysqli_num_rows($resultDomain) > 0) {
    while ($rowDomain = mysqli_fetch_assoc($resultDomain)) {
        $dir = EDIRECTORY_ROOT . "/custom/domain_" . $rowDomain["id"] . "/image_files";
        $imageFiles = glob("$dir/_*.*");
        foreach ($imageFiles as $file) {
            unlink($file);
        }

        $dir = EDIRECTORY_ROOT . "/custom/domain_" . $rowDomain["id"] . "/image_files";
        $imageFiles = glob("$dir/resize_*.*");
        foreach ($imageFiles as $file) {
            unlink($file);
        }
    }
}

$dir = EDIRECTORY_ROOT . "/custom/profile";
$profileFiles = glob("$dir/_*.*");
foreach ($profileFiles as $file) {
    unlink($file);
}
////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////// Delete Pending and Deleted Domain Information ////////////////////////////////
$sqlDomain = "SELECT * FROM Domain WHERE (`status` = 'P') OR (`status` = 'D' AND ADDDATE(`deleted_date`, 7) <= CURDATE())";
$resultDomain = mysqli_query( $link, $sqlDomain);
if (mysqli_num_rows($resultDomain) > 0) {
    while ($rowDomain = mysqli_fetch_assoc($resultDomain)) {
        if ((int)system_checkPerm(EDIRECTORY_ROOT . "/custom/domain_" . $rowDomain["id"]) >= (int)PERMISSION_CUSTOM_FOLDER) {
            unset($domainObj);
            $domainObj = new Domain($rowDomain);
            $domainObj->Delete();
        } else {
            print("\nPermission denied in folder \"" . EDIRECTORY_ROOT . "/custom/domain_" . $rowDomain["id"] . "/\" Domain can not be deleted!\n");
        }
    }
}
////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////// Turn on the scalability if necessary ////////////////////////////////
$listing_scalability = LISTING_SCALABILITY_OPTIMIZATION;
$event_scalability = EVENT_SCALABILITY_OPTIMIZATION;
$classified_scalability = CLASSIFIED_SCALABILITY_OPTIMIZATION;

$listing_categ_scalability = LISTINGCATEGORY_SCALABILITY_OPTIMIZATION;
$event_categ_scalability = EVENTCATEGORY_SCALABILITY_OPTIMIZATION;
$classified_categ_scalability = CLASSIFIEDCATEGORY_SCALABILITY_OPTIMIZATION;
$article_categ_scalability = ARTICLECATEGORY_SCALABILITY_OPTIMIZATION;
$blog_categ_scalability = BLOGCATEGORY_SCALABILITY_OPTIMIZATION;

$updateScalabilityFile = false;

if (LISTING_SCALABILITY_OPTIMIZATION == "off") {
    $sql = "SELECT COUNT(id) AS total FROM Listing";
    $result = mysqli_query( $linkDomain, $sql);
    if ($result) {
        if ($row = mysqli_fetch_assoc($result)) {
            $this_value = $row["total"];

            if ($this_value >= LISTING_SCALABILITY_NUMBER) {
                $listing_scalability = "on";
                $updateScalabilityFile = true;
            }
        }
    }
}

if (EVENT_SCALABILITY_OPTIMIZATION == "off") {
    $sql = "SELECT COUNT(id) AS total FROM Event";
    $result = mysqli_query( $linkDomain, $sql);
    if ($result) {
        if ($row = mysqli_fetch_assoc($result)) {
            $this_value = $row["total"];

            if ($this_value >= EVENT_SCALABILITY_NUMBER) {
                $event_scalability = "on";
                $updateScalabilityFile = true;
            }
        }
    }
}

if (CLASSIFIED_SCALABILITY_OPTIMIZATION == "off") {
    $sql = "SELECT COUNT(id) AS total FROM Classified";
    $result = mysqli_query( $linkDomain, $sql);
    if ($result) {
        if ($row = mysqli_fetch_assoc($result)) {
            $this_value = $row["total"];

            if ($this_value >= CLASSIFIED_SCALABILITY_NUMBER) {
                $classified_scalability = "on";
                $updateScalabilityFile = true;
            }
        }
    }
}

if (LISTINGCATEGORY_SCALABILITY_OPTIMIZATION == "off") {
    $sql = "SELECT COUNT(id) AS total FROM ListingCategory WHERE category_id IS NULL ";
    $result = mysqli_query( $linkDomain, $sql);
    if ($result) {
        if ($row = mysqli_fetch_assoc($result)) {
            $this_value = $row["total"];

            if ($this_value >= LISTINGCATEGORY_SCALABILITY_NUMBER) {
                $listing_categ_scalability = "on";
                $updateScalabilityFile = true;
            }
        }
    }
}

if (EVENTCATEGORY_SCALABILITY_OPTIMIZATION == "off") {
    $sql = "SELECT COUNT(id) AS total FROM EventCategory WHERE category_id IS NULL ";
    $result = mysqli_query( $linkDomain, $sql);
    if ($result) {
        if ($row = mysqli_fetch_assoc($result)) {
            $this_value = $row["total"];

            if ($this_value >= EVENTCATEGORY_SCALABILITY_NUMBER) {
                $event_categ_scalability = "on";
                $updateScalabilityFile = true;
            }
        }
    }
}

if (CLASSIFIEDCATEGORY_SCALABILITY_OPTIMIZATION == "off") {
    $sql = "SELECT COUNT(id) AS total FROM ClassifiedCategory WHERE category_id IS NULL ";
    $result = mysqli_query( $linkDomain, $sql);
    if ($result) {
        if ($row = mysqli_fetch_assoc($result)) {
            $this_value = $row["total"];

            if ($this_value >= CLASSIFIEDCATEGORY_SCALABILITY_NUMBER) {
                $classified_categ_scalability = "on";
                $updateScalabilityFile = true;
            }
        }
    }
}

if (ARTICLECATEGORY_SCALABILITY_OPTIMIZATION == "off") {
    $sql = "SELECT COUNT(id) AS total FROM ArticleCategory WHERE category_id IS NULL ";
    $result = mysqli_query( $linkDomain, $sql);
    if ($result) {
        if ($row = mysqli_fetch_assoc($result)) {
            $this_value = $row["total"];

            if ($this_value >= ARTICLECATEGORY_SCALABILITY_NUMBER) {
                $article_categ_scalability = "on";
                $updateScalabilityFile = true;
            }
        }
    }
}

if (BLOGCATEGORY_SCALABILITY_OPTIMIZATION == "off") {
    $sql = "SELECT COUNT(id) AS total FROM BlogCategory WHERE category_id IS NULL ";
    $result = mysqli_query( $linkDomain, $sql);
    if ($result) {
        if ($row = mysqli_fetch_assoc($result)) {
            $this_value = $row["total"];

            if ($this_value >= BLOGCATEGORY_SCALABILITY_NUMBER) {
                $blog_categ_scalability = "on";
                $updateScalabilityFile = true;
            }
        }
    }
}

if ($updateScalabilityFile) {
    $fileScalPath = EDIRECTORY_ROOT . "/custom/domain_" . SELECTED_DOMAIN_ID . "/conf/scalability.inc.php";

    $scalValues = [];
    $scalValues["listing_scalability"] = $listing_scalability;
    $scalValues["promotion_scalability"] = $promotion_scalability;
    $scalValues["event_scalability"] = $event_scalability;
    $scalValues["banner_scalability"] = $banner_scalability;
    $scalValues["classified_scalability"] = $classified_scalability;
    $scalValues["article_scalability"] = $article_scalability;
    $scalValues["blog_scalability"] = $blog_scalability;
    $scalValues["listingcateg_scalability"] = $listing_categ_scalability;
    $scalValues["eventcateg_scalability"] = $event_categ_scalability;
    $scalValues["classifiedcateg_scalability"] = $classified_categ_scalability;
    $scalValues["articlecateg_scalability"] = $article_categ_scalability;
    $scalValues["blogcateg_scalability"] = $blog_categ_scalability;

    if (!system_writeScalabilityFile($fileScalPath, SELECTED_DOMAIN_ID, $scalValues)) {

        print("\nPermission denied in folder \"" . EDIRECTORY_ROOT . "/custom/domain_" . $rowDomain["id"] . "/\" Can not rewrite scalability file!\n");

    }

}

////////////////////////////////////////////////////////////////////////////////////////////////////

$sqlUpdate = "UPDATE `Control_Cron` SET `running` = 'N' WHERE `domain_id` = " . SELECTED_DOMAIN_ID . " AND `type` = 'daily_maintenance'";
mysqli_query( $link, $sqlUpdate);

////////////////////////////////////////////////////////////////////////////////////////////////////
$time_end = getmicrotime();
$time = $time_end - $time_start;
print "Daily maintenance on Domain " . SELECTED_DOMAIN_ID . " - " . date("Y-m-d H:i:s") . " - " . round($time,
        2) . " seconds.\n";
if (!setting_set("last_datetime_dailymaintenance", date("Y-m-d H:i:s"))) {
    if (!setting_new("last_datetime_dailymaintenance", date("Y-m-d H:i:s"))) {
        print "last_datetime_dailymaintenance error - Domain - " . SELECTED_DOMAIN_ID . " - " . date("Y-m-d H:i:s") . "\n";
    }
}
