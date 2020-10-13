<script>
    <?php if (isset($errorMessage)) { ?>
        notify.error('<?=$errorMessage;?>', '', { fadeOut: 0 });
    <?php } elseif ($_GET["message"] == "ok") { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_SETTINGS_YOURSETTINGSWERECHANGED);?>');
    <?php } ?>
</script>