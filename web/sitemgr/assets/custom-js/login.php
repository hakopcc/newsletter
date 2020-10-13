<script>
    <?php if ($message_login) { ?>
        notify.error('<?=$message_login;?>', '', { fadeOut: 0 });
    <?php } ?>
</script>