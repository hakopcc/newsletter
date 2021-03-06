<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/activity/view-claim.php
	# ----------------------------------------------------------------------------------------------------

    if (is_array($previewClaim)) {
        foreach ($previewClaim as $prevClaim) { ?>

        <section class="view-content-info" id="view-content-info-<?=$prevClaim["id"]?>" style="display:none">
            <div class="control-view">
                <div class="btn-toolbar pull-left">
                    <div class="btn-group btn-group-sm ">
                    <?php if ($prevClaim["listing_id"]) { ?>
                        <a class="btn btn-icon btn-info" href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER?>/listing.php?id=<?=$prevClaim["listing_id"]?>" title="<?=system_showText(LANG_LABEL_EDIT);?>"><i class="icon-edit38"></i> <span class="hidden-xs"><?=system_showText(LANG_LABEL_EDIT);?></span></a>
                    <?php } ?>
                    <?php if ($prevClaim["canApprove"]) { ?>
                        <a class="btn btn-icon btn-info" href="<?=$url_redirect?>/approve.php?id=<?=$prevClaim["id"]?>&screen=<?=$screen?>&letter=<?=$letter?><?=(($url_search_params) ? "&$url_search_params" : "")?>">
                            <i class="icon-ion-ios7-checkmark-empty"></i>
                            <?=system_showText(LANG_SITEMGR_APPROVE)?>
                        </a>
                    <?php } ?>
                    <?php if ($prevClaim["canDeny"]) { ?>
                        <a class="btn btn-icon btn-info" class="text-warning" href="<?=$url_redirect?>/deny.php?id=<?=$prevClaim["id"]?>&screen=<?=$screen?>&letter=<?=$letter?><?=(($url_search_params) ? "&$url_search_params" : "")?>">
                            <i class="icon-ion-ios7-close-empty"></i>
                            <?=system_showText(LANG_SITEMGR_DENY)?>
                        </a>
                    <?php } ?>
                    </div>
                </div>
                <button type="button" class="close close-view" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>

            <div class="view-item">
                <h1><?=string_ucwords(system_showText(LANG_SITEMGR_LISTING))?> - <?=system_showText(LANG_SITEMGR_INFORMATION)?></h1>
                <p>
                    <?=string_ucwords(system_showText(LANG_SITEMGR_ACCOUNT))?>:
                    <?php
                        if ($prevClaim["account_id"]) {
                            echo "<a href=\"".$url_base."/account/sponsor/sponsor.php?id=".$prevClaim["account_id"]."\">";
                        }
                        echo system_showAccountUserName($prevClaim["username"]);
                        if ($prevClaim["account_id"]) {
                            echo "</a>";
                        }
                    ?>
                </p>

            <div class="content-table">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>&nbsp;</th>
                            <th><?=system_showText(LANG_SITEMGR_OLD)?></th>
                            <th><?=system_showText(LANG_SITEMGR_NEW)?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><b><?=string_ucwords(system_showText(LANG_SITEMGR_LISTING))?> <?=system_showText(LANG_SITEMGR_TITLE)?></b></td>
                            <td><?=$prevClaim['old_title'] ?></td>
                            <td><?=$prevClaim['new_title'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_SITEMGR_LABEL_FRIENDLYURL)?></b></td>
                            <td><?=$prevClaim['old_friendly_url'] ?></td>
                            <td><?=$prevClaim['new_friendly_url'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_LABEL_CATEGORY_PLURAL)?></b></td>
                            <td><?=$prevClaim['old_categories'] ?></td>
                            <td><?=$prevClaim['new_categories'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_SITEMGR_LABEL_EMAIL)?></b></td>
                            <td><?=$prevClaim['old_email'] ?></td>
                            <td><?=$prevClaim['new_email'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_SITEMGR_LABEL_URL)?></b></td>
                            <td><?=$prevClaim['old_url'] ?></td>
                            <td><?=$prevClaim['new_url'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_SITEMGR_LABEL_PHONE)?></b></td>
                            <td><?=$prevClaim['old_phone'] ?></td>
                            <td><?=$prevClaim['new_phone'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_LABEL_LABEL_ADDITIONAL_PHONE)?></b></td>
                            <td><?=$prevClaim['old_label_additional_phone'] ?></td>
                            <td><?=$prevClaim['new_label_additional_phone'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_LABEL_ADDITIONAL_PHONE)?></b></td>
                            <td><?=$prevClaim['old_additional_phone'] ?></td>
                            <td><?=$prevClaim['new_additional_phone'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_SITEMGR_LABEL_ADDRESS)?></b></td>
                            <td><?=$prevClaim['old_address'] ?></td>
                            <td><?=$prevClaim['new_address'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_SITEMGR_LABEL_ADDRESS2)?></b></td>
                            <td><?=$prevClaim['old_address2'] ?></td>
                            <td><?=$prevClaim['new_address2'] ?></td>
                        </tr>
                        <?php foreach ($_locations as $_location_level) {
                            $location_arrayOld = db_getFromDB('location'.$_location_level, 'id', $prevClaim["old_location_".$_location_level], 1, '', 'array');
                            $location_arrayNew = db_getFromDB('location'.$_location_level, 'id', $prevClaim["new_location_".$_location_level], 1, '', 'array');
                        ?>
                            <tr>
                                <td><b><?=system_showText((constant("LANG_SITEMGR_LABEL_".constant("LOCATION".$_location_level."_SYSTEM"))))?></b></td>
                                <td><?=$location_arrayOld["name"]?></td>
                                <td><?=$location_arrayNew["name"]?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td><b><?=string_ucwords(ZIPCODE_LABEL)?></b></td>
                            <td><?=$prevClaim['old_zipcode'] ?></td>
                            <td><?=$prevClaim['new_zipcode'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=ucfirst(system_showText(LANG_SITEMGR_TEMPLATE))?></b></td>
                            <td><?=$prevClaim['old_listingtemplate'] ?></td>
                            <td><?=$prevClaim['new_listingtemplate'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_SITEMGR_LEVEL)?></b></td>
                            <td><?=$prevClaim['old_level'] ?></td>
                            <td><?=$prevClaim['new_level'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_LABEL_SUMMARY_DESCRIPTION)?></b></td>
                            <td><?=$prevClaim['old_description'] ?></td>
                            <td><?=$prevClaim['new_description'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_LABEL_DESCRIPTION)?></b></td>
                            <td><?=$prevClaim['old_long_description'] ?></td>
                            <td><?=$prevClaim['new_long_description'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_LABEL_KEYWORDS_FOR_SEARCH)?></b></td>
                            <td><?=$prevClaim['old_keywords'] ?></td>
                            <td><?=$prevClaim['new_keywords'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_LABEL_REFERENCE)?></b></td>
                            <td><?=$prevClaim['old_locations'] ?></td>
                            <td><?=$prevClaim['new_locations'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_LABEL_FEATURES)?></b></td>
                            <td><?=$prevClaim['old_features'] ?></td>
                            <td><?=$prevClaim['new_features'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_LABEL_HOURS_OF_WORK)?></b></td>
                            <td><?=$prevClaim['old_hours_work'] ?></td>
                            <td><?=$prevClaim['new_hours_work'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_LABEL_SEO_TUNING)?> <?=system_showText(LANG_LABEL_TITLE)?></b></td>
                            <td><?=$prevClaim['old_seo_title'] ?></td>
                            <td><?=$prevClaim['new_seo_title'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_SEO_KEYWORDS)?></b></td>
                            <td><?=$prevClaim['old_seo_keywords'] ?></td>
                            <td><?=$prevClaim['new_seo_keywords'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_LABEL_DESCRIPTION)?></b></td>
                            <td><?=$prevClaim['old_seo_description'] ?></td>
                            <td><?=$prevClaim['new_seo_description'] ?></td>
                        </tr>
                        <tr>
                            <td><b><?=system_showText(LANG_LABEL_SOCIALNETWORK)?></b></td>
                            <td><?=$prevClaim['old_social_network'] ?></td>
                            <td><?=$prevClaim['new_social_network'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            </div>
        </section>

        <?php }
    }
