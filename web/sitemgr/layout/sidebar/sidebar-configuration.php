<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/sidebar-configuration.php
	# ----------------------------------------------------------------------------------------------------

?>
    <div class="submenu-item" id="dashboard-settings" <?=(string_strpos($_SERVER['PHP_SELF'], '/configuration/') !== false ? "style='display: block;'" : '')?>>
        <div class="submenu-title"><?=system_showText(LANG_SITEMGR_LABEL_SETTINGS);?></div>
        <div class="submenu-list">
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/configuration/basic-information/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/configuration/basic-information') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_BASIC_INFO);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/configuration/general-settings/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/configuration/general-settings') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_GENERAL_SETTINGS);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/configuration/email/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/configuration/email') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_EMAILSENDINGCONFIGURATION);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/configuration/networking/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/configuration/networking') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_NETWORKING);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/configuration/geography/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/configuration/geography') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_TIME_GEO);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/configuration/payment/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/configuration/payment') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_LEVELS_TAB);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/configuration/google/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/configuration/google') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_INTEGRATION_GOOGLE);?></a>
        </div>
    </div>
