<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/configuration/geography/index.php
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

    mixpanel_track("Accessed section Language & Geography");

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	$_locations = explode(",", EDIR_LOCATIONS);

	# ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

	$url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/configuration/geography/index.php";

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include(EDIRECTORY_ROOT."/includes/code/location_settings.php");

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
                        <h1 class="section-heading-title"><?=system_ShowText(LANG_SITEMGR_TIME_GEO)?> <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="<?=system_showText(LANG_SITEMGR_GEO_TIP);?>"></span></h1>
                    </div>
                </section>

                <div class="tab-options">
                    <?php include(SM_EDIRECTORY_ROOT."/layout/nav-tabs-geography.php"); ?>

                    <div class="row tab-content">
                        <section class="tab-pane active">
                            <form class="form-horizontal" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                                <input type="hidden" name="datesettings" value="true">

                                <?php include(INCLUDES_DIR."/forms/form-datesettings.php"); ?>
                            </form>

                            <form name="location_setting" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                                <?php include(INCLUDES_DIR."/forms/form-locationsettings.php"); ?>
                            </form>

                            <?php
                                // Check if some location was added manually
                                setting_get("added_location_manually", $added_location_manually);
                                if (file_exists(EDIRECTORY_ROOT."/".SITEMGR_ALIAS."/configuration/geography/locations/load_location.php") && ($added_location_manually != "Y")) {
                                    require(EDIRECTORY_ROOT."/".SITEMGR_ALIAS."/configuration/geography/locations/load_location.php");
                                    $enableButton = false;
                            ?>
                                <div class="col-sm-9">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <?=system_showText(LANG_SITEMGR_LOAD_LOCATIONS)?>
                                            <p class="small"><?=system_showText(LANG_SITEMGR_LOAD_LOCATIONS_TIP);?></p>
                                        </div>
                                        <div id="loading_location_status" class="alert alert-loading alert-block text-center hidden">
                                            <img src="<?=DEFAULT_URL;?>/<?=SITEMGR_ALIAS?>/assets/img/loading-64.gif">
                                        </div>

                                        <?php if (is_array($_array_location_options)) { ?>
                                            <div class="panel-body">
                                                <div class="form-group">
                                                    <form id="load_location" method="post" action="<?=$_SERVER["PHP_SELF"]?>" />
                                                        <?php foreach ($_array_location_options as $location_option) { ?>
                                                            <div class="checkbox">
                                                                <label>
                                                                    <?php if (is_array($loaded_locations) && (array_search($location_option["value"], $loaded_locations) !== false)) { ?>
                                                                        <input type="checkbox" class="disable_enable" name="load_location_option" value="<?=$location_option["value"]?>" checked disabled>
                                                                    <?php } else { $enableButton = true; ?>
                                                                        <input type="checkbox" class="disable_enable" name="load_location_option" value="<?=$location_option["value"]?>" <?= ($locations_enable === 'off' ? 'disabled' : ''); ?> />
                                                                    <?php } ?>
                                                                    <?=$location_option["label"]?>
                                                                </label>
                                                            </div>
                                                        <?php } ?>
                                                    </form>
                                                    <?php
                                                        $looseJS = "
                                                            function PrepareToLoadLocations() {
                                                                $('#loading_location_status').removeClass('hidden');
                                                                var location_options = $('#load_location').serialize();
                                                                $.post('".DEFAULT_URL."/".SITEMGR_ALIAS."/configuration/geography/locations/ajax_load_locations.php', {
                                                                    load_location_option: location_options
                                                                }, function( data ) {
                                                                    $('#loading_location_status').addClass('hidden');
                                                                    if ($.trim(data) == 'done') {
                                                                        notify.success('".system_showText(LANG_SITEMGR_LOAD_SUCCESS)."');
                                                                    } else {
                                                                        notify.error('".system_showText(LANG_SITEMGR_LOAD_ERROR)."', '', { fadeOut: 0 });
                                                                    }
                                                                });
                                                            }

                                                            ";
                                                        JavaScriptHandler::registerLoose($looseJS);
                                                    ?>
                                                </div>
                                            </div>

                                            <div class="panel-footer">
                                                <button type="button" value="<?=system_showText(LANG_SITEMGR_SUBMIT)?>" class="btn btn-<?=($enableButton ? "primary" : "default")?> " <?=($enableButton ? "onclick=\"PrepareToLoadLocations();\"" : "disabled")?>><?=system_showText(LANG_SITEMGR_SUBMIT)?></button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php
                                }
                            ?>
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
	$customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/location.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
