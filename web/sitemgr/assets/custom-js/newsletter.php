<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/assets/custom-js/newsletter.php
	# ----------------------------------------------------------------------------------------------------

?>
<script>

    function showAccountTabs(num_div, accType) {
        $("#accType").attr("value", accType);
        $("#accType2").attr("value", accType);

        for (j = 0; j < 2; j++) {
            $('#account_'+j).css('display', 'none');
            $('#tab_account_'+j).removeClass("active");
        }
        $('#account_'+num_div).css('display', '');
        $('#tab_account_'+num_div).addClass("active");

    }

    function disconnect() {
        $("#arcamailer_disconnect").submit();
    }

    function openLogin() {
        window.open("http://send.arcamailer.com<?=($edir_customer_id && $edir_email ? "?username=$edir_email" : "")?>", "_blank");
    }

    // Notification messages
    <?php if ($message_mailapp && $actionForm == "newAcc" && $account_type == "new") { ?>
        notify.error('<?=$message_mailapp;?>', '', { fadeOut: 0 });
    <?php } elseif ($messageSignup) { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_MAILAPP_ACCDONE);?>');
    <?php } elseif ($messageConnect) { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_MAILAPP_CONNECTDONE);?>');
    <?php } elseif ($messageDisconnect) { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_MAILAPP_DISCONNECTDONE);?>');
    <?php } ?>

    <?php if ($message_mailapp && $actionForm == "newAcc" && $account_type == "existing") { ?>
        notify.error('<?=$message_mailapp;?>', '', { fadeOut: 0 });
    <?php } ?>

    <?php if ($message_mailapp && $actionForm == "newList") { ?>
        notify.error('<?=$message_mailapp;?>', '', { fadeOut: 0 });
    <?php } elseif ($messageUpdate) { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_MAILAPP_LISTUPDATE);?>');
    <?php } elseif ($messageNewList) { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_MAILAPP_LISTCREATE);?>');
    <?php } ?>
</script>
