<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/configuration/geography/locations/location_5/location_5.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

	$url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."/configuration/geography";

	# ----------------------------------------------------------------------------------------------------
	# LOCATION RELATIONSHIP
	# ----------------------------------------------------------------------------------------------------
	$_locations = explode(",", EDIR_LOCATIONS);
	$_location_level = 5;
	if (!in_array($_location_level, $_locations)) {
		header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/");
		exit;
	}

	system_retrieveLocationRelationship ($_locations, $_location_level, $_location_father_level, $_location_child_level);

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_GET);
	extract($_POST);

	define("LOCATION_AREA","LOCATION5");
	define("LOCATION_TITLE", string_ucwords(system_showText(constant("LANG_LABEL_".LOCATION5_SYSTEM))));
	include_once(EDIRECTORY_ROOT."/includes/code/location.php");
	$_location_node_params = system_buildLocationNodeParams($_GET);

	if ($success) {
		$message = 2;
		header("Location: ".$url_base."/locations/location_5/index.php?".($_location_node_params?$_location_node_params."&":"")."operation=".$operation."&loc_name=".$location_name);
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

                <form role="form" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" name="location_data_in" id="location_data_in" method="post">
                    <input type="hidden" name="operation"  id="operation"  value="<?=$btn_action?>" />

                    <section class="section-heading">
                        <div class="section-heading-content">
                            <a href="<?=$url_base."/locations/location_5/index.php?".($_location_node_params?$_location_node_params : "")?>" class="heading-back-button"><i class="fa fa-angle-left"></i> <?=system_showText(LANG_SITEMGR_GEO_LOCATIONDATA);?></a>
                            <?php if ($id) { ?>
                                <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_EDIT));?> <?=$location_name?></h1>
                            <?php } else { ?>
                                <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_ADD))." ".system_showText(LOCATION_TITLE) ?></h1>
                            <?php } ?>
                        </div>
                        <div class="section-heading-actions">
                            <button type="submit" name="bt_operation_submit" id="bt_operation_submit" value="<?=$btn_label?>" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
                        </div>
                    </section>

                    <section class="row tab-options">
                        <div class="tab-content">
                            <div class="tab-pane active">
                                <div class="container">
                                    <?php include(INCLUDES_DIR."/forms/form-location.php"); ?>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="row footer-action">
                        <div class="container">
                            <div class="col-xs-12 text-right">
                                <a href="<?=$url_base."/locations/location_5/index.php?".($_location_node_params?$_location_node_params : "")?>" class="btn btn-default btn-xs"><?=system_showText(LANG_CANCEL)?></a>
                                <span class="separator"> <?=system_showText(LANG_OR)?> </span>
                                <button type="submit" name="bt_operation_submit" id="bt_operation_submit" value="<?=$btn_label?>" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
                            </div>
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
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
