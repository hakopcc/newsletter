<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /ed-admin/design/page-editor/index.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------

    include '../../../conf/loadconfig.inc.php';

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSMSession();
    permission_hasSMPerm();

    mixpanel_track('Accessed section Page Editor');

    # ----------------------------------------------------------------------------------------------------
    # CODE
    # ----------------------------------------------------------------------------------------------------
    $container = SymfonyCore::getContainer();
    $widgetService = $container->get('widget.service');
    $pageService = $container->get('page.service');

    $translator = $container->get('translator');
    setting_get('sitemgr_language', $sitemgr_language);
    $sitemgrLanguage = substr($sitemgr_language, 0, 2);

    # ----------------------------------------------------------------------------------------------------
    # DELETE
    # ----------------------------------------------------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //Delete item
        if ($_POST['action'] === 'delete') {
            $pageService->deletePage($_POST['id']);
            $deletedMessage = LANG_SITEMGR_PAGE_DELETED;
        }
    }

    // Get All pages
    $pages = $pageService->getAllPages();

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
            <div class="main-content wysiwyg" content-full="true">
                <?php
                    require(SM_EDIRECTORY_ROOT."/registration.php");
                    require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
                ?>

                <section class="section-heading">
                    <div class="section-heading-content">
                        <h1 class="section-heading-title"><?= system_showText(LANG_SITEMGR_PAGE_EDITOR) ?> <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="<?= system_showText(LANG_SITEMGR_PAGE_EDITOR_TIP); ?>"></span></h1>
                    </div>
                    <div class="section-heading-actions">
                        <a class="btn btn-primary addNewPageButton" data-domain="<?= SELECTED_DOMAIN_ID ?>" href="#"><?= system_showText(LANG_SITEMGR_PAGE_ADD) ?></a>
                    </div>
                </section>

                <div class="card-list" data-module="page-editor">
                    <?php
                        /* @var $page \ArcaSolutions\WysiwygBundle\Entity\Page */
                        if ($pages) {
                            foreach ($pages as $page) {
                    ?>
                    <div class="card-item" data-page-type="<?=$page->getPageType()->getTitle()?>">
                        <div class="card-title"><?= /** @Ignore */ $translator->trans($page->getTitle(), [], 'widgets', /** @Ignore */ $sitemgrLanguage) ?></div>
                        <div class="card-action">
                            <a href="custom.php?id=<?= $page->getId() ?>" class="card-button card-button-edit"><?= system_showText(LANG_SITEMGR_EDIT) ?></a>
                            <?php if (!in_array($page->getPageType()->getTitle(), $container->get('pagetype.service')->pageViewNotAllowed, true) or $page->getPageType()->getTitle() === \ArcaSolutions\WysiwygBundle\Entity\PageType::HOME_PAGE) { ?>
                                <a href="<?= $pageService->getActiveHostFinalPageUrl($page) ?>" class="card-button card-button-view" target="_blank"><?= system_showText(LANG_SITEMGR_VIEW) ?></a>
                            <?php } ?>
                        </div>
                        <?php if ($page->getPageType()->getTitle() === \ArcaSolutions\WysiwygBundle\Entity\PageType::CUSTOM_PAGE) { ?>
                            <button class="btn card-button-remove" type="button" data-toggle="modal" data-target="#modal-delete" onclick="$('#delete-id').val(<?= $page->getId(); ?>); $('#item-type').val('page')">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </button>
                        <?php } ?>
                    </div>
                    <?php
                            }
                        }
                    ?>
                    <a href="javascript:void(0)" class="card-item-placeholder addNewPageButton" data-domain="<?= SELECTED_DOMAIN_ID ?>">
                        <span><i class="fa fa-plus-circle"aria-hidden="true"></i> <?= system_showText(LANG_SITEMGR_PAGE_ADD) ?></span>
                    </a>
                </div>
                <br>
            </div>
        </div>
    </main>
<?php
    include INCLUDES_DIR.'/modals/modal-delete.php';

    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/pages.php';

    include SM_EDIRECTORY_ROOT.'/layout/footer.php';
?>

<script type="text/javascript" src="<?= DEFAULT_URL ?>/scripts/listingCards.js"></script>
