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

class EventCategory extends BaseNToFiveCategory
{
    const ENTITY_CLASS_NAME = 'Event';
    const SYNCHRONIZATION_SERVICE_NAME = 'event.category.synchronization';
    const ENTITY_TABLE_NAME = 'Event';
    const BANNER_SECTION_IDENTIFIER = 'event';

    public function __construct($var = '')
    {
        parent::__construct('event', 'EventCategory',$var, '', '');
    }
}
