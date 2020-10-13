<script>
    <?php if ($deletedMessage) { ?>
        notify.success('<?=$deletedMessage;?>');
    <?php } ?>

    <?php if ($_GET['error'] === '1') { ?>
        notify.error('<?=LANG_SITEMGR_PAGE_BUILDER_NOTFOUND;?>', '', { fadeOut: 0 });
    <?php } ?>
</script>

<script type="text/javascript" src="<?= DEFAULT_URL ?>/scripts/pages.js"></script>