<script>
    <?php if (is_numeric($error_message)) { ?>
        notify.error('<?=$msg_bulkupdate[$error_message];?>', '', { fadeOut: 0 });
    <?php } elseif ($error_msg) { ?>
        notify.error('<?=$error_msg;?>', '', { fadeOut: 0 });
    <?php } elseif ($msg == "success") { ?>
        notify.success('<?=system_showText(LANG_MSG_NEARBYSEARCH_SUCCESSFULLY_UPDATE);?>');
    <?php } elseif ($msg == "successdel") { ?>
        notify.success('<?=system_showText(LANG_MSG_NEARBYSEARCH_SUCCESSFULLY_DELETE);?>');
    <?php } ?>
    
    <?php unset($msg); ?>
</script>