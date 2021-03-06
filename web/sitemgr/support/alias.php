<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /ed-admin/support/alias.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # THIS PAGE IS ONLY USED BY THE SUPPORT
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include("../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSMSession();

    if (!permission_hasSMPermSection(SITEMGR_PERMISSION_SUPERADMIN)) {
        header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/");
        exit;
    }

    $url_redirect = DEFAULT_URL."/".SITEMGR_ALIAS."/support/reset.php";
    extract($_GET);
    extract($_POST);

    # ----------------------------------------------------------------------------------------------------
    # FORMS DEFINES
    # ----------------------------------------------------------------------------------------------------

    $aliasDivisors[0]["name"] = "ALIAS_CLAIM_URL_DIVISOR";
    $aliasDivisors[0]["label"] = "Claim page";
    $aliasDivisors[0]["value"] = ($_SERVER["REQUEST_METHOD"] == "POST" ? $alias_claim_url_divisor : ALIAS_CLAIM_URL_DIVISOR);
    $aliasDivisors[0]["tip"] = DEFAULT_URL."/<b>".ALIAS_CLAIM_URL_DIVISOR."</b>/...";

    # ----------------------------------------------------------------------------------------------------
    # CODE
    # ----------------------------------------------------------------------------------------------------
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $errorArray = [];
        $errorMessage = '';

        foreach ($aliasDivisors as $divisor) {
            if (!${strtolower($divisor["name"])}) {
                $errorArray[] = "&#149;&nbsp;".$divisor["label"];
            }
        }

        if (is_array($errorArray) && $errorArray[0]) {
            $errorMessage = "<b>".system_showText(LANG_MSG_FIELDS_CONTAIN_ERRORS)."</b><br />".implode("<br />",
                    $errorArray);
        }

        if (!$errorMessage) {
            $fileConstPath = EDIRECTORY_ROOT."/custom/domain_".SELECTED_DOMAIN_ID."/conf/constants.inc.php";

            // saves configuration in yaml file
            $domain = new Domain(SELECTED_DOMAIN_ID);
            $classSymfonyYml = new Symfony('domains/'.$domain->getString('url').'.route.yml');
            $classSymfonyYml->save('Configs', ['parameters' => $_POST]);

            system_writeConstantsFile($fileConstPath, SELECTED_DOMAIN_ID, $_POST);
            header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/support/alias.php?message=ok");
            exit;
        }

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
            <div class="main-content" content-full="true">
                <?php
                    require SM_EDIRECTORY_ROOT.'/registration.php';
                    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
                ?>

                <section class="section-heading">
                    <div class="section-heading-content">
                        <h1 class="section-heading-title">Alias Options</h1>
                    </div>
                </section>
                
                <form name="configChecker" id="configChecker" action="<?= system_getFormAction($_SERVER["PHP_SELF"]) ?>" method="post" class="form-horizontal" role="form">
                    <section class=" row section-form">
                        <div class="col-sm-12">
                            <?php include(INCLUDES_DIR."/forms/form-support-alias.php"); ?>
                        </div>
                    </section>
                    <section class="row footer-action">
                        <div class="col-xs-12 text-center">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </section>
                </form>
            </div>
        </div>
    </main>
<?php
    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/alias.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
