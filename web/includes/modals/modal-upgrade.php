<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2029 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/modals/modal-upgrade.php
	# ----------------------------------------------------------------------------------------------------

?>

<div class="modal fade" id="modal-upgrade" tabindex="-1" role="dialog" aria-labelledby="modal-categories" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="button button-sm is-secondary" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
            </div>
            <div class="modal-body">
                <div class="icon-wrapper">
                    <div class="icon-item">
                        <div class="icon-face icon-face-front"><img src="<?='/assets/images/upgrade-icon-graph.svg';?>" alt="Upgrade Request"></div>
                        <div class="icon-face icon-face-back"><img src="<?='/assets/images/upgrade-icon-checked.svg';?>" alt="Upgrade Sent"></div>
                    </div>
                </div>
                <div class="upgrade-content first-step">
                    <strong><?=system_showText(LANG_LABEL_SEND_UPGRADE_REQUEST)?></strong>
                    <p> <?=system_showText(LANG_LABEL_TO_UPGRADE_YOUR_PLAN)?> <a href="mailto:<?=$account->email ?>"><?=$account->email ?></a></p>
                    <button class="button button-md is-primary action-save" id="upgradeButton" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_BUTTON_SEND)?></button>
                </div>
                <div class="upgrade-content second-step">
                    <strong><?=system_showText(LANG_LABEL_REQUEST_RECEIVED)?></strong>
                    <p><?=system_showText(LANG_LABEL_WE_WILL_ANALYZE)?> <a href="mailto:<?=$account->email ?>"><?=$account->email ?></a></p>
                    <button class="button button-md is-primary" data-dismiss="modal"><?=system_showText(LANG_BUTTON_OK)?></button>
                </div>
            </div>
        </div>
    </div>
</div>
