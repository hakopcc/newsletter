<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/sidebar-activity.php
	# ----------------------------------------------------------------------------------------------------

?>
    <div class="submenu-item" id="dashboard-activity" <?=(string_strpos($_SERVER['PHP_SELF'], '/activity/') !== false ? "style='display: block;'" : '')?>>
        <div class="submenu-title"><?=system_showText(LANG_SITEMGR_ACTIVITY);?></div>
        <div class="submenu-list">
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/activity/reports/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/activity/reports/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_REPORTS);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/activity/leads/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/activity/leads/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_LEADS);?></a>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/activity/reviews-comments/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/activity/reviews-comments/') !== false ? 'is-active' : '')?>"><?= ucfirst(system_showText(LANG_SITEMGR_REVIEWS)) ?></a>
            <?php if ((CREDITCARDPAYMENT_FEATURE === 'on' || PAYMENT_INVOICE_STATUS === 'on' || PAYMENT_MANUAL_STATUS === 'on') && PAYMENTSYSTEM_FEATURE === 'on') { ?>
                <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/activity/transactions/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/transactions/') !== false || string_strpos($_SERVER['PHP_SELF'], '/invoices/') !== false  || string_strpos($_SERVER['PHP_SELF'], '/custominvoices/') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_REVENUE_REPORTS);?></a>
            <?php } ?>
            <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/activity/claim/' ?>" class="submenu-link <?=(string_strpos($_SERVER['PHP_SELF'], '/activity/claim/') !== false ? 'is-active' : '')?>"><?= ucfirst(system_showText(LANG_SITEMGR_CLAIM)) ?></a>
        </div>
    </div>
