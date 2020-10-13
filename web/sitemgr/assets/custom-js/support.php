<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /web/sitemgr/assets/custom-js/support.php
	# ----------------------------------------------------------------------------------------------------
?>
<script type="text/javascript">
    function JS_submit(value) {
        $("#rewriteFile").attr("value", value);
        document.configChecker.submit();
    }

    function resetOption(url) {
        location.href = url;
    }

    $(document).ready( function(){
        $("#reset_flags_button").click( function(){
            bootbox.confirm('Are you sure you want to reset all cron flags?', function( result ) {
                if ( result ) {
                    $.post("crontab.php", { action : "resetflags" }).done( function(data){
                        var message, elementClass;

                        if( data ){
                            message = "Success! Resetted all flags.";
                            notify.success(message);
                        }
                        else{
                            message = "Oops! Coldn't reset some flags. Contact support... yeah...";
                            notify.error(message, '', { fadeOut: 0 });
                        }
                    });
                }
            });
        });
    });

    <?php if ($errorMessage) { ?>
        notify.error('<?=$errorMessage;?>', '', { fadeOut: 0 });
    <?php } elseif ($_GET["message"] == "ok") { ?>
        notify.success('<?=system_showText(LANG_SITEMGR_SETTINGS_YOURSETTINGSWERECHANGED);?>');
    <?php } ?>
</script>