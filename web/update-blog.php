<?php

////////////////////////////////////////////////////////////////////////////////////////////////////
ini_set("html_errors", false);
ini_set('memory_limit', '-1');
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
define("EDIRECTORY_ROOT", __DIR__."/..");
////////////////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////////////
$_inCron = true;
include_once("conf/config.inc.php");
////////////////////////////////////////////////////////////////////////////////////////////////////
$host = _DIRECTORYDB_HOST;
$db = _DIRECTORYDB_NAME;
$user = _DIRECTORYDB_USER;
$pass = _DIRECTORYDB_PASS;

$link = ($GLOBALS["___mysqli_ston"] = mysqli_connect($host,  $user,  $pass));
mysqli_query( $link, "SET NAMES 'utf8'");
mysqli_query( $link, 'SET character_set_connection=utf8');
mysqli_query( $link, 'SET character_set_client=utf8');
mysqli_query( $link, 'SET character_set_results=utf8');
mysqli_select_db($GLOBALS["___mysqli_ston"], $db);

$sqlDomain = "	SELECT
                            D.`id`, D.`database_host`, D.`database_port`, D.`database_username`, D.`database_password`, D.`database_name`, D.`url`
                        FROM `Domain` AS D
                        LEFT JOIN `Control_Cron` AS CC ON (CC.`domain_id` = D.`id`)
                        ORDER BY
                            IF (CC.`last_run_date` IS NULL, 0, 1),
                            CC.`last_run_date`,
                            D.`id`
                        LIMIT 1";

$resDomain = mysqli_query( $link, $sqlDomain);

if (mysqli_num_rows($resDomain) > 0) {
    $rowDomain = mysqli_fetch_assoc($resDomain);
    define("SELECTED_DOMAIN_ID", $rowDomain["id"]);

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
    print_r("query error");exit;
    exit;
}
////////////////////////////////////////////////////////////////////////////////////////////////////
$_inCron = false;
//include_once("./conf/loadconfig.inc.php");

/********************** Forum Module Insertion **********************/
$sqlFriendlyURL = 'SELECT id, title FROM Post WHERE friendly_url = ""';

$resultFriendlyURL = mysqli_query($linkDomain, $sqlFriendlyURL);
$ids = [];
if (mysqli_num_rows($resultFriendlyURL) > 0) {
    while ($row = mysqli_fetch_assoc($resultFriendlyURL)) {
        $ids[] = ['id' => $row['id'], 'title' => $row['title']];
    }
}

foreach ($ids as $post) {
    $postId = $post['id'];
    $url = 'post-'.$post['id'].'-'.uniqid();

    $updateQuery = 'UPDATE Post SET friendly_url = "' . $url . '" WHERE id = "' . $postId . '"';
    mysqli_query($linkDomain, $updateQuery);

    print_r($post['id'] . " Updated\n");
}exit;

if (mysqli_num_rows($resultFriendlyURL) > 0) {
    $friendlyUrl = $friendlyUrl . FRIENDLYURL_SEPARATOR . uniqid();
}

?>
