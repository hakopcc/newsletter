<script>
    <?php if (is_numeric($message) && isset($msg_account[$message])) { ?>
        notify.success('<?=$msg_account[$message]?>');
    <?php } elseif ((string_strlen(trim($message_member)) > 0) || (string_strlen(trim($message_account)) > 0) || (string_strlen(trim($message_contact)) > 0) ) { ?>
        <?php
            $messages = [];

            if (string_strlen(trim($message_member)) > 0) {
                $messages[] = $message_member;
            }

            if (string_strlen(trim($message_account)) > 0) {
                $messages[] = $message_account;
            }

            if (string_strlen(trim($message_contact)) > 0) {
                $messages[] = $message_contact;
            }
        ?>

        notify.error('<?=implode("<br/>", $messages);?>', '', { fadeOut: 0 });
    <?php } ?>
</script>