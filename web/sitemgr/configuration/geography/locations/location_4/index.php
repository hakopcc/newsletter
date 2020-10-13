<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/configuration/geography/locations/location_4/index.php
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

    mixpanel_track("Accessed Manage Cities section");

	$url_redirect = "".DEFAULT_URL."/".SITEMGR_ALIAS."/configuration/geography/locations/location_4";
	$url_base = "".DEFAULT_URL."/".SITEMGR_ALIAS."/configuration/geography";
	$sitemgr = 1;

	$url_search_params = system_getURLLocationParams((($_POST)?($_POST):($_GET)));

	# ----------------------------------------------------------------------------------------------------
	# LOCATION BULK UPDATE
	# ----------------------------------------------------------------------------------------------------
	include(EDIRECTORY_ROOT."/includes/code/location_select.php");

	# ----------------------------------------------------------------------------------------------------
	# LOCATION RELATIONSHIP
	# ----------------------------------------------------------------------------------------------------
	$_locations = explode(",", EDIR_LOCATIONS);
	$_location_level = 4;
	if (!in_array($_location_level, $_locations)) {
		header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/");
		exit;
	}
	$_location_node_params = system_buildLocationNodeParams($_GET);
	system_retrieveLocationRelationship ($_locations, $_location_level, $_location_father_level, $_location_child_level);

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_GET);
	extract($_POST);

    //Delete
    if ($_SERVER['REQUEST_METHOD'] == "POST" && $action == "delete") {
        mixpanel_track("Deleted a city");
        $objLocation = new Location4();
        $objLocation->SetString("id", $id);
        $location_data = $objLocation->retrieveLocationById();
        $objLocation->Delete(false);
        $location_name = $location_data["name"];
        $_location_node_params = system_buildLocationNodeParams((($_POST)?($_POST):($_GET)));
        header("Location: ".$url_base."/locations/location_4/index.php?".($_location_node_params?$_location_node_params."&":"")."operation=delete&loc_name=".$location_name);
        exit;
    }

	// checking available filter options
	$aux_check_filter_option = (($_location_father_level !== false ) and (isset(${"location_".$_location_father_level}) and ${"location_".$_location_father_level} != ""));
	if (!$aux_check_filter_option) {
		for ($i_location_father_level = ($_location_father_level-1); $i_location_father_level > 0; $i_location_father_level--) {
			if (!$aux_check_filter_option) {
				$aux_check_filter_option = ((isset(${"location_".$i_location_father_level}) && ${"location_".$i_location_father_level} != "") and (in_array($i_location_father_level, $_locations)));
				$aux_available_level_filter = $i_location_father_level;
			}
		}
	}
	else {
		$aux_available_level_filter = $_location_father_level;
	}

	// Page Browsing /////////////////////////////////////////
	$pageObj  = new pageBrowsing("Location_4", $screen, RESULTS_PER_PAGE, "name, id", "name", $letter, ($aux_check_filter_option?"location_".$aux_available_level_filter."=".${"location_".$aux_available_level_filter}:false), "*", false, false, true);
	$locations = $pageObj->retrievePage();

	// N/A Location //////////////////////////////////////////
	$objLocationLabel = 'Location'.$_location_level;
	$location_na = new $objLocationLabel();
	$location_na->setString('name', LANG_NA);
	$aux_count = 0;
	foreach ($_locations as $i_child_level) {
		system_retrieveLocationRelationship ($_locations, $i_child_level, $i_location_father_level, $i_location_child_level);
		if (($i_location_child_level!==false) and ($_location_level < $i_location_child_level)) {
			if ($aux_available_level_filter)
				$i_locations_child = db_getFromDB("location".($i_location_child_level), array("location_".$_location_level, "location_".$aux_available_level_filter), array(0, ${"location_".$aux_available_level_filter}), "all");
			else
				$i_locations_child = db_getFromDB("location".($i_location_child_level), "location_".$_location_level, 0, "all");
			$aux_count += count($i_locations_child);
		}
	}

	if ($aux_count > 0)
		$locations[] = $location_na;
	//////////////////////////////////////////////////////////

	$paging_url = DEFAULT_URL."/".SITEMGR_ALIAS."/configuration/geography/locations/location_4/index.php";

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

                <div class="tab-options">
                    <?php include(SM_EDIRECTORY_ROOT."/layout/nav-tabs-geography.php"); ?>

                    <div class="row tab-content">
                        <section class="tab-pane active">
                            <div class="col-sm-9">
                                <?php
                                    system_buildLocationBreadCrumb ($_locations, $_GET, $_location_level);
                                    if ($locations) {
                                        include(INCLUDES_DIR."/tables/table_location.php");
                                    } else {
                                        include(INCLUDES_DIR."/tables/table_location_empty.php");
                                    }
                                ?>
                                <div class="content-control-bottom">
                                    <?php include(INCLUDES_DIR."/lists/list-pagination.php"); ?>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php
    include(INCLUDES_DIR."/modals/modal-delete.php");

	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/location.php";
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
