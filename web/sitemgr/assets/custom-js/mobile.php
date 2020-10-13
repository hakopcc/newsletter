<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /web/sitemgr/assets/custom-js/mobile.php
	# ----------------------------------------------------------------------------------------------------
?>
<script>
    function JS_submit(type) {
        if (type == "ios") {
            $("#submit_android").prop("value", "");
        } else if (type == "android") {
            $("#submit_ios").prop("value", "");
        }
        $("#submit_"+type).prop("value", "submit");
        document.splashScreen.submit();
    }

    <?php if ($error_ios) { ?>
        notify.error('<?=$error_ios;?>', '', { fadeOut: 0 });
    <?php } elseif ($success_ios) { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_MOBILE_SUCCESS);?>');
    <?php } ?>

    <?php if ($error_android) { ?>
        notify.error('<?=$error_android;?>', '', { fadeOut: 0 });
    <?php } elseif ($success_android) { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_MOBILE_SUCCESS);?>');
    <?php } ?>
</script>