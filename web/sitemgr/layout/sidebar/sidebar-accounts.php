<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/sidebar-accounts.php
	# ----------------------------------------------------------------------------------------------------

?>

    <div class="submenu-item" id="dashboard-user-accounts" <?=((string_strpos($_SERVER['PHP_SELF'], '/account/') !== false and string_strpos($_SERVER['PHP_SELF'], '/account/myaccount.php') === false) ? "style='display: block;'" : '')?>>
        <div class="submenu-title"><?=system_showText(LANG_SITEMGR_LABEL_USER_ACCOUNTS);?></div>
        <div class="submenu-list">
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/account/sponsor/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/account/sponsor/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_ACC_SPONSOR);?></a>

            <?php if (SOCIALNETWORK_FEATURE == 'on') { ?>
                <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/account/visitor/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/account/visitor/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_ACC_VISITOR);?></a>
            <?php } ?>

            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/account/manager/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/account/manager/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_NAVBAR_SITEMGRACCOUNTS);?></a>
        </div>
    </div>>
