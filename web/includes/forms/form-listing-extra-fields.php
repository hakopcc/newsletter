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
	# * FILE: /includes/forms/form-listing-extra-fields.php
	# ----------------------------------------------------------------------------------------------------

?>
    <div id="listing-extra-fields">
        <?php

        if (!empty($listingtemplate_id)) {
            $listingTemplateId = $listingtemplate_id;
        } elseif (count($listingTemplateArray) === 1 && !empty($listingTemplateArray[0]->getId())) {
            $listingTemplateId = $listingTemplateArray[0]->getId();
        }

        if (!empty($listingTemplateId)) {
            try {
                echo $container->get('listingtemplatefield.service')->renderListingTemplateFields($listingTemplateId, $array_fields, $listingField, $members, $id);
            } catch (Exception $e) {
            }
        } ?>
    </div>
