<script>
    <?php if ($success_message) { ?>
        notify.success('<?=$success_message;?>');
    <?php } elseif ($error_message && !$message) { ?>
        notify.error('<?=$error_message;?>');
    <?php } else { ?>
        <?php if ($message) { ?>
            notify.error('<?=$message;?>');
        <?php } ?>
    <?php } ?>
</script>