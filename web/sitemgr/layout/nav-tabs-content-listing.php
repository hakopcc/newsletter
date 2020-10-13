<?php
    /**
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/nav-tabs-content-listing.php
	# ----------------------------------------------------------------------------------------------------

    $matches = null;
    preg_match( "/(\w*)(?=\.php)/", $_SERVER['PHP_SELF'], $matches );

    empty( $matches ) or $activeTab[ array_pop( $matches ) ] = 'class="active"';

    $listingPageUrl = DEFAULT_URL.'/'.SITEMGR_ALIAS.'/content/'.LISTING_FEATURE_FOLDER;

    if ($id) {
?>
    <ul class="nav nav-tabs pull-left" id="ListingTabs" role="tablist">
        <li <?= $activeTab['listing'] ?>>
            <a href="<?=$listingPageUrl?>/listing.php?id=<?=$id?>" role="tab"><?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_EDITINFORMATION);?></a>
        </li>

        <?php if (PROMOTION_FEATURE == "on" && CUSTOM_PROMOTION_FEATURE == "on" && CUSTOM_HAS_PROMOTION == "on"){ ?>
            <li <?= $activeTab['deal'] ?>
            id="dealTab"
            <?=(system_checkListingLevelField($listing->getNumber('level'), 'deals')) ? '' : 'style="display: none;"'?>
            >
                <a href="<?=$listingPageUrl?>/deal.php?id=<?=$id?>" role="tab"><?=system_showText(LANG_PROMOTION_FEATURE_NAME_PLURAL);?></a>
            </li>
        <?php } ?>

        <?php if (CLASSIFIED_FEATURE == "on" && CUSTOM_CLASSIFIED_FEATURE == "on") { ?>
            <li <?=$activeTab['classified']?>
            id="classifiedTab"
            <?=system_checkListingLevelField($listing->getNumber('level'), 'classifieds') ? '' : 'style="display: none;"'?>
            >
                <a href="<?=$listingPageUrl?>/classified.php?id=<?=$id?>" role="tab"><?=ucfirst(system_showText(LANG_CLASSIFIED_FEATURE_NAME_PLURAL))?></a>
            </li>
        <?php }

            /* ModStores Hooks */
            HookFire("sitemgrlistingtabs_after_render_tabs", [
                "id"           => &$id,
                "levelObj"     => &$levelObj,
                "listing"      => &$listing,
                "activeTab"    => &$activeTab,
                "url_redirect" => &$listingPageUrl,
            ]);
        ?>

        <?php
        try {
            echo $container->get('listingtemplatefield.service')->renderLinkedListingTabs($listingtemplate_id, $id, $listing->getNumber('level'), $linkedListingField);
        } catch (Exception $e) {
        }
        ?>
    </ul>
<?php } ?>
