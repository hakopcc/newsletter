<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/design/css-editor/index.php
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

    mixpanel_track("CSS Editor");

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	include(INCLUDES_DIR."/code/editor.php");

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
            <div class="main-content wysiwyg" content-full="true">
                <?php
                    require SM_EDIRECTORY_ROOT.'/registration.php';
                    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
                ?>

                <form id="htmleditor" role="form" name="htmleditor" class="html-editor" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                    <input type="hidden" name="domain_id" value="<?=SELECTED_DOMAIN_ID?>">
                    <input type="hidden" name="file" value="<?=$file?>">
                    <input type="hidden" name="fileType" value="<?=$fileType?>">
                    <input type="hidden" name="submitAction" value="csseditor">

                    <section class="section-heading">
                        <div class="section-heading-content">
                            <h1 class="section-heading-title"><?=system_showText(LANG_SITEMGR_CSS_EDITOR)?> <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="<?=system_showText(LANG_SITEMGR_EDITOR_TIP1);?>"></span></h1>
                        </div>
                        <div class="section-heading-action">
                            <button type="submit" name="revert" value="Submit" class="btn btn-default action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_RESET)?></button>
                            <button type="submit" name="htmleditor" value="Submit" class="btn btn-primary action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES);?></button>
                        </div>
                    </section>

                    <section class="row">
                        <div class="col-md-9 col-xs-12">
                            <textarea name="text" id="textarea" class="form-control css-editor-textarea" rows="30"><?=htmlspecialchars($text)?></textarea>
                        </div>
                        <div class="col-md-3 col-xs-12">
                            <div class="alert alert-warning" role="alert">
                                <i class="fa fa-exclamation-circle" aria-hidden="true"></i> <?=system_showText(LANG_SITEMGR_COLOR_OPTIONS_TIP);?>
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
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/design.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
