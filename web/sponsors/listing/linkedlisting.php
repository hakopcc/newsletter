<?php
/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
#                                                                    #
######################################################################
\*==================================================================*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /sponsors/listing/linkedlisting.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include '../../conf/loadconfig.inc.php';

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSession();
$acctId = sess_getAccountIdFromSession();

# ----------------------------------------------------------------------------------------------------
# AUX
# ----------------------------------------------------------------------------------------------------
extract($_GET);
extract($_POST);

$url_redirect = ''.DEFAULT_URL.'/'.MEMBERS_ALIAS;
$url_base = ''.DEFAULT_URL.'/'.MEMBERS_ALIAS.'';
$members = 1;
$item_form    = 1;

if (system_blockListingCreation($id)) {
    header('Location: '.DEFAULT_URL.'/'.MEMBERS_ALIAS.'/');
    exit;
}

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
include(EDIRECTORY_ROOT. '/includes/code/linkedlisting.php');

# ----------------------------------------------------------------------------------------------------
# HEADER
# ----------------------------------------------------------------------------------------------------
include MEMBERS_EDIRECTORY_ROOT.'/layout/header.php';

# ----------------------------------------------------------------------------------------------------
# NAVBAR
# ----------------------------------------------------------------------------------------------------
include MEMBERS_EDIRECTORY_ROOT.'/layout/navbar.php';

$members = 1;

$cover_title = system_showText($id ? LANG_LABEL_EDIT : LANG_ADD) .' '. system_showText(LANG_LISTING_FEATURE_NAME);
include EDIRECTORY_ROOT.'/frontend/coverimage.php';
?>
    <div class="members-page">

        <div class="container">
            <div class="members-wrapper">
                <?
                if ($id) {
                    include(MEMBERS_EDIRECTORY_ROOT."/".LISTING_FEATURE_FOLDER."/navbar.php");
                }
                ?>
                <div class="members-panel edit-panel">
                    <div class="panel-body">
                        <form name="listing" id="listing" action="<?=system_getFormAction($_SERVER['PHP_SELF'])?>" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="id" value="<?=$id?>">
                            <input type="hidden" id="listingFieldId" value="<?=$linkedListingField->getId()?>" >
                            <input type="hidden" id="listingId" value="<?=$id?>">

                            <div class="custom-edit-content" has-sidebar="true">
                                <?php include INCLUDES_DIR.'/forms/form-linked-listing.php' ?>
                            </div>

                            <div class="linked-listing-footer-actions">
                                <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/" class="button button-md is-outline"><?=system_showText(LANG_BUTTON_CANCEL)?></a>
                                <button class="button button-md is-primary action-save" type="button" onclick="submitLinkedListings(<?=$id?>)" id="submitButton" data-text="<?=LANG_MSG_SAVE_CHANGES?>" data-loading-text="<?= LANG_LABEL_FORM_WAIT ?>">
                                    <?=system_showText(LANG_MSG_SAVE_CHANGES)?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php

# ----------------------------------------------------------------------------------------------------
# FOOTER
# ----------------------------------------------------------------------------------------------------
$customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/linked-listings.php';
include MEMBERS_EDIRECTORY_ROOT.'/layout/footer.php';
