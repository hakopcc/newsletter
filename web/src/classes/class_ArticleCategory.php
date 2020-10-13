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
# * FILE: /classes/class_ArticleCategory.php
# ----------------------------------------------------------------------------------------------------

class ArticleCategory extends BaseNToFiveCategory
{
    const ENTITY_CLASS_NAME = 'Article';
    const SYNCHRONIZATION_SERVICE_NAME = 'article.category.synchronization';
    const ENTITY_TABLE_NAME = 'Article';
    const BANNER_SECTION_IDENTIFIER = 'article';

    public function __construct($var = '')
    {
        parent::__construct('article', 'ArticleCategory',$var, '', '');
    }
}
