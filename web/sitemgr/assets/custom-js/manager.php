<script>
    $('.toggle-item').on('click', function(){
        let state = $(this).parent(".switch-button").hasClass("is-enable");

        if(state){
            $(this).parent(".switch-button").removeClass("is-enable");
            $(this).parent(".switch-button").addClass("is-disable");
            $('#managerStatusState').val('2');
        } else {
            $(this).parent(".switch-button").removeClass("is-disable");
            $(this).parent(".switch-button").addClass("is-enable");
            $('#managerStatusState').val('1');
        }
    });

    <?php if (is_numeric($message) && isset($msg_account[$message])) { ?>
        notify.success('<?=$msg_account[$message]?>');
    <?php } ?>
    <?php if ($message_smpassword) { ?>
        notify.error('<?=$message_smpassword?>', '', { fadeOut: 0 });
    <?php } ?>
    <?php if ($message_smaccount) { ?>
        <?php if($success){ ?>
            notify.success('<?=$message_smaccount?>');
        <?php } else { ?>
            notify.error('<?=$message_smaccount?>', '', { fadeOut: 0 });
        <?php } ?>
    <?php } ?>
</script>
