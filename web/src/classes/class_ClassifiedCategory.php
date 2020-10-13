<?php

/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2020 Arca Solutions, Inc. All Rights Reserved.           #
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
# * FILE: /classes/class_ClassifiedCategory.php
# ----------------------------------------------------------------------------------------------------

class ClassifiedCategory extends BaseNToFiveCategory
{
    const ENTITY_CLASS_NAME = 'Classified';
    const SYNCHRONIZATION_SERVICE_NAME = 'classified.category.synchronization';
    const ENTITY_TABLE_NAME = 'Classified';
    const BANNER_SECTION_IDENTIFIER = 'classified';

    public function __construct($var = '')
    {
        parent::__construct('classified', 'ClassifiedCategory',$var, '', '');
    }
}
