<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/assets/custom-js/manage-account.php
	# ----------------------------------------------------------------------------------------------------

?>
<script>
    function accountLogin(action, username) {
        var url = "";
        if (action == 'profile' || action == 'edit_profile') {
            url = "<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/sitemgraccess.php?account=" + username + "&action=" + action;
        } else {
            url = "<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/sitemgraccess.php?account=" + username + "";
        }
        membersection = window.open(url, "member_section");
        membersection.focus();
    }

    function approveAccount(acc_id) {
            $.get("<?=DEFAULT_URL."/".SITEMGR_ALIAS."/account/approve.php"?>", {
            acc_id: acc_id
        }, function () {
            window.location.reload();
        });
    }

    <?php if($message){ ?>
        <?php if($message == 3){ ?>
            <?php if($messageSection == 'sponsor'){ ?>
                notify.success('<?=system_showText(LANG_SITEMGR_SPONSORACCOUNTS)." ".system_showText(LANG_SITEMGR_WASSUCCESSDELETED);?>');
            <?php } else { ?>
                notify.success('<?=system_showText(LANG_SITEMGR_LABEL_MEMBER_ACCOUNT)." ".system_showText(LANG_SITEMGR_WASSUCCESSDELETED);?>');
            <?php } ?>
        <?php } ?>
    <?php } ?>

</script>
