<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/listing/deal.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track("Accessed Deal Association tab");

	$url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER;
	$url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
	$sitemgr = 1;

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

	$url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include(EDIRECTORY_ROOT."/includes/code/listing_promotion.php");
    $levelObj = $level = new ListingLevel($listing->getNumber("level"));

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(SM_EDIRECTORY_ROOT."/layout/header.php");

?>
    <main class="main-dashboard">
        <nav class="main-sidebar">
            <?php include(SM_EDIRECTORY_ROOT."/layout/sidebar-dashboard.php"); ?>
            <div class="sidebar-submenu">
                <?php include(SM_EDIRECTORY_ROOT . "/layout/sidebar.php"); ?>
            </div>
        </nav>
        <div class="main-wrapper">
            <?php
                $listingObj = new Listing($id);
                $level = $listingObj->getLevel($id);
                $account_id = $listingObj->getNumber('account_id');

                $limit = system_checkListingLevelField($level, 'deals') ?? 0;

                include(SM_EDIRECTORY_ROOT."/layout/navbar.php");
            ?>
            <div class="main-content" content-full="true">
                <?php
                    require(SM_EDIRECTORY_ROOT."/registration.php");
                    require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
                ?>
                <form role="form" name="promotion" class="form-content-blocked" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?=$id?>" />
                    <input type="hidden" name="promotion_id" id="promotion_id" value="<?=$promotion_id?>" />
                    <input type="hidden" name="listing_id" value="<?=$listing_id?>">
                    <input type="hidden" name="letter" value="<?=$letter?>" />
                    <input type="hidden" name="screen" value="<?=$screen?>" />
                    <?=system_getFormInputSearchParams((($_POST)?($_POST):($_GET)));?>

                    <section class="section-heading">
                        <div class="section-heading-content">
                            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/"?>" class="heading-back-button"><i class="fa fa-angle-left"></i> <?=system_showText(LANG_SITEMGR_NAVBAR_LISTING);?></a>
                            <?php if ($id) { ?>
                                <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_EDIT));?> <?= $listing->getString('title') ?></h1>
                            <?php } else { ?>
                                <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_ADD))." ".system_showText(LANG_SITEMGR_LISTING_SING) ?></h1>
                            <?php } ?>
                        </div>
                        <?php if (CUSTOM_PROMOTION_FEATURE == "on") { ?>
                            <div class="section-heading-actions">
                                <button type="submit" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
                            </div>
                        <?php } ?>
                    </section>

                    <section class="row tab-options new-structure-form-block">
                        <div class="container">
                            <?php include(SM_EDIRECTORY_ROOT."/layout/nav-tabs-content-listing.php"); ?>
                        </div>
                        <?php if (CUSTOM_PROMOTION_FEATURE == "on") { ?>
                            <div class="tab-content">
                                <div class="tab-pane active">
                                    <div class="container">
                                        <?php include(INCLUDES_DIR."/forms/form-listing-promotion.php"); ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </section>

                    <?php if (CUSTOM_PROMOTION_FEATURE == "on") { ?>
                        <section class="row footer-action">
                            <div class="container">
                                <div class="col-xs-12 text-right">
                                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/"?>" class="btn btn-default btn-xs"><?=system_showText(LANG_CANCEL)?></a>
                                    <span class="separator"> <?=system_showText(LANG_OR)?>  </span>
                                    <button type="submit" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
                                </div>
                            </div>
                        </section>
                    <?php } else { ?>
                        <p class="alert alert-info"><?=system_showText(LANG_SITEMGR_MODULE_UNAVAILABLE)?></p>
                    <?php } ?>
                </form>

                <form id="formlistingpromotioncancel" action="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/<?=LISTING_FEATURE_FOLDER;?>/index.php" method="post">
                    <input type="hidden" name="letter" value="<?=$letter?>" />
                    <input type="hidden" name="screen" value="<?=$screen?>" />
                    <?=system_getFormInputSearchParams((($_POST)?($_POST):($_GET)));?>
                </form>
            </div>
        </div>
    </main>

<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/modules.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
