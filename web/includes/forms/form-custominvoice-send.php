<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/forms/form-custominvoices-send.php
	# ----------------------------------------------------------------------------------------------------

?>
<input type="hidden" name="id" value="<?=$id?>" />

<div class="col-md-8 col-sm-offset-2">
	<?php MessageHandler::render(false, false, true); ?>

    <div class="panel panel-default">
		<div class="panel-body">
			<div class="row">
				<div class="form-group col-sm-6">
					<label for='from'><?=system_showText(LANG_SITEMGR_LABEL_FROM)?></label>
					<input id='from' class="form-control" type="text" name="from" value="<?=$sitemgr_email?>" readonly/>
				</div>
				<div class="form-group col-sm-6">
					<label for='to'><?=system_showText(LANG_SITEMGR_CUSTOMINVOICE_TO)?></label>
					<input id='to' class="form-control" type="text" name="to" value="<?=$contact->getString("email")?>" readonly/>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-sm-6">
					<label for='cc'><?=system_showText(LANG_SITEMGR_LABEL_CC)?></label>
					<input id='cc' class="form-control" type="email"  name="cc" maxlength="255" value="<?=$cc;?>" />
				</div>
				<div class="form-group col-sm-6">
					<label for='bcc'><?=system_showText(LANG_SITEMGR_LABEL_BCC)?></label>
					<input id='bcc' class="form-control" type="email" name="bcc" value="<?=$bcc?>" maxlength="255" />
				</div>
			</div>
			<div class="row">
				<div class="form-group col-sm-12">
					<label for='subject'><?=system_showText(LANG_SITEMGR_LABEL_SUBJECT)?></label>
					<input id='subject' class="form-control" type="text"  name="subject" value="<?=$subject?>" maxlength="255"/>
				</div>
			</div>
			<div class="row">
                <div class="col-sm-12">
                    <label for='body_message'><?=system_showText(LANG_SITEMGR_LABEL_BODY)?></label>
                    <? if ($content_type == "text/plain") { ?>
                        <textarea id="body_message" class="form-control" name="body_message" rows="15"><?=$body?></textarea>
                    <? } ?>

                    <? if ($content_type == "text/html") {
                        // calling CKEditor
                        setting_get('sitemgr_language', $lang);
                        system_addCKEditor("body_message", $body, 30, 15, $lang);
                    } ?>
				</div>
			</div>
		</div>
	</div>
</div>
