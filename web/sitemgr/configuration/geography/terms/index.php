<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /ed-admin/configuration/terms/index.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include("../../../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSMSession();
    permission_hasSMPerm();

    # ----------------------------------------------------------------------------------------------------
    # AUX
    # ----------------------------------------------------------------------------------------------------
    extract($_POST);
    extract($_GET);

    $manageModule = "nearbySearch";

    # ----------------------------------------------------------------------------------------------------
    # SEARCH & BULK UPDATE
    # ----------------------------------------------------------------------------------------------------
    if ($search_title) {
        $where = " token LIKE ".db_formatString('%'.$search_title.'%')." ";
    }

    include(INCLUDES_DIR."/code/bulkupdate.php");

    # ----------------------------------------------------------------------------------------------------
    # Page Browsing
    # ----------------------------------------------------------------------------------------------------
    $pageObj = new pageBrowsing("NearbySearch", $screen, RESULTS_PER_PAGE, "id DESC", "token", $letter, $where);
    $nearbyTerms= $pageObj->retrievePage();
    $paging_url = DEFAULT_URL."/".SITEMGR_ALIAS."/configuration/geography/terms/index.php";

    if (!$msg && !$error_msg && !$error_message){
        $msg =  ($action == "delete")? "successdel" : null;
    }

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
            <?php include(SM_EDIRECTORY_ROOT."/layout/submenu-content.php"); ?>
            <div class="content-control header-bar hidden" id="bulkupdate">
                <?php include(INCLUDES_DIR."/forms/form-bulkupdate.php"); ?>
            </div>
            <div class="main-content" content-full="false">
                <?php
                    require SM_EDIRECTORY_ROOT.'/registration.php';
                    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
                ?>

                <section class="section-heading">
                    <div class="section-heading-content">
                        <h1 class="section-heading-title"><?=system_ShowText(LANG_SITEMGR_TIME_GEO)?> <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="<?=system_showText(LANG_SITEMGR_GEO_TIP);?>"></span></h1>
                    </div>
                </section>

                <div class="row tab-options terms geography-terms">
                    <?php include(SM_EDIRECTORY_ROOT."/layout/nav-tabs-geography.php"); ?>

                    <div class="row tab-content">
                        <section class="tab-pane active">
                            <div class="content-full">
                                <?php if ($nearbyTerms) { ?>
                                    <?php include(SM_EDIRECTORY_ROOT."/layout/submenu-content.php"); ?>

                                    <div class="list-content">
                                        <?php include(INCLUDES_DIR."/lists/list-terms.php"); ?>

                                        <div class="content-control-bottom pagination-responsive">
                                            <?php include(INCLUDES_DIR."/lists/list-pagination.php"); ?>
                                        </div>
                                    </div>

                                    <div class="view-content">
                                        <?php include(SM_EDIRECTORY_ROOT."/configuration/geography/terms/view-term.php"); ?>
                                    </div>
                                <?php } else { ?>
                                    <?php include(SM_EDIRECTORY_ROOT."/layout/norecords.php"); ?>
                                <?php } ?>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php
    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    include(INCLUDES_DIR."/modals/modal-bulk.php");
    include(EDIRECTORY_ROOT."/includes/code/maptuning_forms.php");

    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/terms.php";
    include(SM_EDIRECTORY_ROOT . "/layout/footer.php");
