<script>
    <?php if ($message_editorchoice) { ?>
        notify.success('<?=$message_editorchoice;?>');
    <?php } elseif (is_numeric($message) && isset($msg_designation[$message])) { ?>
        notify.success('<?=$msg_designation[$message];?>');
    <?php } elseif ($message_error_editorchoice) { ?>
        notify.error('<?=$message_error_editorchoice?>', '', { fadeOut: 0 });
    <?php } ?>
</script>