<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/mobile/about/index.php
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

    mixpanel_track("Accessed Branding your App section");

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------

    include(INCLUDES_DIR."/code/mobile_about.php");

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
                <section class="section-heading">
                    <div class="section-heading-content">
                        <h1 class="section-heading-title"><?=system_showText(LANG_SITEMGR_APPBUILDER_ABOUT_TITLE);?> <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="<?=system_showText(LANG_SITEMGR_CONFIGURE_APP_STEP_40_MESSAGE);?>"></span></h1>
                    </div>
                </section>

                <section class="row appbuilder">
                    <div class="appbuilder-container">
                        <?php
                            require(EDIRECTORY_ROOT."/".SITEMGR_ALIAS."/registration.php");
                            require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
                        ?>

                        <section class="container">
                            <form id="step4" name="step4" method="post" enctype="multipart/form-data" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>">
                                <input type="hidden" name="next" id="next" value="no" />

                                <div class="form-left">
                                    <h4><?=system_showText(LANG_SITEMGR_APPBUILDER_ABOUT_PREVIEW);?></h4>
                                    <p><?=system_showText(LANG_SITEMGR_APPBUILDER_ABOUT_TIP);?></p>

                                    <div id="device-apple" class="cover-preview-image device-apple">
                                        <div class="about-preview-image">
                                            <span class="lang-aboutus"><?=system_showText(LANG_MENU_ABOUT)?></span>
                                            <div class="prev-logo">
                                                <?php if (file_exists(EDIRECTORY_ROOT."/".IMAGE_APPBUILDER_PATH."/appbuilder_logo_{$appbuilder_logo_id}.{$appbuilder_logo_extension}")) { ?>
                                                    <img src="<?=DEFAULT_URL."/".IMAGE_APPBUILDER_PATH."/appbuilder_logo_{$appbuilder_logo_id}.{$appbuilder_logo_extension}"?>" />
                                                <?php } else { ?>
                                                    <div class="your-logo"><?=system_showText(LANG_SITEMGR_APPBUILDER_ABOUT_LOGO)?></div>
                                                <?php } ?>
                                            </div>
                                            <span class="customtext <?=($about ? "" : "wireframe")?>">
                                                <?php if ($about) { ?>
                                                <?=nl2br($about);?>
                                                <?php } else { ?>
                                                <div class="your-abouttext"><?=system_showText(LANG_MENU_ABOUT)?></div>
                                                <?php } ?>
                                            </span>
                                            <span class="lang-email"><?=system_showText(LANG_LABEL_EMAIL_ADDRESS)?></span>
                                            <span class="lang-phone"><?=system_showText(LANG_LABEL_PHONE_NUMBER)?></span>
                                            <span class="lang-website"><?=system_showText(LANG_LABEL_WEBSITE)?></span>
                                        </div>

                                        <div class="change-device">
                                            <a class="icon-device-apple active" href="javascript:void(0);"></a>
                                            <a class="icon-device-android" href="javascript:void(0);"></a>
                                        </div>
                                    </div>

                                    <div id="device-android" class="cover-preview-image device-android" style="display:none;">
                                        <div class="about-preview-image">
                                            <span class="lang-aboutus"><?=system_showText(LANG_MENU_ABOUT)?></span>
                                            <div class="prev-logo">
                                                <?php if (file_exists(EDIRECTORY_ROOT."/".IMAGE_APPBUILDER_PATH."/appbuilder_logo_{$appbuilder_logo_id}.{$appbuilder_logo_extension}")) { ?>
                                                    <img src="<?=DEFAULT_URL."/".IMAGE_APPBUILDER_PATH."/appbuilder_logo_{$appbuilder_logo_id}.{$appbuilder_logo_extension}"?>" />
                                                <?php } else { ?>
                                                    <div class="your-logo"><?=system_showText(LANG_SITEMGR_APPBUILDER_ABOUT_LOGO)?></div>
                                                <?php } ?>
                                            </div>
                                            <span class="customtext <?=($about ? "" : "wireframe")?>">
                                                <?php if ($about) { ?>
                                                <?=nl2br($about);?>
                                                <?php } else { ?>
                                                <div class="your-abouttext"><?=system_showText(LANG_MENU_ABOUT)?></div>
                                                <?php } ?>
                                            </span>
                                            <span class="lang-email"><?=system_showText(LANG_LABEL_EMAIL_ADDRESS)?></span>
                                            <span class="lang-phone"><?=system_showText(LANG_LABEL_PHONE_NUMBER)?></span>
                                            <span class="lang-website"><?=system_showText(LANG_LABEL_WEBSITE)?></span>
                                        </div>

                                        <div class="change-device">
                                            <a class="icon-device-apple" href="javascript:void(0);"></a>
                                            <a class="icon-device-android active" href="javascript:void(0);"></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-right">
                                    <h4><?=system_showText(LANG_SITEMGR_APPBUILDER_ABOUT_CONFIG);?></h4>
                                    <p><?=system_showText(LANG_SITEMGR_APPBUILDER_ABOUT_CONFIG_TIP);?></p>

                                    <div>
                                        <label for="logo"><?=system_showText(LANG_SITEMGR_LOGO_IMAGE)?> (430 x 320 pixels)</label>
                                        <div class="row">
                                        <div class="col-xs-6"><input id="image" name="image" type="file" onchange="sendFile();" class="filestyle upload-files file-noinput"><br></div>
                                        <div class="col-xs-6"> <div id="loading_image" class="loading-image hidden"><img src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/loading-32.gif" alt="<?=system_showText(LANG_SITEMGR_WAITLOADING);?>" title="<?=system_showText(LANG_SITEMGR_WAITLOADING);?>"/></div></div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="textarea"><?=system_showText(LANG_SITEMGR_ABOUT_TEXT)?></label>
                                        <textarea class="form-control" cols="40" rows="8" name="about" id="textarea" onkeyup="updateAbout();"><?=$about;?></textarea>
                                    </div>

                                    <div>
                                        <label for="email"><?=system_showText(LANG_LABEL_EMAIL_ADDRESS)?></label>
                                        <input class="form-control" type="email" name="email" id="email" value="<?=$email;?>" />
                                    </div>

                                    <div>
                                        <label for="phone"><?=system_showText(LANG_LABEL_PHONE_NUMBER)?></label>
                                        <input class="form-control" type="phone" name="phone" id="phone" value="<?=$phone;?>" />
                                    </div>

                                    <div>
                                        <label for="website"><?=system_showText(LANG_LABEL_WEBSITE)?></label>
                                        <input class="form-control" type="url" name="website" id="website" value="<?=$website;?>" />
                                    </div>
                                </div>

                                <br class="clearfix"><br />

                                <div class="action">
                                    <button type="button" class="btn btn-success" onclick="JS_submit(true);"><?=system_showText(LANG_SITEMGR_SAVE_CHANGES)?></button>
                                </div>
                            </form>
                        </section>
                    </div>
                </section>
            </div>
        </div>
    </main>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/appbuilder.php";
	include(SM_EDIRECTORY_ROOT."/layout/footer.php");
