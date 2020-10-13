<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/sidebar-design.php
	# ----------------------------------------------------------------------------------------------------

?>
    <div class="submenu-item" id="dashboard-design" <?=(string_strpos($_SERVER['PHP_SELF'], '/design/') !== false ? "style='display: block;'" : '')?>>
        <div class="submenu-title"><?=system_showText(LANG_SITEMGR_LABEL_DESIGN);?></div>
        <div class="submenu-list">
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/design/page-editor/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/design/page-editor/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_PAGE_EDITOR);?></a>
            <?php HookFire('sidebardesign_after_render_pageeditor'); ?>

            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/design/themes/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/design/themes/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_MENU_THEMES);?></a>
            <?php HookFire('sidebardesign_after_render_themes'); ?>

            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/design/colors-fonts/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/design/colors-fonts/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_COLORS_FONTS);?></a>
            <?php HookFire('sidebardesign_after_render_colorsfonts'); ?>

            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/design/css-editor/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/design/css-editor/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_CSS_EDITOR);?></a>
            <?php HookFire('sidebardesign_after_render_csseditor'); ?>

            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/design/email-editor/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/design/email-editor') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_EMAIL_EDITOR);?></a>
            <?php HookFire('sidebardesign_after_render_emaileditor'); ?>
        </div>
    </div>
