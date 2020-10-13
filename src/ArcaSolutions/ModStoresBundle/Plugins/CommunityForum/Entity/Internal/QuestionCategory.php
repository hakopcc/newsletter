<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Entity\Internal;

use ArcaSolutions\CoreBundle\Legacy\Data\Category\BaseCategory;
use Exception;

class QuestionCategory extends BaseCategory
{
    const ENTITY_CLASS_NAME = Question::class;
    const SYNCHRONIZATION_SERVICE_NAME = 'question.category.synchronization';
    const ENTITY_TABLE_NAME = 'Question';
    const BANNER_SECTION_IDENTIFIER = 'question';

    /**
     * QuestionCategory constructor.
     * @param string $var
     * @throws Exception
     */
    public function __construct($var = '')
    {
        parent::__construct('question', 'QuestionCategory', $var);
    }

    protected function executeAfterDeleteCategoryBeforeSyncServiceDelete($dbObj)
    {
        $sql = "UPDATE Question SET category_id = NULL WHERE category_id = $this->id";
        $dbObj->query($sql);
    }

    public function updateFullTextItems($module_object_ids = [])
    {
        //This override is necessary to avoid errors caused by an issue that was no fixed in version 13.0.02 of eDirectory.
        //The issue is related with the usage of $variable = new ${<string_constant_from_class_with_a_class_name>}(<some_parameter>);
        //The issue is solved using a variable to keep the value of <string_constant_from_class_with_a_class_name> and changing the code to: $variable = new $variable_with_keep_value(<some_parameter>);
        //Lines affected was: src/ArcaSolutions/CoreBundle/Legacy/Data/Category/BaseCategory.php:572, src/ArcaSolutions/CoreBundle/Legacy/Data/Category/BaseCategory.php:584 and src/ArcaSolutions/CoreBundle/Legacy/Data/Category/BaseCategory.php:599
    }

    public function prepareToSave()
    {
        //Usage of getString here is avoided due to ignore any changes related to the _categoryTableName parameter
        $originalCategoryTableName = 'QuestionCategory';
        $vars = get_object_vars($this);
        for ($i = 0, $iMax = count($vars); $i < $iMax; $i++) {
            $key = each($vars);
            if($key['key']==='_categoryTableName'){
                $originalCategoryTableName = $key['value'];
                break;
            }
        }
        parent::prepareToSave();
        $this->setString('_categoryTableName', $originalCategoryTableName);
    }
}
