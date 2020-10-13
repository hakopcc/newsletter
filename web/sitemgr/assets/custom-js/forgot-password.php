<?php
    /*
     * # Admin Panel for eDirectory
     * @copyright Copyright 2020 Arca Solutions, Inc.
     * @author Basecode - Arca Solutions, Inc.
     */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /web/sitemgr/assets/custom-js/forgot-password.php
    # ----------------------------------------------------------------------------------------------------
?>
<script>
    <?php if ($message) { ?>
        <?php if($message_class == "successMessage"){ ?>
            notify.success('<?=$message;?>');
        <?php } else { ?>
            notify.error('<?=$message;?>', '', { fadeOut: 0 });
        <?php } ?>
    <?php } ?>
</script>