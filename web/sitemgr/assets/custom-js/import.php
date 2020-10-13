<script>
    <?php if ($_GET["message"] == 1){ ?>
        notify.success('ImportLog successfully updated!');
    <?php } ?>
    
    <?php if ($success != 0){ ?>
        <?php if($success == 1){ ?>
            notify.success('Cron setting successfully changed!');
        <?php } else { ?>
            notify.error('Error trying to change the cron setting, please try again.', '', { fadeOut: 0 });
        <?php } ?>
    <?php } ?>

    <?php if ($successEvent != 0){ ?>
        <?php if($successEvent == 1){ ?>
            notify.success('Cron setting successfully changed!');
        <?php } else { ?>
            notify.error('Error trying to change the cron setting, please try again.', '', { fadeOut: 0 });
        <?php } ?>
    <?php } ?>
</script>