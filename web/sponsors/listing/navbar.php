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
	# * FILE: /sponsors/listing/navbar.php
	# ----------------------------------------------------------------------------------------------------
?>
    <div class="sponsor-listing-template-tabs">
        <ul class="nav nav-tabs nav-justified">
            <li <?=((string_strpos($_SERVER["PHP_SELF"], "/".MEMBERS_ALIAS."/".LISTING_FEATURE_FOLDER."/listing") !== false) ? "class=\"active\"" : "") ?>>
                <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/<?=LISTING_FEATURE_FOLDER?>/listing.php?id=<?=$id?>"><?=system_showText(LANG_LISTING_INFORMATION)?></a>
            </li>

            <?php
            try {
                $linkedListings = $container->get('listingtemplatefield.service')->getLinkedListings($listingtemplate_id);

                if(!empty($linkedListings)) {
                    foreach ($linkedListings as $linkedListing) {
                        $activeTab = (!empty($linkedListingField) && $linkedListingField->getId() === $linkedListing->getId()) ? 'class="active"' : '';
                        ?>
                        <li <?= $activeTab ?>>
                            <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/<?=LISTING_FEATURE_FOLDER?>/linkedlisting.php?id=<?=$id?>&fieldId=<?=$linkedListing->getId()?>&listingtemplate_id=<?=$listingtemplate_id?>" role="tab"><?=$linkedListing->getLabel()?></a>
                        </li>
                    <?php }
                }
            } catch (Exception $e) {
            }
            ?>
        </ul>
    </div>
