<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/layout/sidebar-content.php
# ----------------------------------------------------------------------------------------------------
?>

<div class="submenu-item" id="dashboard-content" <?=(string_strpos($_SERVER['PHP_SELF'], '/content/') !== false ? "style='display: block;'" : '')?>>
    <div class="submenu-title"><?=system_showText(LANG_LABEL_CONTENT); ?></div>
    <div class="submenu-list">
        <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .LISTING_FEATURE_FOLDER. '/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/content/' .LISTING_FEATURE_FOLDER. '/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_NAVBAR_LISTING);?></a>
        <?php HookFire('sidebarcontent_after_render_listingitem'); ?>

        <?php if (PROMOTION_FEATURE == 'on' && CUSTOM_HAS_PROMOTION == 'on' && CUSTOM_PROMOTION_FEATURE == 'on') { ?>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .PROMOTION_FEATURE_FOLDER. '/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/content/' .PROMOTION_FEATURE_FOLDER) !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_NAVBAR_PROMOTION);?></a>
        <?php } ?>
        <?php HookFire('sidebarcontent_after_render_dealitem'); ?>

        <?php if (EVENT_FEATURE == 'on' && CUSTOM_EVENT_FEATURE == 'on') { ?>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .EVENT_FEATURE_FOLDER. '/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/content/' .EVENT_FEATURE_FOLDER) !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_NAVBAR_EVENT);?></a>
        <?php } ?>
        <?php HookFire('sidebarcontent_after_render_eventitem'); ?>

        <?php if (CLASSIFIED_FEATURE == 'on' && CUSTOM_CLASSIFIED_FEATURE == 'on') { ?>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .CLASSIFIED_FEATURE_FOLDER. '/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/content/' .CLASSIFIED_FEATURE_FOLDER) !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_NAVBAR_CLASSIFIED);?></a>
        <?php } ?>
        <?php HookFire('sidebarcontent_after_render_classifieditem'); ?>

        <?php if (ARTICLE_FEATURE == 'on' && CUSTOM_ARTICLE_FEATURE == 'on') { ?>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .ARTICLE_FEATURE_FOLDER. '/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/content/' .ARTICLE_FEATURE_FOLDER) !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_NAVBAR_ARTICLE);?></a>
        <?php } ?>
        <?php HookFire('sidebarcontent_after_render_articleitem'); ?>

        <?php if (BLOG_FEATURE == 'on' && CUSTOM_BLOG_FEATURE == 'on') { ?>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .BLOG_FEATURE_FOLDER. '/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/content/' .BLOG_FEATURE_FOLDER) !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_BLOG_SING);?></a>
        <?php } ?>
        <?php HookFire('sidebarcontent_after_render_blogitem'); ?>

        <?php if (BANNER_FEATURE == 'on' && CUSTOM_BANNER_FEATURE == 'on') { ?>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .BANNER_FEATURE_FOLDER. '/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/content/' .BANNER_FEATURE_FOLDER) !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_NAVBAR_BANNER);?></a>
        <?php } ?>
        <?php HookFire('sidebarcontent_after_render_banneritem'); ?>

        <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/faq/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/content/faq') !== false ? 'is-active' : '')?>"><?=string_ucwords(system_showText(LANG_SITEMGR_MENU_FAQ))?></a>
        <?php HookFire('sidebarcontent_after_render_faqitem'); ?>

        <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/import/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/content/import/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_CONTENT_IMPORT);?></a>
        <?php HookFire('sidebarcontent_after_render_importitem'); ?>

        <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/export/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/content/export/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_CONTENT_EXPORT);?></a>
        <?php HookFire('sidebarcontent_after_render_exportitem'); ?>
    </div>
</div>
