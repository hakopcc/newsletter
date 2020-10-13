<?php

use ArcaSolutions\ListingBundle\Entity\LinkedListings;

if(!empty($limit)) { ?>
    <div class="col-sm-12">
        <div class="panel panel-form linked-listing-block">
            <div class="panel-body">
                <div class="linked-listing-container">
                    <?php
                    $i = 0;
                    $listingsIds = [];
                    if(!empty($listings)) {
                        /** @var LinkedListings $listing */
                        foreach($listings as $listing) {
                            $linkedListing = $listing->getLinkedListing();

                            $listingsIds[] = $linkedListing->getId();
                            ?>
                            <div class="linked-list-item is-selected" data-ref="<?=$linkedListing->getId()?>">
                                <span><?=$linkedListing->getTitle()?></span>
                                <a href="javascript:void(0)" class="remove-linked-listing"  data-id="<?=$linkedListing->getId()?>"><i class="fa fa-close"></i></a>
                            </div>
                            <?php
                            $i++;
                        }
                    }
                    for (; $i < $limit; $i++) { ?>
                         <div class="linked-list-item"><i class="fa fa-plus-circle"></i> <?=LANG_ADD_LISTING?></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php }
include(INCLUDES_DIR. '/modals/modal-linked-listing.php'); ?>
