<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Internal;

use ArcaSolutions\CoreBundle\Legacy\Data\Handle;
use ItemStatus;
use SymfonyCore;

class Question extends Handle
{
    public $id;
    public $account_id;
    public $title;
    public $friendly_url;
    public $description;
    public $entered;
    public $updated;
    public $upvotes;
    public $status;
    public $category_id;

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
            $sql = "SELECT * FROM Question WHERE id = $var";

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
        $this->category_id = !empty($row['category_id']) ? $row['category_id'] : 'NULL';
        $this->title = !empty($row['title']) ? $row['title'] : ($this->title ? $this->title : '');
        $this->friendly_url = !empty($row['friendly_url']) ? $row['friendly_url'] : ($this->friendly_url ? $this->friendly_url : '');
        $this->description = !empty($row['description']) ? $row['description'] : ($this->description ? $this->description : '');
        $this->entered = !empty($row['entered']) ? $row['entered'] : ($this->entered ? $this->entered : '0000-00-00 00:00:00');
        $this->updated = !empty($row['updated']) ? $row['updated'] : ($this->updated ? $this->updated : '0000-00-00 00:00:00');
        $this->upvotes = !empty($row['upvotes']) ? $row['upvotes'] : ($this->upvotes ? $this->upvotes : 'NULL');
        $this->status = !empty($row['status']) ? $row['status'] : ($this->status ? $this->status : 'P');

        $this->data_in_array = $row;
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

        ### Answers
        $sql = "DELETE FROM Answer WHERE question_id = $this->id";
        $dbObj->query($sql);

        ### Question
        $sql = "DELETE FROM Question WHERE id = $this->id";
        $dbObj->query($sql);

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get('question.synchronization')->addDelete($this->id);
        }
    }

    public function getCategories(
        $have_data = false,
        $data = false,
        $id = false,
        $getAll = false,
        $object = false,
        $bulk = false
    ) {

    }

    public function setCategories($array)
    {
        if ($this->id && isset($array[0])) {
            $this->setNumber('category_id', $array[0]);
            $this->Save();
        }
    }

    public function Save()
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);

        if ($this->domain_id) {
            $dbObj = db_getDBObjectByDomainID($this->domain_id, $dbMain);
            $aux_log_domain_id = $this->domain_id;
        } else if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            $aux_log_domain_id = SELECTED_DOMAIN_ID;
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        /* it checks if the social_network is already a json, if it's does not encode again */
        if (is_array($this->social_network)) {
            $this->social_network = json_encode($this->social_network);
        }

        $this->prepareToSave();

        $aux_old_account = str_replace("'", '', $this->old_account_id);
        $aux_account = str_replace("'", '', $this->account_id);

        $this->friendly_url = string_strtolower($this->friendly_url);

        if ($this->id) {

            $updateItem = true;
            $sql = "SELECT status FROM Question WHERE id = $this->id";
            $result = $dbObj->query($sql);
            if ($row = mysqli_fetch_assoc($result)) {
                $last_status = $row['status'];
            }
            $this_status = $this->status;
            $this_id = $this->id;

            $sql = 'UPDATE Question SET'
                ." account_id         = $this->account_id,"
                ." category_id         = $this->category_id,"
                ." title           = $this->title,"
                ." friendly_url           = $this->friendly_url,"
                ." description           = $this->description,"
                ." entered           = $this->entered,"
                ." updated           = $this->updated,"
                ." upvotes           = $this->upvotes,"
                ." status           = $this->status"
                ." WHERE id           = $this->id";

            $dbObj->query($sql);

        } else {
            $sql = 'INSERT INTO Question'
                .' (account_id,'
                .' category_id,'
                .' title,'
                .' friendly_url,'
                .' description,'
                .' entered,'
                .' updated,'
                .' upvotes,'
                .' status)'
                .' VALUES'
                ." ($this->account_id,"
                ." $this->category_id,"
                ." $this->title,"
                ." $this->friendly_url,"
                ." $this->description,"
                ." $this->entered,"
                ." $this->updated,"
                ." $this->upvotes,"
                ." $this->status)";

            $dbObj->query($sql);
            $this->id = (is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id)) ? false : $___mysqli_res);


            //Reload the question object variables
            $sql = "SELECT * FROM Question WHERE id = $this->id";
            $row = mysqli_fetch_array($dbObj->query($sql));
            $this->makeFromRow($row);
            $this->prepareToSave();

        }

        $this->prepareToUse();

        $this->synchronize();
    }

    public function synchronize()
    {
        if ($symfonyContainer = SymfonyCore::getContainer()) {
            if ($this->status == 'A') {
                $symfonyContainer->get('question.synchronization')->addUpsert($this->id);
            } else {
                $symfonyContainer->get('question.synchronization')->addDelete($this->id);
            }
            $symfonyContainer->get('question.category.synchronization')->addUpsert($this->category_id);
        }
    }

    public function updateCategoryStatusByID()
    {

    }

    public function getFullPath()
    {

    }

    public function getGalleries()
    {
        return [];
    }
}
