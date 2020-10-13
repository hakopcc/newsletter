<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/promote/awards/index.php
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

    mixpanel_track("Accessed Awards & Badges section");

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_POST);
	extract($_GET);

    # ----------------------------------------------------------------------------------------------------
	# SUBMIT
	# ----------------------------------------------------------------------------------------------------
	if ($_SERVER['REQUEST_METHOD'] == "POST") {

		if ($delete_id) {
			$editorChoice = new EditorChoice($delete_id);
            mixpanel_track("Deleted a badge");
			$editorChoice->delete();
			$message = 0;
			header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/promote/awards/index.php?message=".$message."");
			exit;
		} else {
            mixpanel_track("Edited a badge");
			include(INCLUDES_DIR."/code/editor_choice.php");
		}

	}

    # ----------------------------------------------------------------------------------------------------
	# FORMS DEFINES
	# ----------------------------------------------------------------------------------------------------

    $default_max_choice = 10;

	if ($default_max_choice == 1) $editorChoices[] = db_getFromDB("editor_choice", "", "", $default_max_choice, "id", "object", SELECTED_DOMAIN_ID);
	else $editorChoices = db_getFromDB("editor_choice", "", "", $default_max_choice, "id", "object", SELECTED_DOMAIN_ID);
	$indice = 0;
	if ($editorChoices) {
		foreach ($editorChoices as $editor) {
			$default_editor_id[$indice] = $editor->getNumber("id");
			$default_name[$indice]      = $editor->getString("name", false);
			$default_available[$indice] = ($editor->available) ? "checked" : "";
			$default_images[$indice++]  = $editor->getNumber("image_id");
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
                        <h1 class="section-heading-title"><?=system_showText(LANG_SITEMGR_AWARD_BADGE);?> <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="<?=system_showText(LANG_SITEMGR_AWARD_BADGE_TIP1);?>"></span></h1>
                    </div>
                </section>

                <section class="row">
                    <section class="form-thumbnails">
                        <div class="row">
                            <?php include(INCLUDES_DIR."/lists/list-badges.php"); ?>
                        </div>
                    </section>
                </section>

                <form name="badges" id="badges" role="form" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="last_badge_changed" id="last_badge_changed" value="">
                    <input type="hidden" name="delete_id" id="delete_id" value="">

                    <section class="tab-content">
                        <?php for ($i = 0; $i < $default_max_choice; $i++) { ?>
                            <input type="hidden" name="choice[]" value="<?=$default_editor_id[$i]?>">
                            <input type="hidden" name="image[]"  value="<?=$default_images[$i]?>">

                            <div class="row tab-pane section-form <?=($_POST && $last_badge_changed == $i && $message_error_editorchoice ? "active" : "")?>" id="form-badge<?=$i;?>">
                                <?php include(INCLUDES_DIR."/forms/form-badge.php"); ?>

                                <div class="footer-action col-sm-12 text-center">
                                    <button type="submit" name="editorchoice" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
                                </div>
                            </div>
                        <?php } ?>
                    </section>
                </form>
            </div>
        </div>
    </main>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/awards.php";

	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
