<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/sidebar-support.php
	# ----------------------------------------------------------------------------------------------------

?>
    <div class="submenu-item" id="dashboard-user-accounts" <?=(string_strpos($_SERVER['PHP_SELF'], '/support/') !== false ? "style='display: block;'" : '')?>>
        <div class="submenu-title">Config Checker</div>
        <div class="submenu-list">
            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/support/"?>" class="submenu-link <?=(string_strpos($_SERVER["PHP_SELF"], "/support/index.php") !== false ? "is-active" : "")?>">System Settings</a>
            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/support/reset.php"?>" class="submenu-link <?=(string_strpos($_SERVER["PHP_SELF"], "/support/reset.php") !== false ? "is-active" : "")?>">Reset Settings</a>
            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/support/crontab.php"?>" class="submenu-link <?=(string_strpos($_SERVER["PHP_SELF"], "/support/crontab.php") !== false ? "is-active" : "")?>">Crontab</a>
            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/support/domain.php"?>" class="submenu-link <?=(string_strpos($_SERVER["PHP_SELF"], "/support/domain.php") !== false ? "is-active" : "")?>">Domains</a>
            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/support/import.php"?>" class="submenu-link <?=(string_strpos($_SERVER["PHP_SELF"], "/support/import.php") !== false ? "is-active" : "")?>">Import</a>
            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/support/alias.php"?>" class="submenu-link <?=(string_strpos($_SERVER["PHP_SELF"], "/support/alias.php") !== false ? "is-active" : "")?>">Alias Options</a>
        </div>
    </div>
