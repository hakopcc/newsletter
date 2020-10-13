<script>
    <?php if ($message_discountcode) { ?>
        notify.error('<?=$message_discountcode;?>', '', { fadeOut: 0 });
    <?php } ?>

    <?php if (is_numeric($message) && isset($msg_discountcode[$message]) && $page == 'discount') { ?>
        notify.success('<?=$msg_discountcode[$message];?>');
    <?php } ?>

    <?php if (is_numeric($message) && isset($msg_package[$message]) && $page == 'package') { ?>
        notify.success('<?=$msg_package[$message];?>');
    <?php } ?>
</script>