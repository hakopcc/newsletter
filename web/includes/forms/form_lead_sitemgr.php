<?php
/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2020 Arca Solutions, Inc. All Rights Reserved.           #
#                                                                    #
# This file may not be redistributed in whole or part.               #
# eDirectory is licensed on a per-domain basis.                      #
#                                                                    #
# ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
#                                                                    #
# http://www.edirectory.com | http://www.edirectory.com/license.html #
######################################################################
\*==================================================================*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/forms/form_lead_sitemgr.php
# ----------------------------------------------------------------------------------------------------
?>

<div class="view-lead">

    <div class="row">
        <div class="col-sm-12">
            <blockquote class="small">
                <p><?=nl2br($prevModule["message"]);?></p>
            </blockquote>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-10">
            <a class="btn btn-xs btn-info" id="linkreply<?=$prevModule["id"];?>" href="javascript: void(0);" onclick="showLead(<?=$prevModule["id"];?>, 'reply');"><?=system_showText(LANG_LABEL_REPLY)?></a>
            <a class="btn btn-xs btn-info" id="linkforward<?=$prevModule["id"];?>" href="javascript: void(0);" onclick="showLead(<?=$prevModule["id"];?>, 'forward');"><?=system_showText(LANG_LABEL_FORWARD)?></a>
        </div>
    </div>

    <div id="reply_lead_<?=$prevModule["id"];?>" style="display:none" class="view-lead-action">
        <hr>
        <form name="formReply" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">

            <input type="hidden" name="item_id" value="<?=$item_id;?>">
            <input type="hidden" name="item_type" value="<?=$item_type;?>">
            <input type="hidden" name="type" value="<?=$item_type;?>">
            <input type="hidden" name="idLead" value="<?=$prevModule["id"];?>">
            <input type="hidden" name="screen" value="<?=$screen;?>">
            <input type="hidden" name="letter" value="<?=$letter;?>">
            <input type="hidden" name="action" value="reply">

            <div class="form-group">
                <label for="in-email"><?=system_showText(LANG_LABEL_TO);?>: </label>
                <input class="form-control" id="in-email" type="email" name="to" value="<?=($to && $action == "reply" && $idLead == $prevModule["id"] ? $to : $prevModule["email"]);?>">
            </div>

            <div class="form-group">
                <label for="in-message"><?=system_showText(LANG_LABEL_MESSAGE);?>:</label>
                <textarea class="form-control" id="in-message" name="message" rows="5"><?=($message && $action == "reply" && $idLead == $prevModule["id"] ? $message : "");?></textarea>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <button type="submit" name="submit" value="Submit" class="btn btn-info btn-sm action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_BUTTON_SEND);?></button>
                    <button type="reset"  name="cancel" value="Cancel" class="btn btn-default btn-sm" onclick="hideLead(<?=$prevModule["id"];?>, 'reply');"><?=system_showText(LANG_BUTTON_CANCEL);?></button>
                </div>
            </div>

        </form>

    </div>

    <div id="forward_lead_<?=$prevModule["id"];?>" style="display:none" class="view-lead-action">
        <hr>
        <form name="formForward" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">

            <input type="hidden" name="item_id" value="<?=$item_id;?>">
            <input type="hidden" name="item_type" value="<?=$item_type;?>">
            <input type="hidden" name="idLead" value="<?=$prevModule["id"];?>">
            <input type="hidden" name="screen" value="<?=$screen;?>">
            <input type="hidden" name="letter" value="<?=$letter;?>">
            <input type="hidden" name="action" value="forward">

            <div class="form-group">
                <label for="for-mail"><?=system_showText(LANG_LABEL_TO);?>: </label>
                <input class="form-control" id="for-mail" type="email" name="to" value="<?=($to && $action == "forward" && $idLead == $prevModule["id"] ? $to : "");?>">
            </div>

            <div class="form-group">
                <label for="for-message"><?=system_showText(LANG_LABEL_MESSAGE);?>: </label>
                <textarea class="form-control" id="for-message" name="message" rows="6"><?=($message && $action == "forward" && $idLead == $prevModule["id"] ? $message : $prevModule["message"]);?></textarea>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <button type="submit" name="submit" value="Submit" class="btn btn-sm btn-info action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_BUTTON_SEND);?></button>
                    <button type="reset"  name="cancel" value="Cancel" class="btn btn-sm btn-default" onclick="hideLead(<?=$prevModule["id"];?>, 'forward');"><?=system_showText(LANG_BUTTON_CANCEL);?></button>
                </div>
            </div>

        </form>

    </div>

</div>
