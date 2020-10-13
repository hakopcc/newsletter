<script>
    <?php if (is_numeric($message) && isset($msg_claim[$message])) { ?>
        notify.success('<?=$msg_claim[$message]?>');
    <?php } ?>
</script>