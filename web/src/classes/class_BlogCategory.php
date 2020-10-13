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
# * FILE: /classes/class_BlogCategory.php
# ----------------------------------------------------------------------------------------------------

class BlogCategory extends BaseCategory
{
    const ENTITY_CLASS_NAME = 'Post';
    const SYNCHRONIZATION_SERVICE_NAME = 'blog.category.synchronization';
    const ENTITY_TABLE_NAME = 'Post';
    const BANNER_SECTION_IDENTIFIER = 'blog';

    public function __construct($var = '')
    {
        parent::__construct('blog', 'BlogCategory',$var, 'Blog_Category', 'post_id');
    }
}
