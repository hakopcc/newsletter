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
	# * FILE: /sponsors/claim/processpayment.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------
	extract($_GET);
	extract($_POST);

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSession();
	$acctId = sess_getAccountIdFromSession();
	$url_redirect = "".DEFAULT_URL."/".MEMBERS_ALIAS."/claim";
	$url_base = "".DEFAULT_URL."/".MEMBERS_ALIAS."";
	$members = 1;

	# ----------------------------------------------------------------------------------------------------
	# VALIDATE FEATURE
	# ----------------------------------------------------------------------------------------------------
	if (CLAIM_FEATURE != "on") { exit; }
	if (PAYMENT_FEATURE != "on") { exit; }
	if (CREDITCARDPAYMENT_FEATURE != "on") { exit; }

	
	$db = db_getDBObject(DEFAULT_DB, true);
	$dbObjClaim = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $db);
	$sqlClaim = "SELECT id FROM Claim WHERE account_id = '".$acctId."' AND status = 'progress' AND step = 'd' ORDER BY date_time DESC LIMIT 1";
	$resultClaim = $dbObjClaim->query($sqlClaim);
	if ($rowClaim = mysqli_fetch_assoc($resultClaim)) $claimID = $rowClaim["id"];
	
	if (!$claimID) {
		header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
		exit;
	}
	$claimObject = new Claim($claimID);
	if (!$claimObject->getNumber("id") || ($claimObject->getNumber("id") <= 0)) {
		header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
		exit;
	}
	

	$claimlistingid = $claimObject->getNumber("listing_id");

	if (!$claimlistingid) {
		header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
		exit;
	}

	$listingObject = new Listing($claimlistingid);
	if (!$listingObject->getNumber("id") || ($listingObject->getNumber("id") <= 0)) {
		header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
		exit;
	}
	if ($listingObject->getNumber("account_id") != $acctId) {
		header("Location: ".DEFAULT_URL."/".MEMBERS_ALIAS."/");
		exit;
	}
	

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$process = "claim";
	include(INCLUDES_DIR."/code/billing_".$payment_method.".php");

    /* ModStores Hooks */
    HookFire( "claimprocesspayment_before_render_page", [
        "payment_success" => &$payment_success,
        "payment_amount"  => &$payment_amount,
    ]);

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");
	$cover_title = system_showText(LANG_MSG_CLAIM_THIS_LISTING);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>
	<div class="members-page">
        <div class="container">
            <div class="claim-signup-breadcrumb">
                <div class="breadcrumb-item">
					<strong>1:</strong> <?=system_showText(LANG_LABEL_ACCOUNT_SIGNUP);?>
                </div>
                <div class="breadcrumb-item">
					<strong>2:</strong> <?=system_showText(LANG_LISTING_UPDATE);?>
                </div>
				<div class="breadcrumb-item">
					<strong>3:</strong> <?=system_showText(LANG_CHECKOUT);?>
				</div>
				<div class="breadcrumb-item" is-active="true">
					<strong>4:</strong> <?=system_showText(LANG_ADVERTISE_CONFIRMATION);?>
				</div>
            </div>
			<br><br>
			<div>
				<?
				if (!empty($listing_ids[0])) {
					foreach ($listing_ids as $each_listing_id) {
						$listingObject = new Listing($each_listing_id);
						echo "<h1 class=\"standardTitle\">".string_ucwords(LANG_LISTING_FEATURE_NAME).": <span>".$listingObject->getString("title")."</span></h1>";
					}
				}
				?>
				<h3 class="heading h-3"><?=system_showText(LANG_LABEL_PAYMENTSTATUS);?></h3>

				<?
				if ($payment_message) {
					echo $payment_message;
				}
				?>

				<?
				if ($payment_success != "y") {

					$claimObject->setString("step", "e");
					$claimObject->save();
					$next = DEFAULT_URL."/".MEMBERS_ALIAS."/claim/claimfinish.php?claimlistingid=".$claimlistingid;
					?>
					<br>
					<div class="form-edit-alert">
						<?=system_showText(LANG_MSG_THIS_PAGE_WILL_REDIRECT_YOU)?><br>
						<?=system_showText(LANG_MSG_IF_IT_DOES_NOT_WORK)?> <a href="<?=$next?>" class="link"><?=system_showText(LANG_LABEL_CLICK_HERE)?></a>.
					</div>
					<script type="text/javascript">
						window.setTimeout("window.location='<?=$next?>'", 10000);
					</script>
				<? } ?>
			</div>
		</div>
	</div>

<?
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
