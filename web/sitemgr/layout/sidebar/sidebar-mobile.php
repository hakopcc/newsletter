<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/sidebar-mobile.php
	# ----------------------------------------------------------------------------------------------------

?>
    <div class="submenu-item" id="dashboard-mobile-app" <?=(string_strpos($_SERVER['PHP_SELF'], '/mobile/') !== false ? "style='display: block;'" : '')?>>
        <div class="submenu-title"><?=system_showText(LANG_SITEMGR_MOBILE_APPS);?></div>
        <div class="submenu-list">
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/mobile/appbuilder/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/mobile/appbuilder') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_BUILD_YOUR_APP);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/mobile/about/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/mobile/about') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_CONFIGURE_ABOUT_PAGE);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/mobile/custompages/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/mobile/custompages') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_CUSTOMPAGES);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/mobile/menu/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/mobile/menu') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_CONFIGURE_MENU);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/mobile/slider/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/mobile/slider') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_NAVBAR_SLIDER);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/mobile/promote-apps/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/mobile/promote-apps') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_PROMOTE_APPS);?></a>
        </div>
    </div>
