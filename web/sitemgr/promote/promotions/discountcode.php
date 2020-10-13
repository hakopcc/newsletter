<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /ed-admin/promote/promotions/discountcode.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include '../../../conf/loadconfig.inc.php';

    # ----------------------------------------------------------------------------------------------------
    # VALIDATE FEATURE
    # ----------------------------------------------------------------------------------------------------
    if (PAYMENT_FEATURE !== 'on') {
        header('Location:'.DEFAULT_URL.'/'.SITEMGR_ALIAS.'');
        exit;
    }
    if ((CREDITCARDPAYMENT_FEATURE !== 'on') && (PAYMENT_INVOICE_STATUS !== 'on')) {
        header('Location:'.DEFAULT_URL.'/'.SITEMGR_ALIAS.'');
        exit;
    }
    if (PAYMENTSYSTEM_FEATURE !== 'on') {
        exit;
    }

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSMSession();
    permission_hasSMPerm();

    mixpanel_track(($id ? 'Edited an existing discount code' : 'Added a new discount code'));

    # ----------------------------------------------------------------------------------------------------
    # AUX
    # ----------------------------------------------------------------------------------------------------
    extract($_GET);
    extract($_POST);
    $url_base = ''.DEFAULT_URL.'/'.SITEMGR_ALIAS.'';

    require_once CLASSES_DIR.'/class_StripeInterface.php';
    include EDIRECTORY_ROOT.'/includes/code/discountcode.php';

    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    include SM_EDIRECTORY_ROOT.'/layout/header.php';
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
                <?php
                    require SM_EDIRECTORY_ROOT.'/registration.php';
                    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
                ?>

                <form name="discountcode" role="form" action="<?= system_getFormAction($_SERVER['PHP_SELF']) ?>" method="post">
                    <input type="hidden" name="x_id" value="<?= $x_id ?>"/>

                    <section class="section-heading">
                        <div class="section-heading-content">
                            <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/promote/promotions/"?>" class="heading-back-button"><i class="fa fa-angle-left"></i> <?=system_showText(LANG_SITEMGR_PROMO_PACK);?></a>
                            <?php if ($id) { ?>
                                <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_EDIT));?> <?= string_ucwords(LANG_LABEL_DISCOUNTCODE) ?> <?= $x_id ?></h1>
                            <?php } else { ?>
                                <h1 class="section-heading-title"><?= string_ucwords(system_showText(LANG_SITEMGR_ADD))." ".string_ucwords(LANG_LABEL_DISCOUNTCODE) ?></h1>
                            <?php } ?>
                        </div>
                        <div class="section-heading-actions">
                            <button type="submit" name="submit_button" value="Submit" class="btn btn-primary action-save" data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                        </div>
                    </section>

                    <section class="section-form row">
                        <div class="container">
                            <?php include INCLUDES_DIR.'/forms/form-discountcode.php'; ?>
                        </div>
                    </section>

                    <section class="row footer-action">
                        <div class="container">
                            <div class="col-xs-12 text-right">
                                <a href="<?= DEFAULT_URL.'/'.SITEMGR_ALIAS.'/promote/promotions/' ?>"
                                class="btn btn-default btn-xs"><?= system_showText(LANG_CANCEL) ?></a>
                                <span class="separator"> <?= system_showText(LANG_OR) ?> </span>
                                <button type="submit" name="submit_button" value="Submit" class="btn btn-primary action-save" data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                            </div>
                        </div>
                    </section>
                </form>
            </div>
        </div>
    </main>
<?php
    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/promotion.php';

    include SM_EDIRECTORY_ROOT.'/layout/footer.php';
?>
