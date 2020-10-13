<?php
    $container = SymfonyCore::getContainer();

    $modalListings = $container->get('listing.service')->getOrderedListings(10, $acctId);
?>

<div class="modal fade" id="modal-linked-listing" tabindex="-1" role="dialog" aria-labelledby="modal-setting" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content ">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only"><?=LANG_CLOSE?></span></button>
                <h4 class="modal-title"><?=LANG_ADD_LISTING?></h4>
            </div>
            <div class="modal-body">
                <div class="search-listing-linked">
                    <i class="fa fa-search"></i>
                    <input type="text" class="form-control" data-accountId="<?=$acctId?>" id="linkedlisting-search" placeholder="<?=LANG_BUTTON_SEARCH?>">
                </div>
                <div class="linked-listing-list" id="listing-container">
                    <?php foreach ($modalListings as $listing) {
                        $addedListing = in_array($listing['id'], $listingsIds, true);
                        ?>
                        <div class="list-item">
                            <span><?=$listing['title']?></span>
                            <a href="javascript:void(0)" class="addListing" data-id="<?=$listing['id']?>" data-title="<?=$listing['title']?>" <?=!$addedListing ? '' : 'style="display: none;"'?>>
                                <i class="fa fa-plus"></i>
                            </a>
                            <a href="javascript:void(0)" class="removeListing" data-id="<?=$listing['id']?>" <?=$addedListing ? '' : 'style="display: none;"'?>>
                                <i class="fa fa-minus"></i>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button button-md is-secondary btn btn-default" data-dismiss="modal"><?=LANG_CANCEL?></button>
            </div>
        </div>
    </div>
</div>
