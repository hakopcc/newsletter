<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/design/themes/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
    include '../../../conf/loadconfig.inc.php';

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    mixpanel_track('Accessed section Themes');

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include INCLUDES_DIR.'/code/layout_editor.php';

    # ----------------------------------------------------------------------------------------------------
    # FORMS DEFINES
    # ----------------------------------------------------------------------------------------------------
    setting_get('sitemgr_language', $sitemgr_language);

    unset($valuesArray);
    unset($namesArray);

    $_valuesArray = explode(',', EDIR_THEMES);
    $_namesArray = explode(',', EDIR_THEMENAMES);
    $availableThemes = [];
    $countThemes = 0;

    for ($i = 0, $iMax = count($_valuesArray); $i < $iMax; $i++) {
        if ($_namesArray[$i]) {
            $availableThemes[$countThemes]['name'] = $_namesArray[$i];
            $availableThemes[$countThemes]['value'] = $_valuesArray[$i];

            switch ($_valuesArray[$i]) {
                case 'default'      :
                    $availableThemes[$countThemes]['preview_url'] = ($sitemgr_language == 'pt_br' ? 'http://demodirectory.com.br/' : 'http://demodirectory.com/');
                    break;
            }

            $countThemes++;

        }
    }

    $edir_theme = (EDIR_THEME == '' ? 'default' : EDIR_THEME);

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include SM_EDIRECTORY_ROOT.'/layout/header.php';
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
            <div class="main-content wysiwyg" content-full="true">
                <?php
                    require SM_EDIRECTORY_ROOT.'/registration.php';
                    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
                ?>

                <section class="section-heading">
                    <div class="section-heading-content">
                        <h1 class="section-heading-title"><?=system_showText(LANG_SITEMGR_MENU_THEMES)?> <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="<?=system_showText(LANG_SITEMGR_MENU_THEMES_TIP);?>"></span></h1>
                    </div>
                </section>

                <section class="form-thumbnails">
                    <form name="theme" id="theme" role="form" action="<?=system_getFormAction($_SERVER['PHP_SELF'])?>" method="post">
                        <input type="hidden" name="domain_id" value="<?=SELECTED_DOMAIN_ID?>">
                        <input type="hidden" name="select_theme" id="select_theme" value="<?=EDIR_THEME?>">
                        <input type="hidden" name="submitAction" value="changetheme">

                        <div class="row">
                            <div id="loading_theme" class="alert alert-loading alert-loading-fullscreen hidden" >
                                <img class="alert-img-center" src="<?=DEFAULT_URL;?>/<?=SITEMGR_ALIAS?>/assets/img/loading-128.gif">
                            </div>

                            <?php foreach ($availableThemes as $avTheme) { ?>
                                <div class="col-md-3 col-sm-6 col-xs-12">
                                    <div class="select-theme thumbnail <?=($avTheme['value'] == $edir_theme ? 'active in-use' : '')?>">
                                        <div class="caption">
                                            <h4><?=$avTheme['name'];?></h4>
                                            <img src="<?=DEFAULT_URL.'/'.SITEMGR_ALIAS.'/assets/img/themes/'.$avTheme['value'].'.png';?>" alt="<?=$avTheme['name'];?>">
                                            <br><br>
                                            <p class="text-center">
                                                <?php if ($avTheme['value'] == $edir_theme) { ?>
                                                    <a href="javascript:void(0);" class="active btn btn-primary"><?=system_showText(LANG_SITEMGR_INUSE)?></a>
                                                <?php } else { ?>
                                                    <a href="javascript:void(0);" class="btn btn-default" onclick="JS_submit('<?=$avTheme['value']?>');"><?=system_showText(LANG_SITEMGR_USETHIS)?></a>
                                                <?php } ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>
<?php
    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/design.php';
    include SM_EDIRECTORY_ROOT.'/layout/footer.php';
