<?php

$imgPath = '../../../assets/img/listing-widget-placeholder/' . $section . '/' .system_generateFriendlyURL($listingWidgetTitleImg). '.jpg';
if (!file_exists(EDIRECTORY_ROOT. '/' .SITEMGR_ALIAS. '/assets/img/listing-widget-placeholder/' . $section . '/' .system_generateFriendlyURL($listingWidgetTitleImg). '.jpg')){
    $imgPath = '../../../assets/img/listing-widget-placeholder/' . $section . '/custom-content.jpg';
}

$addWidgetUrl = DEFAULT_URL . '/' . SITEMGR_ALIAS . '/content/listing/template/add-' . $section . '-widget.php?listingTemplateId='.$listingTemplateId . '&listingTemplateTabId=' . $tabId;

?>

<div class="listing-widget-item" id="<?=$section?>-<?= $i ?>">
    <a class="widget-add-before btn-new-widget" data-url="<?=$addWidgetUrl;?>">
        <i class="fa fa-plus-circle"></i>
    </a>

    <div class="edit-info hide" data-modaltype="<?= $listingWidgetModal ?>" data-templatewidget="<?= $listingTemplateWidgetId ?>" data-widgetid="<?= $listingWidgetId ?>"></div>
    <input type="hidden" name="listingWidgetId" value="<?=$listingWidgetId?>">
    <input type="hidden" id="listingTemplateListingWidgetIdInput" name="listingTemplateListingWidgetIdInput" value="<?=$listingTemplateWidgetId?>">
    <input type="hidden" name="listingTemplateTabId" value="<?=$tabId?>">

    <div class="widget-name" data-widget-title><?=$listingWidgetTitle?></div>
    <div class="widget-placeholder <?=$listingWidgetTitle == 'Wide Skyscraper Banner' ? 'crop-placeholder-vertical' : '';?>"><img src="<?=$imgPath?>"></div>

    <div class="widget-actions">
        <div class="widget-actions">
            <div class="widget-action-item widget-move"><i class="fa fa-bars" aria-hidden="true"></i></div>
            <?php if(!empty($listingWidgetModal)) { ?>
                <a href="javascript:void(0)" class="widget-action-item widget-edit editListingWidgetButton" data-tab="<?=$tabId?>"
                    data-divid="<?=$section?>-<?= $i ?>" data-toggle="modal" data-target="#" data-widget="<?=$listingWidgetId?>" data-modal="<?=$listingWidgetModal?>">
                    <i class="fa fa-pencil"></i>
                </a>
            <?php } ?>
                <a href="#" class="widget-action-item widget-remove removeListingWidgetButton"
                    data-divid="<?=$section?>-<?= $i ?>" data-toggle="modal" data-target="#remove-widget-modal">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </a>
        </div>
    </div>
</div>
