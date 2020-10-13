<script>
    <?php if ($_SESSION['SESS_SM_ID']) { ?>
        notify.error('<?=system_showText(LANG_SITEMGR_SETLOGIN_ERROR1)?><br/><?=system_showText(LANG_SITEMGR_SETLOGIN_ERROR2)?>', '', { fadeOut: 0 });
    <?php }
    if ($error_currentpassword) { ?>
    notify.error('<?=$error_currentpassword?>', '', { fadeOut: 0 });
    <?php }
    if ($message_changelogin) { ?>
    notify.error('<?=$message_changelogin?>', '', { fadeOut: 0 });
    <?php } ?>
</script>
