<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /includes/modals/modal-reset-listingtemplate.php
    # ----------------------------------------------------------------------------------------------------
?>

<div class="modal fade custom-modal-wrapper" id="modal-reset-listingtemplate" role="dialog" aria-labelledby="modal-delete" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" modal-type="danger">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <div class="modal-header">
                <h4 class="modal-title"><?= system_showText(LANG_SITEMGR_RESET_LISTINGTEMPLATE) ?></h4>
            </div>
            <div class="modal-body">
                <form role="form" name="form_reset_listingtemplate" id="form_reset_listingtemplate" action="<?= system_getFormAction($_SERVER['PHP_SELF']) ?>?id=<?= $listingTemplateId ?>" method="post">
                    <input type="hidden" name="resetListingTemplateId" value="<?= $listingTemplateId ?>">
                    <input type="hidden" name="action" value="reset">
                    <p><?= system_showText(LANG_SITEMGR_RESET_LISTINGTEMPLATE_DESCRIPTION) ?></p>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary confirmation-reset-listingtemplate" onclick="$('#form_reset_listingtemplate').submit();"><?= system_showText(LANG_BUTTON_YES_CONTINUE) ?></button>
                <button type="button" class="btn" data-dismiss="modal"><?= system_showText(LANG_CANCEL)?></button>
            </div>
        </div>
    </div>
</div>
