<?php

	/*==================================================================*\
	######################################################################
	#                                                                    #
	# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
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
	# * FILE: /includes/forms/form-changelogin.php
	# ----------------------------------------------------------------------------------------------------
    ?>

    <div class="row">
        <div class="col-md-6 form-group">
            <label for="first_name"><?=system_showText(LANG_SITEMGR_LABEL_FIRSTNAME)?></label>
            <input type="text" name="first_name" id="first_name" value="<?=$first_name?>" class="form-control" <?=$readonly?>>
        </div>

        <div class="col-md-6 form-group">
            <label for="last_name"><?=system_showText(LANG_SITEMGR_LABEL_LASTNAME)?></label>
            <input type="text" name="last_name" id="last_name" value="<?=$last_name?>" class="form-control" <?=$readonly?>>
        </div>

        <div class="col-md-6 form-group">
            <label for="username"><?=system_showText(LANG_SITEMGR_LABEL_USERNAME)?></label>
            <input type="email" name="username" id="username" value="<?=$username?>" class="form-control" <?=$readonly?>>
        </div>

        <div class="col-md-6 form-group">
            <label for="current_password"><?=system_showText(LANG_SITEMGR_LABEL_CURRENTPASSWORD)?></label>
            <input type="password" name="current_password" id="current_password" class="form-control" <?=$readonly?>>
        </div>

        <div class="col-md-6 form-group">
            <label for="password"><?=system_showText(LANG_SITEMGR_NEW)?> <?=strtolower(system_showText(LANG_SITEMGR_LABEL_PASSWORD))?></label>
            <input type="password" name="password" id="password" class="form-control" <?=$readonly?>>
        </div>

        <div class="col-md-6 form-group">
            <label for="retype_password"><?=system_showText(LANG_SITEMGR_LABEL_RETYPENEWPASSWORD)?></label>
            <input type="password" name="retype_password" id="retype_password" class="form-control" <?=$readonly?>>
        </div>
    </div>
