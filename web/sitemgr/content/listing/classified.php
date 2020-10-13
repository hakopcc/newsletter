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

    mixpanel_track("Accessed Classified Association tab");

    $url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER;
    $url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."";
    $sitemgr = 1;

    $url_search_params = system_getURLSearchParams((($_POST)?($_POST):($_GET)));

    # ----------------------------------------------------------------------------------------------------
    # AUX
    # ----------------------------------------------------------------------------------------------------
    /* I do not like this, but if not do this it will take a lot more time. Sorry */
    extract($_POST);
    extract($_GET);

    # ----------------------------------------------------------------------------------------------------
    # CODE
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/includes/code/listing_classified.php");


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
            <?php include(SM_EDIRECTORY_ROOT."/layout/navbar.php"); ?>
            <div class="main-content" content-full="true">
                <?php
                    require(SM_EDIRECTORY_ROOT."/registration.php");
                    require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
                ?>
                <form role="form" name="promotion" class="form-content-blocked" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?=$id?>" />
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
                        <?php if (CUSTOM_CLASSIFIED_FEATURE == "on") { ?>
                            <div class="section-heading-actions">
                                <button type="submit" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>">
                                    <?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?>
                                </button>
                            </div>
                        <?php } ?>
                    </section>

                    <section class="row tab-options new-structure-form-block">
                        <div class="container">
                            <?php include(SM_EDIRECTORY_ROOT."/layout/nav-tabs-content-listing.php"); ?>
                        </div>

                        <?php if (CUSTOM_CLASSIFIED_FEATURE == "on") { ?>
                            <div class="tab-content">
                                <div class="tab-pane active">
                                    <div class="container">
                                        <?php include(INCLUDES_DIR."/forms/form-listing-classified.php"); ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </section>

                    <?php if (CUSTOM_CLASSIFIED_FEATURE == "on") { ?>
                        <section class="row footer-action">
                            <div class="container">
                                <div class="col-xs-12 text-right">
                                    <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".LISTING_FEATURE_FOLDER."/"?>" class="btn btn-default btn-xs">
                                        <?=system_showText(LANG_CANCEL)?>
                                    </a>
                                    <span class="separator"> <?=system_showText(LANG_OR)?>  </span>
                                    <button type="submit" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>">
                                        <?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?>
                                    </button>
                                </div>
                            </div>
                        </section>
                    <?php } else { ?>
                        <p class="alert alert-info"><?=system_showText(LANG_SITEMGR_MODULE_UNAVAILABLE)?></p>
                    <?php } ?>
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
