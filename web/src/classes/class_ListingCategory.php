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
# * FILE: /classes/class_ListingCategory.php
# ----------------------------------------------------------------------------------------------------

class ListingCategory extends BaseCategory
{
    const ENTITY_CLASS_NAME = 'Listing';
    const SYNCHRONIZATION_SERVICE_NAME = 'listing.category.synchronization';
    const ENTITY_TABLE_NAME = 'Listing';
    const BANNER_SECTION_IDENTIFIER = 'listing';

    public function __construct($var = '')
    {
        parent::__construct('listing', 'ListingCategory',$var, 'Listing_Category', 'listing_id');
    }

    protected function executeAfterDeleteCategoryBeforeSyncServiceDelete($dbObj){
        /**
         * This stretch is responsible for unlinking the deleted ListingCategory from the related ListingTemplate
         */
        $sql = 'DELETE FROM `ListingTemplate_ListingCategory` WHERE `category_id` ='.$this->id;
        $dbObj->query($sql);
    }
}
