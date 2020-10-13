<?php
    $imgPath = "../../assets/img/widget-placeholder/".system_generateFriendlyURL($widgetTitleImg).".jpg";
    if (!file_exists(EDIRECTORY_ROOT."/".SITEMGR_ALIAS."/assets/img/widget-placeholder/".system_generateFriendlyURL($widgetTitleImg).".jpg")){
        $imgPath = "../../assets/img/widget-placeholder/custom-content.jpg";
    }

    /* ModStores Hooks */
    HookFire('listwidgets_before_render', [
        'imgPath'      => &$imgPath,
        'widgetId'     => &$widgetId,
        'widgetModal'  => &$widgetModal,
        'pageWidgetId' => &$pageWidgetId,
        'widgetTitle'  => &$widgetTitle,
        'widgetType'   => &$widgetType,
    ]);

    //Widgets that will display the link to the listing template section
    $isDetail = in_array($title, $container->get('widget.service')->details);

    //Widgets that can't be deleted
    $blockRemoval = in_array($title, $container->get('widget.service')->blockRemoval);

?>

<div id="<?= $i ?>">
    <a class="add-plus-circle-widget btn-new-widget"> </a>

    <div class="edit-widget row <?=$isDetail ? 'is-detail' : '';?>">
        <div class="edit-info hide" data-modaltype="<?= $widgetModal ?>" data-pagewidget="<?= $pageWidgetId ?>" data-widgetid="<?= $widgetId ?>" data-title="<?= $widgetTitleImg ?>" data-type="<?= $widgetType ?>"></div>
        <input type="hidden" name="widgetId" value="<?= $widgetId ?>">
        <input type="hidden" id="pageWidgetIdInput" name="pageWidgetId" value="<?= $pageWidgetId ?>">
        <div class="col-md-3 text-left">
            <h4 data-widget-title><?= $widgetTitle ?></h4>
        </div>
        <div class="col-md-6">
            <div class="edit-hover">
                <?php if ($widgetModal) { ?>
                    <a href="/includes/modals/widget/<?=$widgetModal?>.php" class="editWidgetButton" data-divid="<?= $i ?>" data-toggle="modal" data-target="#" data-modal="<?= $widgetModal ?>">
                        <img src="<?= $imgPath ?>"/>
                    </a>
                <?php } else{ ?>
                    <img src="<?= $imgPath ?>"/>
                <?php } ?>

                <?php if ($isDetail){ ?>
                    <a data-mixpanel-event='Clicked on the listing Edit listing templates through the Page Editor section' href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .LISTING_FEATURE_FOLDER. '/template/' ?>" class="btn btn-lg btn-primary" target="_blank"><?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_EDIT);?></a>
                <?php } ?>
            </div>
        </div>
        <div class="col-md-2">
        </div>
        <div class="col-md-1 text-right">
            <i class="fa fa-bars" aria-hidden="true"></i>
            <?php if ($widgetModal) { ?>
                <a href="/includes/code/widgetActionAjax.php" class="editWidgetButton" data-divid="<?= $i ?>" data-toggle="modal" data-target="#" data-modal="<?= $widgetModal ?>">
                    <i class="fa fa-pencil" aria-hidden="true"></i>
                </a>
            <?php } ?>
            <?php if (!$blockRemoval){ ?>
                <a href="#" class="removeWidgetButton" data-divid="<?= $i ?>" data-toggle="modal" data-target="#remove-widget-modal">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </a>
            <?php } ?>
        </div>
    </div>
</div>
