<script>
    <?php if ($msg == 1) { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_TRANSACTION_DELETE_SUCCESS);?>');
    <?php } ?>

    <?php if ($error_message) { ?>
        notify.error('<?=(is_numeric($error_message) ? $msg_bulkupdate[$error_message] : $error_message);?>', '', { fadeOut: 0 });
    <?php } elseif ($msg == "successdel") { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_TRANSACTIONS_DELETE_SUCCESS);?>');
    <?php } ?>

    <?php unset($msg); ?>

    <?php if ($message_export_payment) { ?>
        $('#modal-payment').modal('show');
        notify.error('<?=$message_export_payment;?>', '', { fadeOut: 0 });
    <?php } ?>
</script>
