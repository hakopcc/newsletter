<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/forms/form-support-reset.php
	# ----------------------------------------------------------------------------------------------------

?>

    <div class="col-md-9">

        <div class="panel panel-default ">
            <div class="panel-heading">Sitemgr Password</div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6"><input type="email" name="sitemgrusername" class="form-control" value="<?=$sm_username?>" placeholder="Username"></div>
                    <div class="col-md-6"><input type="password" name="sitemgrpass" class="form-control" placeholder="New password"></div>
                </div>
                <input type="hidden" name="action" value="sitemgr">
            </div>
            <div class="panel-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>

        <div class="panel panel-default ">
            <div class="panel-heading">Language File</div>
            <div class="panel-body">
                Rebuild language file (<i>custom/domain_<?=SELECTED_DOMAIN_ID?>/lang/language.inc.php</i>)
                <p class="help-block"><small>This may solve some problems related to the language files. Sometimes an issue may occur when copying them over to the /custom folder, so it might be necessary to run this tool to copy them again.</small></p>
            </div>
            <div class="panel-footer">
                <button type="button" class="btn btn-primary <?=$classLang?>" <?=$onclickLang?>><?=($classLang ? "Updated!" : "Reset Settings")?></button>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">Sign In Options - Current Values:</div>
            <div class="panel-body">
                <p><strong>Google Account: </strong><?=$foreignaccount_google ? "<strong style=\"color: green\">ON</strong>" : "<strong style=\"color: red\">OFF</strong>"?>  <small>Turns this sign in option ON/FF.</small></p>
                <p><strong>Facebook: </strong><?=$foreignaccount_facebook? "<strong style=\"color: green\">ON</strong>" : "<strong style=\"color: red\">OFF</strong>"?>   <small>Turns this sign in option ON/FF.</small></p>
                <p><strong>Facebook App ID: </strong><?=$foreignaccount_facebook_apiid?></p>
                <p><strong>Facebook App Secret: </strong><?=$foreignaccount_facebook_apisecret?></p>
            </div>
            <div class="panel-footer">
                <button type="button" class="btn btn-primary <?=$classsignIn?>" <?=$onclicksignIn?>><?=($classsignIn ? "Updated!" : "Reset Settings")?></button>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">Twitter Options - Current Values:</div>
            <div class="panel-body">
                <p><strong>Twitter Account: </strong><?=$twitter_account?></p>
            </div>
            <div class="panel-footer">
                <button type="button" class="btn btn-primary <?=$classtwitter?>" <?=$onclicktwitter?>><?=($classtwitter ? "Updated!" : "Reset Settings")?></button>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">Facebook Comments Options - Current Values:</div>
            <div class="panel-body">
                <p><strong>Facebook Comments: </strong><?=$commenting_fb ? "<strong style=\"color: green\">ON</strong>" : "<strong style=\"color: red\">OFF</strong>"?></p>
                <p><strong>App ID: </strong><?=$foreignaccount_facebook_apiid?></p>
                <p><strong>User ID: </strong><?=$fb_user_id?></p>
            </div>
            <div class="panel-footer">
                <button type="button" class="btn btn-primary <?=$classfbComments?>" <?=$onclickfbComments?>><?=($classfbComments ? "Updated!" : "Reset Settings")?></button>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">Google Maps - Current Values:</div>
            <div class="panel-body">
                <p><strong>Maps:  </strong><?=$google_maps ? "<strong style=\"color: green\">ON</strong>" : "<strong style=\"color: red\">OFF</strong>"?></p>
                <p><strong>Google Maps Key: </strong><?=$google_maps_key?></p>
            </div>
            <div class="panel-footer">
                <button type="button" class="btn btn-primary <?=$classgmaps?>" <?=$onclickgmaps?>><?=($classgmaps ? "Updated!" : "Reset Settings")?></button>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                Google Analytics - Current Values:
            </div>
            <div class="panel-body">
                <p><strong>Google Analytics Account: </strong><?=$google_analytics_account?></p>
                <p><strong>Front: </strong><?=$google_analytics_front ? "<strong style=\"color: green\">ON</strong>" : "<strong style=\"color: red\">OFF</strong>"?></p>
                <p><strong>Members: </strong><?=$google_analytics_members ? "<strong style=\"color: green\">ON</strong>" : "<strong style=\"color: red\">OFF</strong>"?></p>
                <p><strong>Sitemgr: </strong><?=$google_analytics_sitemgr ? "<strong style=\"color: green\">ON</strong>" : "<strong style=\"color: red\">OFF</strong>"?></p>
            </div>
            <div class="panel-footer">
                <button type="button" class="btn btn-primary <?=$classganalytics?>" <?=$onclickganalytics?>><?=($classganalytics ? "Updated!" : "Reset Settings")?></button>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                Footer Links - Current Values:
            </div>
            <div class="panel-body">
                <p><strong>Facebook: </strong><?=$setting_facebook_link?></p>
                <p><strong>Linkedin: </strong><?=$setting_linkedin_link?></p>
            </div>
            <div class="panel-footer">
                <button type="button" class="btn btn-primary <?=$classfooter?>" <?=$onclickfooter?>><?=($classfooter ? "Updated!" : "Reset Settings")?></button>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                Sitemgr General E-mail - Current Values:
            </div>
            <div class="panel-body">
                <p><strong>E-mail: </strong><?=$sitemgr_email?></p>
                <p><strong>Send notifications to the e-mail above: </strong><?=$send_email ? "<strong style=\"color: green\">ON</strong>" : "<strong style=\"color: red\">OFF</strong>"?></p>
            </div>
            <div class="panel-footer">
                <button type="button" class="btn btn-primary <?=$classsystemEmail?>" <?=$onclicksystemEmail?>><?=($classsystemEmail ? "Updated!" : "Reset Settings")?></button>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                "To Do" Items
            </div>
            <div class="panel-body">
                Reset all "to do" items
            </div>
            <div class="panel-footer">
                <button type="button" class="btn btn-primary" onclick="resetOption('<?=$url_redirect."?action=todoItems"?>');">Reset Settings</button>
            </div>
        </div>

    </div>
