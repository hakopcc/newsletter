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
	# * FILE: /sponsors/claim/payment.php
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
	if ((CREDITCARDPAYMENT_FEATURE != "on") && (PAYMENT_INVOICE_STATUS != "on")) { exit; }
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

	$db = db_getDBObject(DEFAULT_DB, true);
	$dbObjClaim = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $db);
	$sqlClaim = "SELECT id FROM Claim WHERE account_id = '".$acctId."' AND listing_id = '".$claimlistingid."' AND status = 'progress' AND step = 'd' ORDER BY date_time DESC LIMIT 1";
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
    

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
	$listing_id[] = $listingObject->getNumber("id");
	$second_step = 1;
	include(INCLUDES_DIR."/code/billing.php");
	if ($bill_info["listings"]) foreach ($bill_info["listings"] as $id => $info);

	setting_get("payment_tax_status", $payment_tax_status);
	setting_get("payment_tax_value", $payment_tax_value);
	setting_get("payment_tax_label", $payment_tax_label);

	# ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/header.php");
    $cover_title = system_showText(LANG_LISTING_CLAIMING) .' "'. $listingObject->getString("title") .'"';
    $cover_subtitle = string_strtoupper(system_showText(LANG_EASYANDFAST)) .' '.string_strtoupper(system_showText(LANG_THREESTEPS));
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
                <?php if (PAYMENT_FEATURE === 'on') { ?>
                    <div class="breadcrumb-item" is-active="true">
                        <strong>3:</strong> <?=system_showText(LANG_LABEL_CHECKOUT);?>
                    </div>
                <?php } ?>
            </div>
            <br><br>
            <div class="members-wrapper">
                <div class="members-panel edit-panel">
                    <div class="panel-header">
                        <?=system_showText(LANG_MENU_MAKEPAYMENT)?>
                    </div>
                    <div class="panel-body">
                        <? if ($paymentSystemError) { ?>
                            <div class="form-edit-alert">
                                <?=$payment_message?><br>
                                <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/billing/index.php"><?=system_showText(LANG_MSG_GO_TO_MEMBERS_CHECKOUT);?></a>.
                            </div>
                        <? } elseif ($payment_message) { ?>
                            <div class="form-edit-alert">
                                <?=system_showText(LANG_MSG_PROBLEMS_WERE_FOUND)?>:<br>
                                <?=$payment_message?><br>
                                <a href="<?=DEFAULT_URL?>/<?=MEMBERS_ALIAS?>/billing/index.php"><?=system_showText(LANG_MSG_GO_TO_MEMBERS_CHECKOUT);?></a>.
                            </div>
                        <? } elseif ((!$bill_info["listings"]) && (!$bill_info["events"]) && (!$bill_info["banners"]) && (!$bill_info["classifieds"]) && (!$bill_info["articles"])) { ?>
                            <div class="form-edit-alert">
                                <?=system_showText(LANG_MSG_NO_ITEMS_SELECTED_REQUIRING_PAYMENT);?>
                            </div>
                        <? } else { ?>
                            <table class="table table-striped table-bordered">
                                <tr>
                                    <th><?=system_showText(LANG_LISTING_FEATURE_NAME);?></th>
                                    <th><?=system_showText(LANG_LABEL_LEVEL);?></th>
                                    <th><?=system_showText(LANG_LABEL_EXTRA_CATEGORY);?></th>
                                    <?
                                    if (PAYMENT_FEATURE == "on") {
                                        if ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on")) {
                                            ?><th><?=system_showText(LANG_LABEL_DISCOUNT_CODE)?></th><?
                                        }
                                    }
                                    ?>
                                    <th><?=system_showText(LANG_LABEL_RENEWAL);?></th>

                                    <? if ($payment_tax_status == "on") { ?>
                                        <th><?=system_showText(LANG_SUBTOTAL);?></th>
                                    <? } ?>

                                    <? if ($payment_tax_status == "on") { ?>
                                        <th><?=$payment_tax_label."(".$payment_tax_value."%)";?></th>
                                    <? } ?>

                                    <th><?=system_showText(LANG_LABEL_TOTAL);?></th>
                                </tr>
                                <tr>
                                    <td>
                                        <strong title="<?=$info["title"]?>">
                                            <?= system_showTruncatedText($info["title"], 60);?>
                                            <?=($info["listingtemplate"]?"<span class=\"itemNote\">(".$info["listingtemplate"].")</span>":"");?>
                                        </strong>
                                    </td>
                                    <td><?=string_ucwords($info["level"]);?></td>
                                    <td><?=$info["extra_category_amount"];?></td>
                                    <?
                                    if (PAYMENT_FEATURE == "on") {
                                        if ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on")) {
                                            ?><td><?=(($info["discount_id"]) ? ($info["discount_id"]) : (system_showText(LANG_NA)));?></td><?
                                        }
                                    }
                                    ?>
                                    <td><?=format_date($info["renewal_date"]);?></td>

                                    <? if ($payment_tax_status == "on") { ?>
                                        <td><?=PAYMENT_CURRENCY_SYMBOL." ".$bill_info["total_bill"];?></td>
                                    <? } ?>

                                    <? if ($payment_tax_status == "on") { ?>
                                        <td><?=PAYMENT_CURRENCY_SYMBOL." ".payment_calculateTax($bill_info["total_bill"], $payment_tax_value, true, false);?></td>
                                    <? } ?>

                                    <td>
                                        <?
                                            if ($payment_tax_status == "on") echo PAYMENT_CURRENCY_SYMBOL." ".payment_calculateTax($bill_info["total_bill"], $payment_tax_value, true);
                                            else echo PAYMENT_CURRENCY_SYMBOL." ".$bill_info["total_bill"];
                                        ?>
                                    </td>
                                </tr>
                            </table>
                            <div class="custom-biling">
                                <?
                                    $payment_process = "claim";
                                    if (file_exists(INCLUDES_DIR."/forms/form_billing_".$payment_method.".php")) {
                                        include(INCLUDES_DIR."/forms/form_billing_".$payment_method.".php");
                                    }
                                ?>
                            </div>
                        <?php } ?>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include(MEMBERS_EDIRECTORY_ROOT."/layout/footer.php");
