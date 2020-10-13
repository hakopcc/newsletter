<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/sidebar-promote.php
	# ----------------------------------------------------------------------------------------------------

?>
    <div class="submenu-item" id="dashboard-promote" <?=(string_strpos($_SERVER['PHP_SELF'], '/promote/') !== false ? "style='display: block;'" : '')?>>
        <div class="submenu-title"><?=system_showText(LANG_SITEMGR_PROMOTE);?></div>
        <div class="submenu-list">
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/promote/seo-center/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/promote/seo-center') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_CONTENT_SEOCENTER);?></a>
            <? if (MAIL_APP_FEATURE == 'on') { ?>
                <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/promote/newsletter/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/promote/newsletter') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_MAILAPP_NEWSLETTER_SING);?></a>
                <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/promote/mailing-list/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/promote/mailing-list') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_MAILINGLIST);?></a>
            <? } ?>
            <?php if ((CREDITCARDPAYMENT_FEATURE === 'on' || PAYMENT_INVOICE_STATUS === 'on') && PAYMENTSYSTEM_FEATURE === 'on') { ?>
                <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/promote/promotions/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/promote/promotions') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_PROMO_PACK);?></a>
            <?php } ?>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/promote/awards/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/promote/awards') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_AWARD_BADGE);?></a>
        </div>
    </div>
