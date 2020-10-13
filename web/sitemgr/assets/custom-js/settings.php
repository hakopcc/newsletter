<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/assets/custom-js/settings.php
	# ----------------------------------------------------------------------------------------------------

    if ($loadMap) {
        include(EDIRECTORY_ROOT."/includes/code/maptuning_forms.php");
   }

    /* ModStores Hooks */
    HookFire('generalsettings_before_render_js');

?>
<script>
    function setNewKey() {
        $("#edirectory_api_key_disabled").attr("value", $("#new_key").val());
        $("#edirectory_api_key").attr("value", $("#new_key").val());
    }

    function download_doc(){
        <?php if (!DEMO_LIVE_MODE) { ?>
            document.location = "<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/configuration/general-settings/index.php?download=1";
        <?php } else { ?>
            livemodeMessage(true);
        <?php } ?>
    }
    
    <?php if ($hasValidCoord) { ?>
        loadMap(false, true);
    <?php } ?>
    
    <?php if ($success) { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_SETTINGS_YOURSETTINGSWERECHANGED);?>');
    <?php } ?>
</script>
