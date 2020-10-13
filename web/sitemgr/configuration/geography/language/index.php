<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /ed-admin/configuration/language/index.php
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
    # VALIDATING FEATURES
    # ----------------------------------------------------------------------------------------------------
    if (MULTILANGUAGE_FEATURE != "on") {
        exit;
    }

    # ----------------------------------------------------------------------------------------------------
    # AUX
    # ----------------------------------------------------------------------------------------------------
    $url_redirect = "" . DEFAULT_URL . "/" . SITEMGR_ALIAS . "/configuration/geography/language/index.php";
    $url_base = "" . DEFAULT_URL . "/" . SITEMGR_ALIAS . "/configuration/geography/language";
    extract($_GET);
    extract($_POST);
    $actionFrom = "changeLang";

    # ----------------------------------------------------------------------------------------------------
    # CODE
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT . "/includes/code/language_center.php");

    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    include(SM_EDIRECTORY_ROOT . "/layout/header.php");
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

                <div class="row tab-options">
                    <?php include(SM_EDIRECTORY_ROOT . "/layout/nav-tabs-geography.php"); ?>

                    <div class="row tab-content">
                        <section class="tab-pane active">
                            <?php include(INCLUDES_DIR . "/forms/form-languages.php"); ?>
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
    $customJS = SM_EDIRECTORY_ROOT . "/assets/custom-js/location.php";
    include(SM_EDIRECTORY_ROOT . "/layout/footer.php");
