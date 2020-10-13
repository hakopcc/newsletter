<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/modals/modal-delete-template.php
	# ----------------------------------------------------------------------------------------------------

?>

    <div class="modal fade custom-modal-wrapper" id="remove-not-linked-listing" tabindex="-1" role="dialog" aria-labelledby="modal-delete" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" modal-type="attention">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <div class="modal-header">
                    <h4 class="modal-title"><?=system_showText(LANG_SITEMGR_ATTENTION);?></h4>
                </div>
                <div class="modal-body">
                    <?=system_showText(LANG_SITEMGR_UNLINK_LINKED_LISTINGS);?>
                </div>
                <div class="modal-footer">
                    <button id="delete-item" type="button" class="btn btn-primary modify-listing"><?=system_showText(LANG_SITEMGR_LETS_DO_THIS);?></button>
                    <button type="button" class="btn" data-dismiss="modal"><?=system_showText(LANG_CANCEL)?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade custom-modal-wrapper" id="remove-linked-listing" tabindex="-1" role="dialog" aria-labelledby="modal-delete" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" modal-type="remove-linked-listing">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <div class="modal-header">
                    <span class="alert-icon"><i class="fa fa-exclamation"></i></span>
                    <h4 class="modal-title"><?=system_showText(LANG_SITEMGR_ATTENTION);?></h4>
                </div>
                <div class="modal-body">
                    <div class="modal-description">
                        <div id="listingText">
                            x
                        </div>
                    </div>
                    <div class="modal-actions">
                        <div class="action-item modify-listing">
                            <strong><?=system_showText(LANG_SITEMGR_MODIFY);?></strong>
                            <span><?=system_showText(LANG_SITEMGR_UNLINK_LINKED_LISTINGS);?></span>
                        </div>
                        <div class="action-item disable-listing">
                            <strong><?=system_showText(LANG_SITEMGR_DISABLE);?></strong>
                            <span><?=system_showText(LANG_SITEMGR_DISABLE_LINKED_LISTINGS);?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
