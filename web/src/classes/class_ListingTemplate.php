<?

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
	# * FILE: /classes/class_listingTemplate.php
	# ----------------------------------------------------------------------------------------------------

/**
 * Class ListingTemplate
 */
class ListingTemplate extends Handle {

		var $id;
		var $title;
		var $updated;
		var $entered;
		var $status;
		var $price;
		var $templateFree;

        /**
         * ListingTemplate constructor.
         * @param string $var
         * @param bool $domain_id
         */
        public function __construct($var='', $domain_id = false) {
			if (is_numeric($var) && ($var)) {
				$dbMain = db_getDBObject(DEFAULT_DB, true);
				if ($domain_id){
					$db = db_getDBObjectByDomainID($domain_id, $dbMain);
				}else if (defined('SELECTED_DOMAIN_ID')) {
					$db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
				} else {
					$db = db_getDBObject();
				}
				unset($dbMain);
				$sql = "SELECT * FROM ListingTemplate WHERE id = $var";
				$row = mysqli_fetch_array($db->query($sql));
				$this->makeFromRow($row);
			} else {
                if (!is_array($var)) {
                    $var = array();
                }
				$this->makeFromRow($var);
			}

            /* ModStores Hooks */
            HookFire('classlistingtypes_contruct', [
                'that' => &$this
            ]);
		}

        /**
         * @param string $row
         */
        function makeFromRow($row='') {

            /* ModStores Hooks */
            HookFire('classlistingtypes_before_makerow', [
                'that' => &$this,
                'row'  => &$row,
            ]);

			$this->id						= ($row['id'])						? $row['id']					: ($this->id			? $this->id				: 0);
			$this->title					= ($row['title'])					? $row['title']					: ($this->title			? $this->title			: '');
			$this->updated					= ($row['updated'])					? $row['updated']				: ($this->updated		? $this->updated		: '');
			$this->entered					= ($row['entered'])					? $row['entered']				: ($this->entered		? $this->entered		: '');
			$this->status					= ($row['status'])					? $row['status']				: ($this->status		? $this->status			: '');
			$this->price					= ($row['price'])					? $row['price']					: ($this->price			? $this->price			: '0.00');
            $this->templateFree				= ($row['template_free'])			? $row['template_free']			: ($this->templateFree  ? $this->templateFree	: 'disabled');

            /* ModStores Hooks */
            HookFire('classlistingtypes_after_makerow', [
                'that' => &$this,
                'row'  => &$row,
            ]);
		}

		function Save() {

            /* ModStores Hooks */
            HookFire('classlistingtypes_before_preparesave', [
                'that' => &$this
            ]);

			$this->prepareToSave();
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined('SELECTED_DOMAIN_ID')) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}

			unset($dbMain);
			if ($this->id) {
				$sql  = 'UPDATE ListingTemplate SET'
					. " title                 = $this->title,"
					. ' updated               = NOW(),'
					. " status                = $this->status,"
					. " price                 = $this->price,"
                    . " template_free          = $this->templateFree"
					. " WHERE id  = $this->id";

                /* ModStores Hooks */
                HookFire('classlistingtypes_before_updatequery', [
                    'that' => &$this,
                    'sql' => &$sql,
                ]);

				$dbObj->query($sql);

                /* ModStores Hooks */
                HookFire('classlistingtypes_after_updatequery', [
                    'that' => &$this,
                ]);

			} else {
				$sql = 'INSERT INTO ListingTemplate'
					. ' ('
					. ' title,'
					. ' updated,'
					. ' entered,'
					. ' status,'
					. ' price,'
					. ' cat_id,'
                    . ' template_free'
					. ' )'
					. ' VALUES'
					. ' ('
					. " $this->title,"
					. ' NOW(),'
					. ' NOW(),'
					. " $this->status,"
					. " $this->price,"
					. " '',"
                    . " $this->templateFree"
					. ' )';

                /* ModStores Hooks */
                HookFire('classlistingtypes_before_insertquery', [
                    'that' => &$this,
                    'sql' => &$sql,
                ]);

				$dbObj->query($sql);

                /* ModStores Hooks */
                HookFire('classlistingtypes_after_insertquery', [
                    'that' => &$this,
                    'dbObj' => &$dbObj,
                ]);

				$this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);
			}

            /* ModStores Hooks */
            HookFire('classlistingtypes_before_prepareuse', [
                'that' => &$this,
            ]);

			$this->prepareToUse();

            /* ModStores Hooks */
            HookFire('classlistingtypes_after_save', [
                'that' => &$this,
            ]);
		}

		function clearListingTFields() {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined('SELECTED_DOMAIN_ID')) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
			unset($dbMain);
			$sql = "DELETE FROM ListingTField WHERE listingtemplate_id = $this->id";
			$dbObj->query($sql);
		}

        /**
         * @param string $field_name
         * @param bool $enabled
         * @return array|bool
         */
        function getListingTFields($field_name= '', $enabled = false) {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined('SELECTED_DOMAIN_ID')) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
			unset($dbMain);
			$sql = "SELECT * FROM ListingTField WHERE listingtemplate_id = $this->id ".($enabled ? "AND enabled = 'y'" : '');
			if (string_strlen(trim($field_name))>0) {
				$field_name = db_formatString($field_name);
				$sql .= " AND label = $field_name ";
			}
			$result = $dbObj->query($sql);
			if ($result && (mysqli_num_rows($result) >= 1)) {
				while ($row = mysqli_fetch_array($result)) {
					$fields[] = $row;
				}
				if ($fields) {
					return $fields;
				}
			}
			return false;
		}

        /**
         * @param string $label
         * @param bool $enabled
         * @return bool|mixed
         */
        function getFieldByLabel($label = '', $enabled = true){
            $dbMain = db_getDBObject(DEFAULT_DB, true);

			if (defined('SELECTED_DOMAIN_ID')) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
			unset($dbMain);
			$sql = 'SELECT label FROM ListingTField WHERE label = ' .db_formatString($label). ' ' .($enabled ? "AND enabled = 'y'" : '');
			$result = $dbObj->query($sql);
			if ($result && (mysqli_num_rows($result) >= 1)) {
				while ($row = mysqli_fetch_array($result)) {
					$fields = $row['field'];
				}
				if ($fields) {
					return $fields;
				}
			}
			return false;
        }

		function Delete() {

			$this->clearListingTFields();

			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined('SELECTED_DOMAIN_ID')) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
			unset($dbMain);

			/*
			 * Need make $listingObj->save() to update listing table to front
			 */
			$sql = 'SELECT id FROM Listing WHERE listingtemplate_id = ' .$this->id;
			$result = $dbObj->query($sql);
			if(mysqli_num_rows($result) > 0){
				while($row = mysqli_fetch_assoc($result)){
					unset($listingObj);
					$listingObj = new Listing($row['id']);
					$listingObj->setString('listingtemplate_id', 'NULL');
					$listingObj->Save();
				}
			}

            /* ModStores Hooks */
            HookFire('classlistingtypes_before_delete', [
                'that' => &$this
            ]);

			$sql = "DELETE FROM ListingTemplate WHERE id = $this->id";
			$dbObj->query($sql);

		}

        /**
         * @param bool $returnObj
         * @return array
         */
        function getCategories($returnObj = true) {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined('SELECTED_DOMAIN_ID')) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
			unset($dbMain);
			$sql = "SELECT cat_id FROM ListingTemplate WHERE id = $this->id";
			$r = $dbObj->query($sql);
			while ($row = mysqli_fetch_array($r)) {
				if ($row['cat_id']) {
					$cat_id = explode(',', $row['cat_id']);
					foreach ($cat_id as $catid) {
					    if ($returnObj) {
                            $categories[] = new ListingCategory($catid);
                        } else {
                            $categories[] = $catid;
                        }
					}
				}
			}
			return $categories;
		}

        /**
         * @param $array
         */
        function setCategories($array) {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined('SELECTED_DOMAIN_ID')) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
			unset($dbMain);
			$cat_id = '';
			if ($array) {
				foreach ($array as $category) {
					if ($category) {
						$catid[] = $category;
					}
				}
			}
			if ($catid) $cat_id = implode(',', $catid);
			$sql = 'UPDATE ListingTemplate SET cat_id = ' .db_formatString($cat_id)." WHERE id = $this->id";
			$dbObj->query($sql);
		}

	}

?>
