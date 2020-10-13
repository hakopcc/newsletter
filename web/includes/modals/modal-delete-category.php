<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/modals/modal-delete-category.php
	# ----------------------------------------------------------------------------------------------------

    /* ModStores Hooks */
    HookFire( "forum_modal_delete", [
        "modalTitle"   => &$modalTitle,
        "modalMessage" => &$modalMessage,
    ]);
?>


    <div class="modal fade custom-modal-wrapper" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="modal-delete" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" modal-type="danger">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <div class="modal-header">
                    <h4 class="modal-title"><?=system_showText(LANG_SITEMGR_CATEGORY_DELETECATEGORY)?></h4>
                </div>
                <div class="modal-body">
                    <?=system_showText(LANG_SITEMGR_CATEGORY_DELETEQUESTION)?>
                </div>
                <div class="modal-footer">
                    <button id="delete-item" type="button" class="btn btn-danger deleteCategory" data-id=""><?=system_showText(LANG_QUICKLIST_REMOVE);?> </button>
                    <button type="button" class="btn" data-dismiss="modal"><?=system_showText(LANG_CANCEL)?></button>
                </div>
            </div>
        </div>
    </div>
