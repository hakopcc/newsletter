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
	# * FILE: web/sponsors/custom-js/member-account.php
    # ----------------------------------------------------------------------------------------------------
?>
<script>
    <?php if (!$contact->getString('email')) { ?>
        notify.error('<?=system_showText(LANG_MSG_FOREIGNACCOUNTWARNING);?>', '', { fadeOut: 0 });
    <?php } ?>

    <?php if ($message) { ?>
        <?php if ($message_style == 'success') { ?>
            notify.success('<?=$message?>');
        <?php } elseif($message_style == 'error') { ?>
            notify.error('<?=$message?>', '', { fadeOut: 0 });
        <?php } else { ?>
            notify.info('<?=$message?>', '', { fadeOut: 0 });
        <?php } ?>
    <?php } ?>

    <?php if (string_strlen(trim($message_demoDotCom)) > 0) { ?>
        notify.error('<?=$message_demoDotCom?>', '', { fadeOut: 0 });
    <?php } ?>
    
    <?php if (string_strlen(trim($message_profile)) > 0) { ?>
        notify.error('<?=$message_profile?>', '', { fadeOut: 0 });
    <?php } ?>
    
    <?php if ($_GET["error"] == "disableAttach") { ?>
        notify.error('<?=system_showText(LANG_FB_ALREADY_LINKED)?>', '', { fadeOut: 0 });
    <?php } ?>
    
    <?php if (isset($_GET["facebookerror"])) { ?>
        notify.error('<?=system_showText(LANG_MSG_ERROR_NUMBER)." 10001. ".system_showText(LANG_MSG_TRY_AGAIN);?>', '', { fadeOut: 0 });
    <?php } ?>
    
    <?php if (string_strlen(trim($message_member)) > 0) { ?>
        notify.error('<?=$message_member?>', '', { fadeOut: 0 });
    <?php } ?>
    
    <?php if (string_strlen(trim($message_account)) > 0) { ?>
        notify.error('<?=$message_account?>', '', { fadeOut: 0 });
    <?php } ?>
    
    <?php if (string_strlen(trim($message_contact)) > 0) { ?>
        notify.error('<?=$message_contact?>', '', { fadeOut: 0 });
    <?php } ?>

    <?php if ($messageAct) { ?>
        notify.success('<?=system_showText(LANG_MSG_ACCOUNT_ACTIVATED);?>');
    <?php } ?>
</script>