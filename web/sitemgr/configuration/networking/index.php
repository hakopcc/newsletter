<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/configuration/networking/index.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include("../../../conf/loadconfig.inc.php");

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSMSession();
permission_hasSMPerm();

mixpanel_track("Accessed section Social Media");

# ----------------------------------------------------------------------------------------------------
# AUX
# ----------------------------------------------------------------------------------------------------
$_POST = array_map("trim", $_POST);
extract($_POST);
extract($_GET);

# ----------------------------------------------------------------------------------------------------
# SUBMIT
# ----------------------------------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $error   = false;
    $success = true;

    if (!empty($signin)) {

        mixpanel_track("Changed Social Media settings", [
            "Facebook Comments" => empty($fb_op)?'empty':$fb_op,
            "Facebook Login" => empty($foreignaccount_facebook)?'empty':$foreignaccount_facebook,
            "Google Login" => empty($foreignaccount_google)?'empty':$foreignaccount_google,
        ]);

        if ((!empty($fb_op) || !empty($foreignaccount_facebook)) && (empty($foreignaccount_facebook_apisecret) || empty($foreignaccount_facebook_apiid) || empty($fb_user_id))) {
            $error = true;
        } else {
            setting_set("foreignaccount_facebook",$foreignaccount_facebook);
            setting_set("foreignaccount_facebook_apisecret", $foreignaccount_facebook_apisecret);
            setting_set("foreignaccount_facebook_apiid", $foreignaccount_facebook_apiid);
            setting_set("commenting_fb", $fb_op);
            setting_set("commenting_fb_user_id", $fb_user_id);
        }

        //Google
        if ((!empty($foreignaccount_google) && !empty($foreignaccount_google_clientid) && !empty($foreignaccount_google_clientsecret)) || (empty($foreignaccount_google) && empty($foreignaccount_google_clientid) && empty($foreignaccount_google_clientsecret))) {
            if (!setting_set("foreignaccount_google", $foreignaccount_google))
                if (!setting_new("foreignaccount_google", $foreignaccount_google))
                    $error = true;

            if (!setting_set("foreignaccount_google_clientid", $foreignaccount_google_clientid))
                if (!setting_new("foreignaccount_google_clientid", $foreignaccount_google_clientid))
                    $error = true;

            if (!setting_set("foreignaccount_google_clientsecret", $foreignaccount_google_clientsecret))
                if (!setting_new("foreignaccount_google_clientsecret", $foreignaccount_google_clientsecret))
                    $error = true;
        } else {
            $error = false;

            if (!empty($foreignaccount_google_clientid) && !empty($foreignaccount_google_clientsecret)) {

                if (!setting_set("foreignaccount_google", $foreignaccount_google))
                    if (!setting_new("foreignaccount_google", $foreignaccount_google))
                        $error = true;

                if (!setting_set("foreignaccount_google_clientid", $foreignaccount_google_clientid))
                    if (!setting_new("foreignaccount_google_clientid", $foreignaccount_google_clientid))
                        $error = true;

                if (!setting_set("foreignaccount_google_clientsecret", $foreignaccount_google_clientsecret))
                    if (!setting_new("foreignaccount_google_clientsecret", $foreignaccount_google_clientsecret))
                        $error = true;
            } else if (empty($foreignaccount_google) || empty($foreignaccount_google_clientid) || empty($foreignaccount_google_clientsecret)) {
                $error = true;
            }
        }

        if (!$error) {
            $actions[] = '&#149;&nbsp;'.system_showText(LANG_SITEMGR_SETTINGS_LOGINOPTION_CONFIGURATIONWASCHANGED);
        } else {
            $actions[] = '&#149;&nbsp;'.system_showText(LANG_SITEMGR_SETTINGS_LOGINOPTION_EMPTYKEYS);
            $message_style = "errorMessage";
        }
        if ($actions) {
            $message_foreignaccount .= implode("<br />", $actions);
        }

        $success = !$error;
    }
}

# ----------------------------------------------------------------------------------------------------
# HEADER
# ----------------------------------------------------------------------------------------------------
include(SM_EDIRECTORY_ROOT."/layout/header.php");

# ----------------------------------------------------------------------------------------------------
# FORMS DEFINES
# ----------------------------------------------------------------------------------------------------
//Sign In Options
/**
 * Facebook Account
 */
setting_get("foreignaccount_facebook", $foreignaccount_facebook);
if ($foreignaccount_facebook === "on") $foreignaccount_facebook_checked = "checked";
if (empty($foreignaccount_facebook_apisecret)) setting_get("foreignaccount_facebook_apisecret", $foreignaccount_facebook_apisecret);
if (empty($foreignaccount_facebook_apiid)) setting_get("foreignaccount_facebook_apiid", $foreignaccount_facebook_apiid);

/**
 * Google Account
 */
setting_get("foreignaccount_google", $foreignaccount_google);
if ($foreignaccount_google === "on") $foreignaccount_google_checked = "checked";
if (empty($foreignaccount_google_clientid)) setting_get("foreignaccount_google_clientid", $foreignaccount_google_clientid);
if (empty($foreignaccount_google_clientsecret)) setting_get("foreignaccount_google_clientsecret", $foreignaccount_google_clientsecret);

/*
 * Facebook User ID
 */
$checkLink = '#';

if (empty($commenting_fb)) setting_get("commenting_fb", $commenting_fb);
if (empty($fb_user_id)) setting_get("commenting_fb_user_id", $fb_user_id);

if ($_GET["user_id"]) {
    $fb_user_id = $_GET["user_id"];
}
?>
<main class="main-dashboard">
    <nav class="main-sidebar">
        <?php include(SM_EDIRECTORY_ROOT."/layout/sidebar-dashboard.php"); ?>
        <div class="sidebar-submenu">
            <?php include(SM_EDIRECTORY_ROOT . "/layout/sidebar.php"); ?>
        </div>
    </nav>
    <div class="main-wrapper">
        <?php include(SM_EDIRECTORY_ROOT."/layout/navbar.php"); ?>
        <div class="main-content" content-full="true">
            <?php
            require SM_EDIRECTORY_ROOT.'/registration.php';
            require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
            ?>

            <section class="section-heading">
                <div class="section-heading-content">
                    <h1 class="section-heading-title"><?=system_showText(LANG_SITEMGR_NETWORKING);?> <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="<?=system_showText(LANG_SITEMGR_SETTINGS_SHARE_TIP1);?>"></span></h1>
                </div>
            </section>

            <section class="section-form row">
                <div class="col-xs-12">
                    <?php include(INCLUDES_DIR."/forms/form-networking.php"); ?>
                </div>
            </section>
        </div>
    </div>
</main>
<div class="modal fade" id="confirmFbUserIdModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?=system_showText(LANG_SITEMGR_SETTINGS_CONFIRMFBID_MODAL_TITLE)?></h4>
            </div>
            <div class="modal-body">
                <div id="fb-id-container"></div>
                <div id="modal-confirmation-msg"><?=sprintf(system_showText(LANG_SITEMGR_SETTINGS_CONFIRMFBID_MODAL_CHECK_MSG_FORMAT), '<a href="#" id="logOffFacebook">'.system_showText(LANG_SITEMGR_HERE).'</a>')?></div>
                <script id="fb-id-container-template" type="text/x-jsrender">
                        <input type="hidden" id="confirmed-fb-id" value="{{:id}}">
                        <span class="fbfirstname">{{:name}}</span>&nbsp;(<span class="fbemail">{{:email}}</span>)&nbsp;-&nbsp;<?=system_showText(LANG_FACEBOOK_USER_ID);?>:&nbsp;<span class="fbuserid">{{:id}}</span>
                    </script>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= system_showText(LANG_SITEMGR_CANCEL); ?></button>
                <button type="button" class="btn btn-default" id="submit-select-fb-id-form"><?= system_showText(LANG_SITEMGR_SETTINGS_CONFIRMFBID_MODAL_CONFIRM); ?></button>
            </div>
        </div>
    </div>
</div>
<?php
if (class_exists("JavaScriptHandler")) {
    setting_get('sitemgr_language', $lang);
    $langStr='en_US';
    switch ($lang){
        case 'de':
        case 'ge':
            $langStr='de_DE';
            break;
        case 'es':
            $langStr='es_ES';
            break;
        case 'fr':
            $langStr='fr_FR';
            break;
        case 'it':
            $langStr='it_IT';
            break;
        case 'pt':
            $langStr='pt_BR';
            break;
        case 'tr':
            $langStr='tr_TR';
            break;
    }
    $sdkSrc='sdk.js';
    if (!empty(DEMO_DEV_MODE)) {
        $sdkSrc = 'sdk/debug.js';
    }
    $facebookJsSdkSrc="//connect.facebook.net/" . $langStr . "/" . $sdkSrc;
    JavaScriptHandler::registerLone("", "src=\"" . $facebookJsSdkSrc . "\" async defer");
    JavaScriptHandler::registerLone("", "src=\"https://www.jsviews.com/download/jsrender.min.js\"");
}

# ----------------------------------------------------------------------------------------------------
# FOOTER
# ----------------------------------------------------------------------------------------------------
$customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/networking.php";

include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
