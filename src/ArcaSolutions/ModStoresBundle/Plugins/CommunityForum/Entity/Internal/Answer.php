<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Internal;

use ArcaSolutions\CoreBundle\Legacy\Data\Handle;
use ItemStatus;
use SymfonyCore;

class Answer extends Handle
{
    public $id;
    public $account_id;
    public $question_id;
    public $description;
    public $entered;
    public $updated;
    public $upvotes;
    public $status;

    public function __construct($var = '', $domain_id = false)
    {
        if (is_numeric($var) && $var) {
            $dbMain = db_getDBObject(DEFAULT_DB, true);
            if ($domain_id) {
                $this->domain_id = $domain_id;
                $db = db_getDBObjectByDomainID($domain_id, $dbMain);
            } else if (defined('SELECTED_DOMAIN_ID')) {
                $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $db = db_getDBObject();
            }
            unset($dbMain);
            $sql = "SELECT * FROM Answer WHERE id = $var";

            $row = mysqli_fetch_array($db->query($sql));

            unset($db);

            $this->old_account_id = $row['account_id'];

            $this->makeFromRow($row);
        } else {
            if (!is_array($var)) {
                $var = [];
            }
            $this->makeFromRow($var);
        }
    }

    public function makeFromRow($row = [])
    {
        $this->id = !empty($row['id']) ? $row['id'] : ($this->id ? $this->id : 0);
        $this->account_id = !empty($row['account_id']) ? $row['account_id'] : 'NULL';
        $this->question_id = !empty($row['question_id']) ? $row['question_id'] : ($this->question_id ? $this->question_id : 'NULL');
        $this->description = !empty($row['description']) ? $row['description'] : ($this->description ? $this->description : '');
        $this->entered = !empty($row['entered']) ? $row['entered'] : ($this->entered ? $this->entered : '0000-00-00 00:00:00');
        $this->updated = !empty($row['updated']) ? $row['updated'] : ($this->updated ? $this->updated : '0000-00-00 00:00:00');
        $this->upvotes = !empty($row['upvotes']) ? $row['upvotes'] : ($this->upvotes ? $this->upvotes : 'NULL');
        $this->status = !empty($row['status']) ? $row['status'] : ($this->status ? $this->status : 'P');

        $this->data_in_array = $row;
    }

    public function Save()
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);

        if ($this->domain_id) {
            $dbObj = db_getDBObjectByDomainID($this->domain_id, $dbMain);
        } else if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        /* it checks if the social_network is already a json, if it's does not encode again */
        if (is_array($this->social_network)) {
            $this->social_network = json_encode($this->social_network);
        }

        $this->prepareToSave();

        $this->friendly_url = string_strtolower($this->friendly_url);

        if ($this->id) {

            $updateItem = true;
            $sql = "SELECT status FROM Answer WHERE id = $this->id";
            $result = $dbObj->query($sql);
            if ($row = mysqli_fetch_assoc($result)) {
                $last_status = $row['status'];
            }
            $this_status = $this->status;
            $this_id = $this->id;

            $sql = 'UPDATE Answer SET'
                ." account_id         = $this->account_id,"
                ." question_id           = $this->question_id,"
                ." description           = $this->description,"
                ." entered           = $this->entered,"
                ." updated           = $this->updated,"
                ." upvotes           = $this->upvotes,"
                ." status           = $this->status"
                ." WHERE id           = $this->id";

            $dbObj->query($sql);

        } else {
            $sql = 'INSERT INTO Answer'
                .' (account_id,'
                .' question_id,'
                .' description,'
                .' entered,'
                .' updated,'
                .' upvotes,'
                .' status)'
                .' VALUES'
                ." ($this->account_id,"
                ." $this->question_id,"
                ." $this->description,"
                ." $this->entered,"
                ." $this->updated,"
                ." $this->upvotes,"
                ." $this->status)";

            $dbObj->query($sql);
            $this->id = (is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id)) ? false : $___mysqli_res);

            //Reload the Answer object variables
            $sql = "SELECT * FROM answer WHERE id = $this->id";
            $row = mysqli_fetch_array($dbObj->query($sql));
            $this->makeFromRow($row);
            $this->prepareToSave();

        }

        $this->prepareToUse();
    }

    public function Delete($domain_id = false, $update_count = true)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if ($domain_id) {
            $dbObj = db_getDBObjectByDomainID($domain_id, $dbMain);
        } else {
            if (defined('SELECTED_DOMAIN_ID')) {
                $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $dbObj = db_getDBObject();
            }
            unset($dbMain);
        }

        $sql = "DELETE FROM Answer WHERE id = $this->id";
        $dbObj->query($sql);

        $sql = "DELETE FROM AnswerUpvotes WHERE answer = $this->id";
        $dbObj->query($sql);

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get('question.synchronization')->addDelete($this->id);
        }
    }

    public function synchronize()
    {
        if ($symfonyContainer = SymfonyCore::getContainer()) {
            if ($this->status == 'A') {
                $symfonyContainer->get('question.synchronization')->addUpsert($this->id);
            } else {
                $symfonyContainer->get('question.synchronization')->addDelete($this->id);
            }
        }
    }

    public function getCategories()
    {
        return false;
    }

    public function setCategories($array)
    {
        return true;
    }
}
