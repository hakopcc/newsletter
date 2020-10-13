<?php
namespace ArcaSolutions\CoreBundle\Legacy\Data\Category;

use ArcaSolutions\CoreBundle\Legacy\Data\Handle;
use Exception;

use SymfonyCore;

abstract class BaseCategory extends Handle
{
    const ENTITY_CLASS_NAME = '';
    const SYNCHRONIZATION_SERVICE_NAME = '';
    const ENTITY_TABLE_NAME = '';
    const BANNER_SECTION_IDENTIFIER = '';
    public $id;
    public $title;
    public $page_title;
    public $friendly_url;
    public $category_id;
    public $image_id;
    public $icon_id;
    public $featured;
    public $summary_description;
    public $seo_description;
    public $keywords;
    public $seo_keywords;
    public $content;
    public $enabled;
    private $_categoryType;
    private $_categoryRelationTableName;
    private $_categoryTableName;
    private $_categoryRelationEntityIdFieldName;
    private $_nToNRelation = true;

    /**
     * BaseCategory constructor.
     * @param string $var
     * @param $categoryType
     * @param $categoryTableName
     * @param $categoryRelationTableName
     * @param $categoryRelationEntityIdFieldName
     * @throws Exception
     */
    public function __construct($categoryType, $categoryTableName, $var = '', $categoryRelationTableName = '', $categoryRelationEntityIdFieldName = '')
    {
        if(empty($this::SYNCHRONIZATION_SERVICE_NAME)){
            throw new Exception("The constant SYNCHRONIZATION_SERVICE_NAME need to be declared and set in class " . get_class($this));
        }
        if(empty($this::ENTITY_CLASS_NAME)){
            throw new Exception("The constant ENTITY_CLASS_NAME need to be declared and set in class " . get_class($this));
        }
        if(empty($this::ENTITY_TABLE_NAME)){
            throw new Exception("The constant ENTITY_TABLE_NAME need to be declared and set in class " . get_class($this));
        }
        if(empty($this::BANNER_SECTION_IDENTIFIER)){
            throw new Exception("The constant BANNER_SECTION_IDENTIFIER need to be declared and set in class " . get_class($this));
        }
        if(empty($categoryType)){
            throw new Exception("Category type cannot be empty");
        }
        $this->_categoryType = $categoryType;
        if(empty($categoryTableName)){
            throw new Exception("Category table name cannot be empty");
        }
        $this->_categoryTableName = $categoryTableName;

        if(empty($categoryRelationTableName)){
            $this->_categoryRelationTableName = '';
            $this->_nToNRelation = false;
        } else {
            $this->_categoryRelationTableName = $categoryRelationTableName;

            if (empty($categoryRelationEntityIdFieldName)) {
                throw new Exception("Category relation entity id field name cannot be empty");
            }
            $this->_categoryRelationEntityIdFieldName = $categoryRelationEntityIdFieldName;
            $this->_nToNRelation = true;
        }

        if (is_numeric($var) && ($var)) {
            $dbMain = db_getDBObject(DEFAULT_DB, true);
            if (defined('SELECTED_DOMAIN_ID')) {
                $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $db = db_getDBObject();
            }
            unset($dbMain);
            $sql = "SELECT * FROM ".$this->_categoryTableName." WHERE id = $var";
            $result = $db->query($sql, MYSQLI_USE_RESULT);
            $row = mysqli_fetch_array($result);
            mysqli_free_result($result);
            $this->makeFromRow($row);
        } else {
            if (!is_array($var)) {
                $var = [];
            }
            $this->makeFromRow($var);
        }

        /* ModStores Hooks */
        HookFire('classbasecategory_contruct', [
            'that' => &$this,
            'categoryType' => $this->_categoryType
        ]);
    }

    /**
     * @param string $row
     */
    public function makeFromRow($row = '')
    {
        /* ModStores Hooks */
        HookFire('classbasecategory_before_makerow', [
            'that' => &$this,
            'row'  => &$row,
            'categoryType' => $this->_categoryType
        ]);

        $this->id = ($row['id']) ? $row['id'] : ($this->id ? $this->id : 0);
        $this->title = ($row['title']) ? $row['title'] : ($this->title ? $this->title : '');
        $this->page_title = ($row['page_title']) ? $row['page_title'] : ($this->page_title ? $this->page_title : '');
        $this->friendly_url = ($row['friendly_url']) ? $row['friendly_url'] : ($this->friendly_url ? $this->friendly_url : '');
        $this->category_id = ($row['category_id']) ? $row['category_id'] : ($this->category_id ? $this->category_id : 'NULL');
        $this->summary_description = ($row['summary_description']) ? $row['summary_description'] : '';
        $this->featured = ($row['featured']) ? $row['featured'] : ($this->featured ? $this->featured : 'n');
        $this->seo_description = ($row['seo_description']) ? $row['seo_description'] : '';
        $this->keywords = ($row['keywords']) ? $row['keywords'] : ($this->keywords ? $this->keywords : '');
        $this->seo_keywords = ($row['seo_keywords']) ? $row['seo_keywords'] : '';
        $this->content = ($row['content']) ? $row['content'] : '';
        $this->enabled = ($row['enabled']) ? $row['enabled'] : ($this->enabled ? $this->enabled : 'n');

        if ($row['image_id']) {
            $this->image_id = $row['image_id'];
        } else {
            if (!$this->image_id) {
                $this->image_id = 'NULL';
            }
        }

        if ($row['icon_id']) {
            $this->icon_id = $row['icon_id'];
        } else {
            if (!$this->icon_id) {
                $this->icon_id = 'NULL';
            }
        }

        /* ModStores Hooks */
        HookFire('classbasecategory_after_makerow', [
            'that' => &$this,
            'row'  => &$row,
            'categoryType' => $this->_categoryType
        ]);
    }

    public function Save()
    {
        /* ModStores Hooks */
        HookFire('classbasecategory_before_preparesave', [
            'that' => &$this,
            'categoryType' => $this->_categoryType
        ]);

        $this->prepareToSave();

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        $this->friendly_url = string_strtolower($this->friendly_url);

        if ($this->id) {

            $sql = 'UPDATE '.$this->_categoryTableName.' SET'
                ." title = $this->title,"
                ." page_title = $this->page_title,"
                ." friendly_url = $this->friendly_url,"
                ." category_id = $this->category_id,"
                ." image_id = $this->image_id,"
                ." icon_id = $this->icon_id,"
                ." featured = $this->featured,"
                ." summary_description = $this->summary_description,"
                ." seo_description = $this->seo_description,"
                ." keywords = $this->keywords,"
                ." seo_keywords = $this->seo_keywords,"
                ." content = $this->content,"
                ." enabled = $this->enabled"
                ." WHERE id = $this->id";

            /* ModStores Hooks */
            HookFire('classbasecategory_before_updatequery', [
                'that' => &$this,
                'sql'  => &$sql,
                'categoryType' => $this->_categoryType
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire('classbasecategory_after_updatequery', [
                'that' => &$this,
                'categoryType' => $this->_categoryType
            ]);

        } else {

            $sql = 'INSERT INTO '.$this->_categoryTableName
                .' (title,'
                .' page_title,'
                .' friendly_url,'
                .' category_id,'
                .' image_id,'
                .' icon_id,'
                .' featured,'
                .' summary_description,'
                .' seo_description,'
                .' keywords,'
                .' seo_keywords,'
                .' content,'
                .' enabled)'
                .' VALUES'
                ." ($this->title,"
                ." $this->page_title,"
                ." $this->friendly_url,"
                ." $this->category_id,"
                ." $this->image_id,"
                ." $this->icon_id,"
                ." $this->featured,"
                ." $this->summary_description,"
                ." $this->seo_description,"
                ." $this->keywords,"
                ." $this->seo_keywords,"
                ." $this->content,"
                ." $this->enabled)";

            /* ModStores Hooks */
            HookFire('classbasecategory_before_insertquery', [
                'that' => &$this,
                'sql'  => &$sql,
                'categoryType' => $this->_categoryType
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire('classbasecategory_after_insertquery', [
                'that'  => &$this,
                'dbObj' => &$dbObj,
                'categoryType' => $this->_categoryType
            ]);

            $this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);

        }

        /* ModStores Hooks */
        HookFire('classbasecategory_before_prepareuse', [
            'that' => &$this,
            'categoryType' => $this->_categoryType
        ]);

        $this->prepareToUse();

        $this->synchronize();

        /* ModStores Hooks */
        HookFire('classbasecategory_after_save', [
            'that' => &$this,
            'categoryType' => $this->_categoryType
        ]);
    }

    /**
     * @param integer $category_id
     * @return mixed
     */
    public function findRootCategoryId($category_id)
    {
        $parentIds = $this->getParentIds($category_id);
        $count = count($parentIds);

        return $count > 0 ? $parentIds[($count - 1)] : $category_id;
    }

    /**
     * @param integer $categoryId
     * @return array
     */
    public function getParentIds($categoryId)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        $parentIds = [];

        while ($categoryId && $categoryId > 0) {
            $sql = "SELECT category_id, id FROM ".$this->_categoryTableName." WHERE id = $categoryId";
            $result = $dbObj->query($sql);
            $row = mysqli_fetch_assoc($result);
            $categoryId = $row['category_id'];
            $parentIds[] = $row['id'];
        }

        return $parentIds;
    }

    /**
     * @param $categoryId
     * @return array
     */
    public function getChildrenIds($categoryId)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        /*
         * Remove "'" if need
         */
        $categoryId = str_replace("'", '', $categoryId);
        $childrenIds = [];

        $sql = "SELECT id FROM ".$this->_categoryTableName." WHERE category_id = $categoryId";
        $result = $dbObj->query($sql);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $childrenIds[] = $row['id'];
                $childrenIds = array_merge($childrenIds, $this->getChildrenIds($row['id']));
            }
        }

        return $childrenIds;
    }

    /**
     * Get parents or children of a given category
     * @param int $id
     * @param bool $get_parents
     * @param bool $get_children
     * @return bool|string Coma separeted ids including the $id
     */
    public function getHierarchy($id, $get_parents = false, $get_children = false)
    {
        unset($dbObj, $string_hierarchy);
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        $sql = 'SELECT category.id,
						   category.category_id
						FROM '.$this->_categoryTableName.' category
						WHERE category.id = '.$id;

        $result = $dbObj->query($sql);

        if (mysqli_num_rows($result) > 0) {
            $aux_array = mysqli_fetch_assoc($result);

            //To keep the old rules
            if (!$get_parents && !$get_children) {
                if ($aux_array['category_id'] == 0) {
                    $get_parents = false;
                    $get_children = true;
                } else {
                    $get_parents = true;
                    $get_children = false;
                }
            }

            $array_hierarchy = null;
            if ($get_children) {
                // Get children
                $array_hierarchy = $this->getChildrenIds($id);
            } else {
                if ($get_parents) {
                    // Get Parents
                    $array_hierarchy = $this->getParentIds($id);
                }
            }

            if (is_array($array_hierarchy) && count($array_hierarchy) > 0) {
                $string_hierarchy = implode(',', $array_hierarchy);
            }

            if (string_strlen($string_hierarchy) > 0) {
                $string_hierarchy .= ','.$id;
            } else {
                $string_hierarchy = $id;
            }

            return $string_hierarchy;
        }

        return false;
    }

    public function Delete()
    {
        if (!$this->id) {
            return;
        }

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        $category_ids = $this->getHierarchy($this->id, false, true);
        $module_object_ids = [];

        if ($category_ids) {
			if($this->_nToNRelation) {
	            $sql = "SELECT ".$this->_categoryRelationEntityIdFieldName." FROM ".$this->_categoryRelationTableName." WHERE category_id IN ($category_ids)";
	            $result = $dbObj->query($sql);

	            while ($row = mysqli_fetch_assoc($result)) {
	                $module_object_ids[] = $row[$this->_categoryRelationEntityIdFieldName];
	            }

	            $sql_delete = "DELETE FROM ".$this->_categoryRelationTableName." WHERE category_id IN ($category_ids)";
	            $dbObj->query($sql_delete);
			}

            /* ModStores Hooks */
            HookFire('classbasecategory_before_delete', [
                'that' => &$this,
                'categoryType' => $this->_categoryType
            ]);

            $this->executeBeforeDeleteCategory($dbObj,$category_ids);

            $sql_delete = "DELETE FROM ".$this->_categoryTableName." WHERE id IN ($category_ids)";
            $dbObj->query($sql_delete);
        }

        $this->updateFullTextItems($module_object_ids);

        ### IMAGE
        if ($this->image_id) {
            $image = new Image($this->image_id);
            if ($image) {
                $image->Delete();
            }
        }
        if ($this->icon_id) {
            $image = new Image($this->icon_id);
            if ($image) {
                $image->Delete();
            }
        }

        $sql = "UPDATE Banner SET category_id = NULL WHERE category_id = $this->id AND section = '".$this::BANNER_SECTION_IDENTIFIER."'";
        $dbObj->query($sql);

        $this->executeAfterDeleteCategoryBeforeSyncServiceDelete($dbObj);

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get($this::SYNCHRONIZATION_SERVICE_NAME)->addDelete($category_ids);
        }
    }

    /**
     * @param mysql|null $dbObj
     * @param $category_ids
     */
    protected function executeBeforeDeleteCategory($dbObj,$category_ids){

    }

    /**
     * @param mysql|null $dbObj
     * @return mixed
     */
    protected abstract function executeAfterDeleteCategoryBeforeSyncServiceDelete($dbObj);

    /**
     * @return array|bool
     */
    public function getFullPath()
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        $fields = '`id`, `category_id`, `featured`, `enabled`, `friendly_url`, `title`';

        $category_id = $this->id;
        $i = 0;
        while ($category_id != 0) {
            $sql = "SELECT $fields FROM ".$this->_categoryTableName." WHERE id = $category_id";

            $result = $dbObj->query($sql, MYSQLI_USE_RESULT);
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            $path[$i]['id'] = $row['id'];
            $path[$i]['dad'] = $row['category_id'];
            $path[$i]['title'] = $row['title'];
            $path[$i]['friendly_url'] = $row['friendly_url'];
            $path[$i]['featured'] = $row['featured'];
            $path[$i]['enabled'] = $row['enabled'];
            $i++;
            $category_id = $row['category_id'];
        }
        if ($path) {
            $path = array_reverse($path);
            for ($i = 0; $i < count($path); $i++) {
                $path[$i]['level'] = $i + 1;
            }

            return ($path);
        } else {
            return false;
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

                    if($this->_nToNRelation) {
                        $sql = "SELECT " . $this->_categoryRelationEntityIdFieldName . " FROM " . $this->_categoryRelationTableName . " WHERE category_id IN ($category_ids)";
                        $result = $dbObj->query($sql);

                        while ($row = mysqli_fetch_array($result)) {
                            if ($row[$this->_categoryType . '_id']) {
                                $entityObj = new ${$this::ENTITY_CLASS_NAME}($row[$this->_categoryRelationEntityIdFieldName]);
                                if (method_exists($entityObj, 'setFullTextSearch')) {
                                    $entityObj->setFullTextSearch();
                                }
                                unset($entityObj);
                            }
                        }
                    } else {
                        $sql = "SELECT id FROM " . $this::ENTITY_TABLE_NAME . " WHERE category_id IN ($category_ids)";
                        $result = $dbObj->query($sql);
                        while ($row = mysqli_fetch_array($result)) {
                            if ($row['id']) {
                                $entityObj = new ${$this::ENTITY_CLASS_NAME}($row['id']);
                                if (method_exists($entityObj, 'setFullTextSearch')) {
                                    $entityObj->setFullTextSearch();
                                }
                                unset($entityObj);
                            }
                        }
                    }
                }

                $return = true;
            }
        } else {
            foreach ($module_object_ids as $module_object_id) {
                if ($module_object_id) {
                    $entityObj = new ${$this::ENTITY_CLASS_NAME}($module_object_id);
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

    public function synchronize()
    {
        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $synchronizationService = $symfonyContainer->get($this::SYNCHRONIZATION_SERVICE_NAME);

            $categoryIds = array_unique(explode(',', $this->getHierarchy($this->id, true)));

            $synchronizationService->addUpsert($categoryIds);
        }
    }
}
