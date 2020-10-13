<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /ed-admin/content/listing/template/index.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include '../../../../conf/loadconfig.inc.php';

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSMSession();
    permission_hasSMPerm();

    mixpanel_track('Accessed section Template Editor');

    # ----------------------------------------------------------------------------------------------------
    # CODE
    # ----------------------------------------------------------------------------------------------------
    $container = SymfonyCore::getContainer();
    $listingTemplateService = $container->get('listingtemplate.service');

    // Get all templates
    $listingTemplates = $listingTemplateService->getAllListingTemplates();

    // Get all active templates
    $activeTemplates = $listingTemplateService->getAllActivesTemplates();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if($_POST["action"]=="delete"){
            $listingTemplateService->deleteListingTemplate($_POST['id']);
            header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/content/listing/template/index.php?deletedSuccessfully=1");
            exit;
        }
    }
    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    include SM_EDIRECTORY_ROOT . '/layout/header.php';

?>
    <main class="main-dashboard wysiwyg" id="view-content-list">
        <div id="loading_ajax" class="alert alert-loading alert-loading-fullscreen" style="display: none;">
            <img src="<?= DEFAULT_URL; ?>/<?= SITEMGR_ALIAS ?>/assets/img/loading-128.gif" class="alert-img-center">
        </div>

        <nav class="main-sidebar">

            <?php include(SM_EDIRECTORY_ROOT. '/layout/sidebar-dashboard.php'); ?>

            <div class="sidebar-submenu">
                <?php include(SM_EDIRECTORY_ROOT. '/layout/sidebar.php'); ?>
            </div>

        </nav>

        <div class="main-wrapper">
            <?php
            include(SM_EDIRECTORY_ROOT. '/layout/navbar.php');
            include(SM_EDIRECTORY_ROOT. '/layout/submenu-content.php');
            ?>

            <div class="main-content" content-full="false">

                <input  type="hidden" id="custId" name="custId" value="">

                <div class="card-list" data-module="listing-template">

                    <?php if(!empty($listingTemplates)) {

                        foreach ($listingTemplates as $listingTemplate) { ?>

                            <div class="card-item" card-enabled="<?=$listingTemplate->getStatus() === 'disabled' ? 'false' : 'true';?>">
                                <div class="card-title">
                                    <?=$listingTemplate->getTitle()?>
                                    <?php if($listingTemplate->getStatus() === 'disabled'){ ?>
                                        <span>(<?=system_showText(LANG_SITEMGR_DISABLED);?>)</span>
                                    <?php } ?>
                                </div>
                                <div class="card-action">
                                    <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .LISTING_FEATURE_FOLDER.'/template/listing-template.php?id='.$listingTemplate->getId()?>" class="card-button card-button-edit"><?= system_showText(LANG_SITEMGR_EDIT) ?></a>
                                </div>
                                <?php if (count($listingTemplates) !== 1) { ?>
                                    <?php if (count($activeTemplates) !== 1 || (count($activeTemplates) === 1 && $listingTemplate->getStatus() === 'disabled')) { ?>
                                        <button class="btn card-button-remove delete" type="button" data-ref= "<?=$listingTemplate->getId()?>" data-toggle="modal" >
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>
                                    <?php } else {?>
                                        <!--disable delete button -->
                                        <button class="btn card-button-remove disable" type="button" data-ref="<?=$listingTemplate->getId()?>" data-toggle="modal" >
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    <?php } ?>

                    <a href="javascript:void(0)" class="card-item-placeholder addNewListingTemplate" data-domain="<?= SELECTED_DOMAIN_ID ?>">
                        <span><i class="fa fa-plus-circle" aria-hidden="true"></i> <?= system_showText(LANG_SITEMGR_ADD_LISTING_TEMPLATE) ?></span>
                    </a>
                </div>
            </div>
        </div>
    </main>


<?php
    include INCLUDES_DIR . '/modals/modal-delete-template.php';
    include INCLUDES_DIR . '/modals/modal-delete.php';

    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT. '/assets/custom-js/listing-template.php';
    include SM_EDIRECTORY_ROOT . '/layout/footer.php';

