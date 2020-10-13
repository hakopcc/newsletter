<div class="modal fade custom-modal-wrapper" id="remove-tab-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog">
        <div class="modal-content" modal-type="danger">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= system_showText(LANG_SITEMGR_DELETE). ' ' .system_showText(LANG_SITEMGR_LISTINGTEMPLATE_TAB) ?></h4>
            </div>
            <div class="modal-body">
                <p><?= system_showText(LANG_SITEMGR_MSGAREYOUSURE) ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger confirmRemoval"><?= system_showText(LANG_QUICKLIST_REMOVE); ?></button>
                <button type="button" class="btn" data-dismiss="modal"><?= system_showText(LANG_SITEMGR_CANCEL); ?></button>
            </div>
        </div>
    </div>
</div>
