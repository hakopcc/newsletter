<?php
	/*==================================================================*\
	######################################################################
	#                                                                    #
	# Copyright 2020 Arca Solutions, Inc. All Rights Reserved.           #
	#                                                                    #
	# This file may not be redistributed in whole or part.               #
	# eDirectory is licensed on a per-domain basis.                      #
	#                                                                    #
	# ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
	#                                                                    #
	# http://www.edirectory.com | http://www.edirectory.com/license.html #
	######################################################################
	\*==================================================================*/

	# ----------------------------------------------------------------------------------------------------
	# * FILE: web/sponsors/custom-js/help.php
    # ----------------------------------------------------------------------------------------------------
?>
<script>
    <?php if ($message_help) { ?>
        <?php if ($success) { ?>
            notify.success('<?=$message_help?>');
        <?php } else { ?>
            notify.error('<?=$message_help?>', '', { fadeOut: 0 });
        <?php } ?>
    <?php } ?>
</script>