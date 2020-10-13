<?php
use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
?>

<div class="list-widget-form" id="<?=system_generateFriendlyURL(ListingWidget::CALL_TO_ACTION)?>">
    <div class="widget-form has-scroll">
        <?php include INCLUDES_DIR . '/forms/listing-widget/form-calltoaction.php' ?>
    </div>
</div>

<div class="list-widget-form" id="<?=system_generateFriendlyURL(ListingWidget::SPECIALTIES)?>">
    <div class="widget-form has-scroll">
        <?php include INCLUDES_DIR . '/forms/listing-widget/form-specialties.php' ?>
    </div>
</div>

<div class="list-widget-form" id="<?=system_generateFriendlyURL(ListingWidget::RANGE)?>">
    <div class="widget-form has-scroll">
        <?php include INCLUDES_DIR . '/forms/listing-widget/form-range.php' ?>
    </div>
</div>

<div class="list-widget-form" id="<?=system_generateFriendlyURL(ListingWidget::DESCRIPTION)?>">
    <div class="widget-form has-scroll">
        <?php include INCLUDES_DIR . '/forms/listing-widget/form-description.php' ?>
    </div>
</div>

<div class="list-widget-form" id="<?=system_generateFriendlyURL(ListingWidget::MORE_DETAILS)?>">
    <div class="widget-form has-scroll">
        <?php include INCLUDES_DIR . '/forms/listing-widget/form-moredetails.php' ?>
    </div>
</div>

<div class="list-widget-form" id="<?=system_generateFriendlyURL(ListingWidget::LINKED_LISTINGS)?>">
    <div class="widget-form has-scroll">
        <?php include INCLUDES_DIR . '/forms/listing-widget/form-linkedlistings.php' ?>
    </div>
</div>

<div class="list-widget-form" id="<?=system_generateFriendlyURL(ListingWidget::RELATED_LISTINGS)?>">
    <div class="widget-form has-scroll">
        <?php include INCLUDES_DIR . '/forms/listing-widget/form-relatedlistings.php' ?>
    </div>
</div>

<div class="list-widget-form" id="<?=system_generateFriendlyURL(ListingWidget::CHECK_LIST)?>">
    <div class="widget-form has-scroll">
        <?php include INCLUDES_DIR . '/forms/listing-widget/form-checklist.php' ?>
    </div>
</div>
