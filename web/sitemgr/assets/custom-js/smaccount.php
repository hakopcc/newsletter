<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /ed-admin/assets/custom-js/smaccount.php
    # ----------------------------------------------------------------------------------------------------
?>

<script src="<?=DEFAULT_URL?>/scripts/jquery/auto_upload/js/file_uploads.js"></script>

<script>
    function uploadSMAccountPicture() {
        $("#smaccount").vPB({
            url: DEFAULT_URL + "/sitemgr/account/myaccount.php?action=uploadPhoto&ajax=1",
            success: function(response)
            {
                strReturn = response.split("||");

                if (strReturn[0] == "ok") {
                    $("#smaccount_image").hide().fadeIn('slow').html(strReturn[1]);
                } else {
                    notify.error(strReturn[1], '', { fadeOut: 0 });
                }
                btn = $('.action-save');
                btn.button('reset');
            }
        }).submit();
    }

    function removeSMAccountPicture() {
        $.post(DEFAULT_URL + "/sitemgr/account/myaccount.php", {
            action: "removePhoto",
            ajax: true
        }, function(){
            $("#smaccount_image").html('<img class="user-picture" width="100" height="100" src="/assets/images/user-image.png">');
            $('#remove_image').hide();
        });
    }

    <?php if ($message_smpassword) { ?>
        notify.error('<?=$message_smpassword;?>', '', { fadeOut: 0 });
    <?php } ?>
    <?php if ($message_smaccount) { ?>
        <?php if($success){ ?>
            notify.success('<?=$message_smaccount;?>');
        <?php } else { ?>
            notify.error('<?=$message_smaccount;?>', '', { fadeOut: 0 });
        <?php } ?>
    <?php } ?>

    <?php if ($error_currentpassword) { ?>
        notify.error('<?=$error_currentpassword;?>', '', { fadeOut: 0 });
    <?php } ?>

    <?php if ($message_changelogin) { ?>
        <?php if($success){ ?>
            notify.success('<?=$message_changelogin;?>');
        <?php } else { ?>
            notify.error('<?=$message_changelogin;?>', '', { fadeOut: 0 });
        <?php } ?>
    <?php } ?>
</script>
