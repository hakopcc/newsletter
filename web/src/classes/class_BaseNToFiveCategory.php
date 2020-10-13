<?php

abstract class BaseNToFiveCategory extends BaseCategory
{
    /**
     * @param mysql|null $dbObj
     * @param $category_ids
     */
    protected function executeBeforeDeleteCategory($dbObj,$category_ids){
        if(!empty($category_ids)) {
            for ($catIndex = 1; $catIndex <= 5; $catIndex++) {
                $sql = "UPDATE " . $this::ENTITY_TABLE_NAME . " SET cat_" . $catIndex . "_id = NULL, parcat_" . $catIndex . "_level1_id = 0, parcat_" . $catIndex . "_level2_id = 0, parcat_" . $catIndex . "_level3_id = 0, parcat_" . $catIndex . "_level4_id = 0 WHERE cat_" . $catIndex . "_id IN (" . $category_ids . ")";
                $dbObj->query($sql);
            }
        }
    }

    /**
     * @param array $module_object_ids
     * @return bool
     */
    public function updateFullTextItems($module_object_ids = [])
    {
        $return = false;

        if (!$module_object_ids) {
            if ($this->id) {
                $category_ids = $this->getHierarchy($this->id, true, false);
                $category_ids .= (string_strlen($category_ids) ? ',' : '');
                $category_ids .= $this->getHierarchy($this->id, false, true);

                if ($category_ids) {
                    $dbMain = db_getDBObject(DEFAULT_DB, true);
                    if (defined('SELECTED_DOMAIN_ID')) {
                        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
                    } else {
                        $dbObj = db_getDBObject();
                    }
                    unset($dbMain);

                    $sql = "SELECT id FROM " . $this::ENTITY_TABLE_NAME . " WHERE (";
                    for ($catIndex = 1; $catIndex <= 5; $catIndex++) {
                        $sql .= (($catIndex>1)?'OR ':'').'cat_'.$catIndex.'_id IN (' . $category_ids . ')';
                        $sql .= 'OR parcat_'.$catIndex.'_level1_id IN (' . $category_ids . ')';
                        $sql .= 'OR parcat_'.$catIndex.'_level2_id IN (' . $category_ids . ')';
                        $sql .= 'OR parcat_'.$catIndex.'_level3_id IN (' . $category_ids . ')';
                        $sql .= 'OR parcat_'.$catIndex.'_level4_id IN (' . $category_ids . ')';
                    }
                    $sql .= ')';

                    $result = $dbObj->query($sql);
                    while ($row = mysqli_fetch_array($result)) {
                        if ($row['id']) {
                            $class = $this::ENTITY_CLASS_NAME;
                            $entityObj = new $class($row['id']);
                            if (method_exists($entityObj, 'setFullTextSearch')) {
                                $entityObj->setFullTextSearch();
                            }
                            unset($entityObj);
                        }
                    }
                }
                $return = true;
            }
        } else {
            foreach ($module_object_ids as $module_object_id) {
                if ($module_object_id) {
                    $class = $this::ENTITY_CLASS_NAME;
                    $entityObj = new $class($module_object_id);
                    if(method_exists($entityObj,'setFullTextSearch')) {
                        $entityObj->setFullTextSearch();
                    }
                    unset($entityObj);
                }
            }
            $return = true;
        }
        return $return;
    }
}
