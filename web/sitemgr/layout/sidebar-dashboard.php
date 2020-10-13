<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/sidebar-dashboard.php
	# ----------------------------------------------------------------------------------------------------

    $symfonyKernel = SymfonyCore::getKernel();
?>
    <div class="sidebar-menu <?=((string_strpos($_SERVER["PHP_SELF"], "/sitemgr/index.php") === false && string_strpos($_SERVER["PHP_SELF"], "/account/myaccount.php") === false && string_strpos($_SERVER["PHP_SELF"], "/sites/") === false) ? "is-closed" : "")?>">
        <a href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/" class="sidebar-link <?=(string_strpos($_SERVER["PHP_SELF"], "/sitemgr/index.php") !== false ? "is-actived" : "")?>" id="tour-dashboard" data-target="dashboard-home">
            <img src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/dashboard/icon-dashboard.svg" class="navbar-icon">
            <?=system_showText(LANG_SITEMGR_DASHBOARD);?>
        </a>
        <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_CONTENT)) { ?>
            <a href="javascript:void(0);" class="sidebar-link <?=(string_strpos($_SERVER["PHP_SELF"], "/content/") !== false ? "is-actived" : "")?>" id="tour-content" data-target="dashboard-content">
                <img src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/dashboard/icon-content.svg" class="navbar-icon">
                <?=system_showText(LANG_LABEL_CONTENT); ?>
            </a>
        <?php } ?>
        <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_DESIGN)) { ?>
            <a href="javascript:void(0);" class="sidebar-link <?=(string_strpos($_SERVER["PHP_SELF"], "/design/") !== false ? "is-actived" : "")?>" id="tour-design" data-target="dashboard-design">
                <img src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/dashboard/icon-design.svg" class="navbar-icon">
                <?=system_showText(LANG_SITEMGR_LABEL_DESIGN);?>
            </a>
        <?php } ?>
        <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_ACTIVITY)) { ?>
            <a href="javascript:void(0);" class="sidebar-link <?=(string_strpos($_SERVER["PHP_SELF"], "/activity/") !== false ? "is-actived" : "")?>" id="tour-activity" data-target="dashboard-activity">
                <img src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/dashboard/icon-activity.svg" class="navbar-icon">
                <?=system_showText(LANG_SITEMGR_ACTIVITY);?>
            </a>
        <?php } ?>
        <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_PROMOTE)) { ?>
            <a href="javascript:void(0);" class="sidebar-link <?=(string_strpos($_SERVER["PHP_SELF"], "/promote/") !== false ? "is-actived" : "")?>" id="tour-promote" data-target="dashboard-promote">
                <img src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/dashboard/icon-promote.svg" class="navbar-icon">
                <?=system_showText(LANG_SITEMGR_PROMOTE);?>
            </a>
        <?php } ?>
        <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_CONFIG)) { ?>
            <a href="javascript:void(0);" class="sidebar-link <?=(string_strpos($_SERVER["PHP_SELF"], "/configuration/") !== false ? "is-actived" : "")?>" id="tour-settings" data-target="dashboard-settings">
                <img src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/dashboard/icon-settings.svg" class="navbar-icon">
                <?=system_showText(LANG_LABEL_ACCOUNT_SETTINGS);?>
            </a>
        <?php } ?>
        <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_ACCOUNTS)) { ?>
            <a href="javascript:void(0);" class="sidebar-link <?=((string_strpos($_SERVER["PHP_SELF"], "/account/") !== false and string_strpos($_SERVER["PHP_SELF"], "/account/myaccount.php") === false) ? "is-actived" : "")?>" id="tour-user-accounts" data-target="dashboard-user-accounts">
                <img src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/dashboard/icon-user-accounts.svg" class="navbar-icon">
                <?=system_showText(LANG_SITEMGR_LABEL_USER_ACCOUNTS);?>
            </a>
        <?php } ?>
        <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_MOBILE)) { ?>
            <a href="javascript:void(0);" class="sidebar-link <?=(string_strpos($_SERVER["PHP_SELF"], "/mobile/") !== false ? "is-actived" : "")?>" id="tour-mobile-apps" data-target="dashboard-mobile-app">
                <img src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/dashboard/icon-mobile-apps.svg" class="navbar-icon">
                <?=system_showText(LANG_SITEMGR_MOBILE_APPS);?>
            </a>
        <?php } ?>

        <?php if (BRANDED_PRINT == "on") { ?>
            <div class="powered-by">
                <span><?=system_showText(LANG_POWEREDBY)?></span>
                <a href="http://www.edirectory.com<?=(string_strpos($_SERVER["HTTP_HOST"], ".com.br") !== false ? ".br" : "")?>" target="_blank">eDirectory <span>Cloud Service</span></a>
                <span><?=$symfonyKernel::VERSION?> &copy; Arca Solutions Inc.</span>
            </div>
        <?php } ?>
    </div>
