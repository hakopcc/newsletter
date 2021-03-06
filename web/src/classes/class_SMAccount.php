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
# * FILE: /classes/class_smaccount.php
# ----------------------------------------------------------------------------------------------------

class SMAccount extends Handle
{
    var $id;
    var $updated;
    var $entered;
    var $username;
    var $password;
    var $permission;
    var $iprestriction;
    var $phone;
    var $email;
    var $active;
    var $mixpanelDistinctId;
    var $first_name;
    var $last_name;
    var $image_id;

    public function __construct($var = '')
    {
        if (is_numeric($var) && ($var)) {
            $db = db_getDBObject(DEFAULT_DB, true);
            $sql = "SELECT * FROM SMAccount WHERE id = $var";
            $row = mysqli_fetch_array($db->query($sql));
            $this->makeFromRow($row);
        } else {
            if (!is_array($var)) {
                $var = [];
            }
            $this->makeFromRow($var);
        }
    }

    function makeFromRow($row = '')
    {
        $this->id                 = $row['id']                   ?: ($this->id ?: 0);
        $this->updated            = $row['updated']              ?: ($this->updated ?: '');
        $this->entered            = $row['entered']              ?: ($this->entered ?: '');
        $this->username           = $row['username']             ?: ($this->username ?: '');
        $this->password           = $row['password']             ?: ($this->password ?: '');
        $this->permission         = $row['permission']           ?: ($this->permission ?: 0);
        $this->phone              = $row['phone']                ?: '';
        $this->email              = $row['email']                ?: '';
        $this->active             = $row['active']               ?: ($this->active ?: '');
        $this->iprestriction      = $row['iprestriction']        ?: ($this->iprestriction ?: '');
        $this->mixpanelDistinctId = $row['mixpanel_distinct_id'] ?: null;
        $this->first_name         = $row['first_name']           ?: '';
        $this->last_name          = $row['last_name']            ?: '';
        $this->image_id           = !empty($row['image_id'])     ? $row['image_id'] : ($this->image_id ?: 'NULL');
    }

    function Save()
    {
        $insert_password = $this->password;
        $aux_username = $this->username;
        $aux_password = $this->password;
        $this->prepareToSave();
        $dbObj = db_getDBObject(DEFAULT_DB, true);

        if ($this->id) {
            $sql = 'UPDATE SMAccount SET'
                . ' updated				= NOW(),'
                ." username			    = $this->username,"
                ." permission			= $this->permission,"
                ." iprestriction		= $this->iprestriction,"
                ." phone				= $this->phone,"
                ." email				= $this->email,"
                ." active				= $this->active,"
                ." first_name			= $this->first_name,"
                ." last_name			= $this->last_name,"
                ." image_id	     		= $this->image_id,"
                . ' complementary_info  = ' .db_formatString(md5($aux_username.$aux_password))
                ." WHERE id             = $this->id";

            $dbObj->query($sql);
        } else {
            $sql = 'INSERT INTO SMAccount'
                . ' ('
                . ' updated,'
                . ' entered,'
                . ' username,'
                . ' password,'
                . ' permission,'
                . ' iprestriction,'
                . ' phone,'
                . ' email,'
                . ' active,'
                . ' complementary_info,'
                . ' first_name,'
                . ' last_name,'
                . ' image_id,'
                . ' mixpanel_distinct_id'
                . ' )'
                . ' VALUES'
                . ' ('
                . ' NOW(),'
                . ' NOW(),'
                . " $this->username,"
                . ' ' .db_formatString(md5($insert_password)). ','
                . " $this->permission,"
                . " $this->iprestriction,"
                . " $this->phone,"
                . " $this->email,"
                . " $this->active,"
                . ' ' .db_formatString(md5($aux_username.(string_strtolower(PASSWORD_ENCRYPTION) == 'on') ? md5($aux_password) : $aux_password)). ', '
                . " $this->first_name,"
                . " $this->last_name,"
                . " $this->image_id,"
                . ' ' .$this->mixpanelDistinctId
                . ' )';

            $dbObj->query($sql);
            $this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);
        }

        $this->prepareToUse();
    }

    function updatePassword()
    {
        $dbObj = db_getDBObject(DEFAULT_DB, true);
        $sql = 'UPDATE SMAccount SET password = ' .db_formatString(md5($this->password)). ', complementary_info = ' .db_formatString(md5($this->username.$this->password))." WHERE id = $this->id";
        $dbObj->query($sql);
    }

    function Delete()
    {
        $dbObj = db_getDBObject(DEFAULT_DB, true);
        $sql = "DELETE FROM SMAccount WHERE id = $this->id";
        $dbObj->query($sql);
    }

}
