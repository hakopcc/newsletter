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
# * FILE: /classes/class_Domain.php
# ----------------------------------------------------------------------------------------------------

/**
 * <code>
 *        $domainObj = new Domain($id);
 * <code>
 * @copyright Copyright 2018 Arca Solutions, Inc.
 * @author Arca Solutions, Inc.
 * @version 8.0.00
 * @package Classes
 * @name Domain
 * @access Public
 */
class Domain extends Handle
{

    /**
     * @var integer
     * @access Private
     */
    var $id;
    /**
     * @var integer
     * @access Private
     */
    var $smaccount_id;
    /**
     * @var string
     * @access Private
     */
    var $name;
    /**
     * @var string
     * @access Private
     */
    var $database_host;
    /**
     * @var string
     * @access Private
     */
    var $database_port;
    /**
     * @var string
     * @access Private
     */
    var $database_username;
    /**
     * @var string
     * @access Private
     */
    var $database_password;
    /**
     * @var string
     * @access Private
     */
    var $database_name;
    /**
     * @var string
     * @access Private
     */
    var $url;
    /**
     * A - Active
     * D - Deleted
     * P - Pending
     * When a Domain is deleted the domain status is set to "D"
     * When an error occurs while the domain is created as its status is 'P'
     * @var char
     * @access Private
     */
    var $status;
    /**
     * @var string
     * @access Private
     */
    var $activation_status;
    /**
     * @var date
     * @access Private
     */
    var $created;
    /**
     * When a Domain is deleted this field is set to Current Date
     * @var date
     * @access Private
     */
    var $deleted_date;
    /**
     * @var integer
     * @access Private
     */
    var $percent;
    /**
     * @var boolean
     * @access Private
     */
    var $error;
    /**
     * @var string
     * @access Private
     */
    var $event_feature;
    /**
     * @var string
     * @access Private
     */
    var $banner_feature;
    /**
     * @var string
     * @access Private
     */
    var $classified_feature;
    /**
     * @var string
     * @access Private
     */
    var $article_feature;

    /**
     * <code>
     *        $domainObj = new Domain($id);
     *        //OR
     *        $domainObj = new Domain($row);
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name Domain
     * @access Public
     * @param mixed $var
     * @return Domain
     */
    public function __construct($var = '')
    {
        $db = db_getDBObject(DEFAULT_DB, true);
        if (is_numeric($var) && ($var)) {

            /*
             * Get information of constants of domain
             */
            unset($row);
            $row = db_getDomainInformation($var);
            if (is_array($row)) {
                $this->makeFromRow($row);
            } else {
                $sql = "SELECT * FROM Domain WHERE id = $var";
                $row = mysqli_fetch_array($db->query($sql));
                $this->makeFromRow($row);
            }


        } else {
            if (is_string($var) && ($var)) {
                $sql = "SELECT * FROM Domain WHERE url = '$var' LIMIT 1";
                $row = mysqli_fetch_array($db->query($sql));
                $this->makeFromRow($row);
            } else {
                if (!is_array($var)) {
                    $var = [];
                }
                $this->makeFromRow($var);
            }
        }
    }

    /**
     * <code>
     *        $this->makeFromRow($row);
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @param array|string $row
     * @access Public
     */
    function makeFromRow($row = '')
    {
        $row["id"] ? $this->id = $row["id"] : $this->id = 0;
        $row["smaccount_id"] ? $this->smaccount_id = $row["smaccount_id"] : $this->smaccount_id = 0;
        $row["name"] ? $this->name = $row["name"] : $this->name = "";
        $row["database_host"] ? $this->database_host = $row["database_host"] : $this->database_host = "";
        $row["database_port"] ? $this->database_port = $row["database_port"] : $this->database_port = "";
        $row["database_username"] ? $this->database_username = $row["database_username"] : $this->database_username = "";
        $row["database_password"] ? $this->database_password = $row["database_password"] : $this->database_password = "";
        $row["database_name"] ? $this->database_name = $row["database_name"] : $this->database_name = "";
        $row["url"] ? $this->url = $row["url"] : $this->url = "";
        $row["status"] ? $this->status = $row["status"] : $this->status = "P";
        $row["activation_status"] ? $this->activation_status = $row["activation_status"] : $this->activation_status = "P";
        $this->setDate("created", $row["created"]);
        $this->setDate("deleted_date", $row["deleted_date"]);
        $row["event_feature"] ? $this->event_feature = $row["event_feature"] : $this->event_feature = "";
        $row["banner_feature"] ? $this->banner_feature = $row["banner_feature"] : $this->banner_feature = "";
        $row["classified_feature"] ? $this->classified_feature = $row["classified_feature"] : $this->classified_feature = "";
        $row["article_feature"] ? $this->article_feature = $row["article_feature"] : $this->article_feature = "";

    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $domainObj->Save();
     * <br /><br />
     *        //Using this in Domain() class.
     *        $this->Save();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name Save
     * @access Public
     */
    function Save()
    {
        $this->prepareToSave();

        $dbObj = db_getDBObject(DEFAULT_DB, true);

        if ($this->id) {
            $sql = "UPDATE Domain SET"
                ." smaccount_id = $this->smaccount_id,"
                ." name = $this->name,"
                ." database_host = $this->database_host,"
                ." database_port = $this->database_port,"
                ." database_username = $this->database_username,"
                ." database_password = $this->database_password,"
                ." database_name = $this->database_name,"
                ." url = $this->url,"
                ." article_feature = $this->article_feature,"
                ." banner_feature = $this->banner_feature,"
                ." classified_feature = $this->classified_feature,"
                ." event_feature = $this->event_feature"
                ." WHERE id = $this->id";

            $dbObj->query($sql);
        } else {
            $sql = "INSERT INTO Domain"
                ." (smaccount_id, name, database_host, database_port, database_username, database_password, database_name, url, status, activation_status, deleted_date, created, article_feature, banner_feature, classified_feature, event_feature)"
                ." VALUES"
                ." ($this->smaccount_id, $this->name, $this->database_host, $this->database_port, $this->database_username, $this->database_password, $this->database_name, $this->url, 'P','P', CURDATE(), CURDATE(), $this->article_feature, $this->banner_feature, $this->classified_feature, $this->event_feature)";

            $dbObj->query($sql);
            $this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);
        }
        $this->prepareToUse();
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $domainObj->Delete();
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name Delete
     * @access Public
     */
    function Delete()
    {
        $dbObj = db_getDBObject(DEFAULT_DB, true);

        if ($this->status == "A") {
            /*
             * Changing the Domain Status to 'D' (Deleted)
             */
            $sql = "UPDATE Domain SET status = 'D', deleted_date = CURDATE() WHERE id = $this->id";
            $dbObj->query($sql);

            $domainYml = new Symfony('domain.yml');
            $domainYml->remove('multidomain', $this->getString('url'));

        } else {
            if (is_numeric($this->id) && $this->id) {

                /*
                 * Deleting the domain Custom Folder (custom/domain_[ID]
                 */
                $customFolder = EDIRECTORY_ROOT."/custom/domain_$this->id";
                if (is_dir($customFolder)) {
                    $this->deleteFolder($customFolder);
                }

                /*
                 * Dropping the Domain Data Base
                 */
                // Instancing the new Data Base Connection
                $dbHostNEW = $this->database_host.($this->database_port ? ":".$this->database_port : "");
                $dbUserNEW = $this->database_username;
                $dbPassNEW = $this->database_password;
                $dbNameNEW = $this->database_name;

                $new_link = ($GLOBALS["___mysqli_ston"] = mysqli_connect($dbHostNEW,  $dbUserNEW,  $dbPassNEW));
                @mysqli_select_db( $new_link, $dbNameNEW);
                // Only if Data Base exists then Drop the Data Base
                if (!mysqli_error($GLOBALS["___mysqli_ston"])) {
                    $sqlDrop = "DROP DATABASE `".DB_NAME_PREFIX."_domain_$this->id`";
                    mysqli_query( $new_link, $sqlDrop);
                }

                // Control Export Listing
                $sql = "DELETE FROM `Control_Export_Listing` WHERE domain_id = $this->id";
                $dbObj->query($sql);

                // Control Export Event
                $sql = "DELETE FROM `Control_Export_Event` WHERE domain_id = $this->id";
                $dbObj->query($sql);

                // Control Export MailApp
                $sql = "DELETE FROM `Control_Export_MailApp` WHERE domain_id = $this->id";
                $dbObj->query($sql);

                // Control Cron
                $sql = "DELETE FROM `Control_Cron` WHERE `domain_id` = $this->id";
                $dbObj->query($sql);

                // Account
                $sql = "DELETE FROM `Account_Domain` WHERE `domain_id` = $this->id";
                $dbObj->query($sql);

                // Package
                $sql = "DELETE FROM `Package` WHERE `parent_domain` = $this->id";
                $dbObj->query($sql);

                // PackageItems
                $sql = "DELETE FROM `PackageItems` WHERE `domain_id` = $this->id";
                $dbObj->query($sql);

                // PackageModules
                $sql = "DELETE FROM `PackageModules` WHERE `parent_domain_id` = $this->id";
                $dbObj->query($sql);

                // PackageModules
                $sql = "DELETE FROM `PackageModules` WHERE `domain_id` = $this->id";
                $dbObj->query($sql);

                // Table Domain
                $sql = "DELETE FROM `Domain` WHERE id = $this->id";
                $dbObj->query($sql);

            }
        }

        /*
         * Rewrite the Domain Config File
         */
        $sql = "SELECT `id`, `url` FROM `Domain` WHERE `status` = 'A'";
        $result = $dbObj->query($sql);
        if (mysqli_num_rows($result) > 0) {
            $domainFilePath = EDIRECTORY_ROOT."/custom/domain/domain.inc.php";
            $domainFile = fopen($domainFilePath, "w+");
            unset($buffer);
            $buffer = "<?".PHP_EOL;
            while ($row = mysqli_fetch_assoc($result)) {
                $buffer .= "\$domainInfo[\"".$row["url"]."\"] = ".$row["id"].";".PHP_EOL;
            }
            $buffer .= "?>".PHP_EOL;
            fwrite($domainFile, $buffer, strlen($buffer));
            fclose($domainFile);
        }
    }

    function deleteFolder($directory, $empty = false)
    {
        if (string_substr($directory, -1) == "/") {
            $directory = string_substr($directory, 0, -1);
        }

        if (!file_exists($directory) || !is_dir($directory)) {
            return false;
        } elseif (!is_readable($directory)) {
            return false;
        } else {
            $directoryHandle = opendir($directory);

            while ($contents = readdir($directoryHandle)) {
                if ($contents != '.' && $contents != '..') {
                    $path = $directory."/".$contents;

                    if (is_dir($path)) {
                        $this->deleteFolder($path);
                    } else {
                        unlink($path);
                    }
                }
            }

            closedir($directoryHandle);

            if ($empty == false) {
                if (!rmdir($directory)) {
                    return false;
                }
            }

            return true;
        }
    }

    function getAllDomains($array_fields, $status, $less_this_domain = false)
    {

        $dbObj = db_getDBObject(DEFAULT_DB, true);
        $sql = "SELECT ".(is_array($array_fields) ? implode(",",
                $array_fields) : $array_fields)." FROM `Domain` WHERE `status` = '".$status."'".($less_this_domain ? " AND `id` != ".$less_this_domain : "")." ORDER BY name";
        $result = $dbObj->query($sql);
        if (mysqli_num_rows($result)) {
            unset($domains);
            while ($row = mysqli_fetch_assoc($result)) {
                $domains[] = $row;
            }
            if ($domains) {
                return $domains;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    function changeActivationStatus()
    {
        $auxAStatus = isRegistered($this->url, $this->id);

        if ($auxAStatus && $this->activation_status == "P") {
            $this->activation_status = "A";

            $dbObj = db_getDBObject(DEFAULT_DB, true);
            $this->prepareToSave();
            $sql = "UPDATE `Domain` SET `activation_status` = $this->activation_status WHERE `id` = $this->id";
            $dbObj->query($sql);
            $this->prepareToUse();
        } else {
            if (!$auxAStatus && $this->activation_status == "A") {
                $this->activation_status = "P";

                $dbObj = db_getDBObject(DEFAULT_DB, true);
                $this->prepareToSave();
                $sql = "UPDATE `Domain` SET `activation_status` = $this->activation_status WHERE `id` = $this->id";
                $dbObj->query($sql);
                $this->prepareToUse();
            }
        }
    }
}
