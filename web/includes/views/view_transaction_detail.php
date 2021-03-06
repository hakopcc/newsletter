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
	# * FILE: /includes/views/view_transaction_detail.php
	# ----------------------------------------------------------------------------------------------------

$log_id = $transaction["id"];
$dbMain = db_getDBObject(DEFAULT_DB, true);
$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
$sql = "SELECT level FROM Payment_Classified_Log WHERE payment_log_id = $log_id LIMIT 1";
$result = $dbObj->query($sql);
$log_level = mysqli_fetch_assoc($result);

$str_time = "";

$startTimeStr = explode(":", $transaction["transaction_datetime"]);
$startTimeStr[0] = string_substr($startTimeStr[0],-2);
if (CLOCK_TYPE == '24') {
	$start_time_hour = $startTimeStr[0];
} elseif (CLOCK_TYPE == '12') {
	if ($startTimeStr[0] > "12") {
		$start_time_hour = $startTimeStr[0] - 12;
		$start_time_am_pm = "pm";
	} elseif ($startTimeStr[0] == "12") {
		$start_time_hour = 12;
		$start_time_am_pm = "pm";
	} elseif ($startTimeStr[0] == "00") {
		$start_time_hour = 12;
		$start_time_am_pm = "am";
	} else {
		$start_time_hour = $startTimeStr[0];
		$start_time_am_pm = "am";
	}
}
if ($start_time_hour < 10) $start_time_hour = "0".($start_time_hour+0);
$start_time_min = $startTimeStr[1];
$str_time .= $start_time_hour.":".$start_time_min." ".$start_time_am_pm;

$transac_date = explode(" ",$transaction["transaction_datetime"]);
?>

<br />
<?php if ($transaction["system_type"] == "manual") {?>
	<p class="alert alert-info"><?=system_showText(LANG_TRANSACTION_MANUAL);?></p>
<?php } ?>

<dl class="dl-horizontal">
    <?php if (string_strpos($url_base, "/".SITEMGR_ALIAS."")) { ?>

		<dt><?=system_showText(LANG_LABEL_ACCOUNT)?>:</dt>
		<dd>
            <?php if ($transaction["account_id"]) echo "<a href=\"".DEFAULT_URL."/".SITEMGR_ALIAS."/account/view.php?id=".$transaction["account_id"]."\" title = \"".$transaction["username"]."\">"; ?>
				<?=system_showTruncatedText(system_showAccountUserName($transaction["username"]), 50);?>
            <?php if ($transaction["account_id"]) echo "</a>"; ?>
		</dd>
    <?php } ?>

	<dt><?=system_showText(LANG_LABEL_PAYMENT_TYPE)?>:</dt>
	<dd>
        <?php
		if (($transaction["system_type"] != "paypal") && ($transaction["system_type"] != "manual") && ($transaction["system_type"] != "pagseguro")) {
			echo system_showText(LANG_CREDITCARD);
		} else {
			echo $transaction["system_type"];
		}
		if ($transaction["recurring"] == "y") {
			echo "&nbsp;<em>".system_showText(LANG_MSG_PRICES_AMOUNT_PER_INSTALLMENTS)."</em>";
		}
		?>
	</dd>
	<dt><?=system_showText(LANG_LABEL_ID)?>:</dt>
	<dd> <?=$transaction["transaction_id"]?></dd>

	<dt><?=system_showText(LANG_LABEL_STATUS)?>:</dt>
	<dd> <?=@constant(string_strtoupper(("LANG_LABEL_".$transaction["transaction_status"])))?></dd>

	<dt><?=system_showText(LANG_LABEL_DATE)?>:</dt>
	<dd> <?=$transac_date[0]." - ".$str_time;?></dd>

	<dt><?=system_showText(LANG_LABEL_IP)?>:</dt>
	<dd> <?=$transaction["ip"]?></dd>

	<dt><?=system_showText(LANG_LABEL_SUBTOTAL)?>:</dt>
	<dd> <?=number_format($transaction['transaction_amount'] - $transaction['transaction_tax'], 2, '.', '');?> (<?=$transaction["transaction_currency"]?>)</dd>

	<dt><?=system_showText(LANG_LABEL_TAX)?>:</dt>
	<dd> <?=$transaction["transaction_tax"]?> (<?=$transaction["transaction_currency"]?>)</dd>

	<dt><?=system_showText(LANG_LABEL_AMOUNT)?>:</dt>
	<dd> <?=$transaction["transaction_amount"]?> (<?=$transaction["transaction_currency"]?>)</dd>

	<dt><?=system_showText(LANG_LABEL_NOTES)?>:</dt>
	<dd><?=htmlspecialchars_decode($transaction['notes'])?></dd>

    <?php if (STRIPEPAYMENT_FEATURE == 'on' && RECURRING_FEATURE == 'on' && $transaction["transaction_status"] === 'Approved') { ?>
        <dt>&nbsp;</dt>
        <dd><a href="javascript:void(0);" id="linkCancelSub"><?=system_showText(LANG_LABEL_CANCELSUB)?></a></dd>
    <?php } ?>
</dl>

<?php if ($transaction_listing_log) {
	?>

	<h2 class="theme-title"><?=system_showText(LANG_LISTING_FEATURE_NAME_PLURAL);?></h2>

	<table class="table table-striped table-bordered">
		<tr>
			<th><?=system_showText(LANG_LABEL_TITLE);?></th>
			<th><?=system_showText(LANG_LABEL_EXTRA_CATEGORY);?></th>
			<th><?=system_showText(LANG_LABEL_LEVEL);?></th>
            <?php if (PAYMENT_FEATURE == "on") { ?>
                <?php if ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on")) { ?>
					<th><?=system_showText(LANG_LABEL_DISCOUNT_CODE)?></th>
                <?php } ?>
            <?php } ?>
			<th><?=system_showText(LANG_LABEL_RENEWAL);?></th>
			<th><?=system_showText(LANG_LABEL_ITEMPRICE);?></th>
		</tr>
        <?php foreach ($transaction_listing_log as $each_listing) { ?>
			<tr>
				<td>
                    <?php
					$transactionListingObj = new Listing($each_listing["listing_id"]);

                    if (string_strpos($url_base, "/".MEMBERS_ALIAS."") !== false) {
                        $validMember = false;
                    } else {
                        $validMember = true;
                    }

					if ($transactionListingObj->getNumber("id") > 0 && $validMember) {
						?><a href="<?=$url_base?>/<?=LISTING_FEATURE_FOLDER;?>/view.php?id=<?=$each_listing["listing_id"]?>" title="<?=$each_listing["listing_title"];?>"><?=system_showTruncatedText($each_listing["listing_title"], 50);?></a><?php
					} else {
						?><?=system_showTruncatedText($each_listing["listing_title"], 50);?><?php
					}
					?>
					<?=($each_listing["listingtemplate"]?"<span class=\"itemNote\">(".$each_listing["listingtemplate"].")</span>":"");?>
				</td>
				<td><?=$each_listing["extra_categories"]?></td>
				<td><?=string_ucwords($each_listing["level_label"]);?></td>
                <?php if (PAYMENT_FEATURE == "on") { ?>
                    <?php if ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on")) { ?>
						<td><?=$each_listing["discount_id"]?></td>
                    <?php } ?>
                <?php } ?>
				<td><?=($each_listing["renewal_date"] == "0000-00-00") ? system_showText(LANG_NA) : format_date($each_listing["renewal_date"],DEFAULT_DATE_FORMAT,"date")?></td>
				<td>
					<?=$each_listing["amount"]." (".$transaction["transaction_currency"].")";?>
				</td>
			</tr>
        <?php } ?>
	</table>
<?php } ?>

<?php if ($transaction_event_log) { ?>

	<h2 class="theme-title"><?=system_showText(LANG_EVENT_FEATURE_NAME_PLURAL);?></h2>

	<table class="table table-striped table-bordered">
		<tr>
			<th><?=system_showText(LANG_LABEL_TITLE);?></th>
			<th><?=system_showText(LANG_LABEL_LEVEL);?></th>
            <?php if (PAYMENT_FEATURE == "on") { ?>
                <?php if ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on")) { ?>
					<th><?=system_showText(LANG_LABEL_DISCOUNT_CODE)?></th>
                <?php } ?>
            <?php } ?>
			<th><?=system_showText(LANG_LABEL_RENEWAL);?></th>
			<th><?=system_showText(LANG_LABEL_ITEMPRICE);?></th>
		</tr>
        <?php foreach ($transaction_event_log as $each_event) { ?>
			<tr>
				<td>
                    <?php
					$transactionEventObj = new Event($each_event["event_id"]);

                    if (string_strpos($url_base, "/".MEMBERS_ALIAS."") !== false) {
                        $validMember = false;
                    } else {
                        $validMember = true;
                    }

					if ($transactionEventObj->getNumber("id") > 0 && $validMember) {
						?><a href="<?=$url_base?>/<?=EVENT_FEATURE_FOLDER;?>/view.php?id=<?=$each_event["event_id"]?>" title="<?=$each_event["event_title"];?>"><?=system_showTruncatedText($each_event["event_title"], 50);?></a><?php
					} else {
						?><?=system_showTruncatedText($each_event["event_title"], 50);?><?php
					}
					?>
				</td>
				<td><?=string_ucwords($each_event["level_label"]);?></td>
                <?php if (PAYMENT_FEATURE == "on") { ?>
                    <?php if ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on")) { ?>
						<td><?=$each_event["discount_id"]?></td>
                    <?php } ?>
                <?php } ?>
				<td><?=($each_event["renewal_date"] == "0000-00-00") ? system_showText(LANG_NA) : format_date($each_event["renewal_date"],DEFAULT_DATE_FORMAT,"date")?></td>
				<td>
					<?=$each_event["amount"]." (".$transaction["transaction_currency"].")";?>
				</td>
			</tr>
        <?php } ?>
	</table>
<?php } ?>

<?php if ($transaction_banner_log) { ?>

	<h2 class="theme-title"><?=system_showText(LANG_BANNER_FEATURE_NAME_PLURAL);?></h2>

	<table class="table table-striped table-bordered">
		<tr>
			<th><?=system_showText(LANG_LABEL_CAPTION)?></th>
			<th><?=system_showText(LANG_LABEL_LEVEL);?></th>
            <?php if (PAYMENT_FEATURE == "on") { ?>
                <?php if ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on")) { ?>
					<th><?=system_showText(LANG_LABEL_DISCOUNT_CODE)?></th>
                <?php } ?>
            <?php } ?>
			<th><?=system_showText(LANG_LABEL_RENEWAL);?></th>
			<th><?=system_showText(LANG_LABEL_ITEMPRICE);?></th>
		</tr>
        <?php foreach ($transaction_banner_log as $each_banner) {?>
			<tr>
				<td>
                    <?php
					$transactionBannerObj = new Banner($each_banner["banner_id"]);

                    if (string_strpos($url_base, "/".MEMBERS_ALIAS."") !== false) {
                        $validMember = false;
                    } else {
                        $validMember = true;
                    }

					if ($transactionBannerObj->getNumber("id") > 0 && $validMember) {
						?><a href="<?=$url_base?>/<?=BANNER_FEATURE_FOLDER;?>/view.php?id=<?=$each_banner["banner_id"]?>" title="<?=$each_banner["banner_caption"]?>"><?=system_showTruncatedText($each_banner["banner_caption"], 50);?></a><?php
					} else {
						?><?=system_showTruncatedText($each_banner["banner_caption"], 50);?><?php
					}
					?>
				</td>
				<td><?=string_ucwords($each_banner["level_label"]);?></td>
                <?php if (PAYMENT_FEATURE == "on") { ?>
                    <?php if ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on")) { ?>
						<td><?=$each_banner["discount_id"]?></td>
                    <?php } ?>
                <?php } ?>
				<td><?=($each_banner["renewal_date"] == "0000-00-00") ? (system_showText(LANG_NA)) : format_date($each_banner["renewal_date"],DEFAULT_DATE_FORMAT,"date")?></td>
				<td>
					<?=$each_banner["amount"]." (".$transaction["transaction_currency"].")";?>
				</td>
			</tr>
        <?php } ?>
	</table>
<?php } ?>

<?php if ($transaction_classified_log) { ?>

	<h2 class="theme-title"><?=system_showText(LANG_CLASSIFIED_FEATURE_NAME_PLURAL);?></h2>

	<table class="table table-striped table-bordered">
		<tr>
			<th><?=system_showText(LANG_LABEL_TITLE);?></th>
			<th><?=system_showText(LANG_LABEL_LEVEL);?></th>
            <?php if (PAYMENT_FEATURE == "on") { ?>
                <?php if ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on")) { ?>
					<th><?=system_showText(LANG_LABEL_DISCOUNT_CODE)?></th>
                <?php } ?>
            <?php } ?>
			<th><?=system_showText(LANG_LABEL_RENEWAL);?></th>
			<th><?=system_showText(LANG_LABEL_ITEMPRICE);?></th>
		</tr>
        <?php foreach ($transaction_classified_log as $each_classified) { ?>
			<tr>
				<td>
                    <?php
					$transactionClassifiedObj = new Classified($each_classified["classified_id"]);

                    if (string_strpos($url_base, "/".MEMBERS_ALIAS."") !== false) {
                        $validMember = false;
                    } else {
                        $validMember = true;
                    }

					if ($transactionClassifiedObj->getNumber("id") > 0 && $validMember) {
						?><a href="<?=$url_base?>/<?=CLASSIFIED_FEATURE_FOLDER;?>/view.php?id=<?=$each_classified["classified_id"]?>" title="<?=$each_classified["classified_title"]?>"><?=system_showTruncatedText($each_classified["classified_title"], 50);?></a><?php
					} else {
						?><?=system_showTruncatedText($each_classified["classified_title"], 50);?><?php
					}
					?>
				</td>
				<td><?=string_ucwords($each_classified["level_label"]);?></td>
                <?php if (PAYMENT_FEATURE == "on") { ?>
                    <?php if ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on")) { ?>
						<td><?=$each_classified["discount_id"]?></td>
                    <?php } ?>
                <?php } ?>
				<td><?=($each_classified["renewal_date"] == "0000-00-00") ? system_showText(LANG_NA) : format_date($each_classified["renewal_date"],DEFAULT_DATE_FORMAT,"date")?></td>
				<td>
					<?=$each_classified["amount"]." (".$transaction["transaction_currency"].")";?>
				</td>
			</tr>
        <?php } ?>
	</table>
<?php } ?>

<?php if ($transaction_article_log) { ?>

	<h2 class="theme-title"><?=system_showText(LANG_ARTICLE_FEATURE_NAME_PLURAL);?></h2>

	<table class="table table-striped table-bordered">
		<tr>
			<th><?=system_showText(LANG_LABEL_TITLE);?></th>
            <?php if (PAYMENT_FEATURE == "on") { ?>
                <?php if ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on")) { ?>
					<th><?=system_showText(LANG_LABEL_DISCOUNT_CODE)?></th>
                <?php } ?>
            <?php } ?>
			<th><?=system_showText(LANG_LABEL_RENEWAL);?></th>
			<th><?=system_showText(LANG_LABEL_ITEMPRICE);?></th>
		</tr>
        <?php foreach ($transaction_article_log as $each_article) { ?>
			<tr>
				<td>
                    <?php
					$transactionArticleObj = new Article($each_article["article_id"]);

                    if (string_strpos($url_base, "/".MEMBERS_ALIAS."") !== false) {
                        $validMember = false;
                    } else {
                        $validMember = true;
                    }

					if ($transactionArticleObj->getNumber("id") > 0 && $validMember) {
						?><a href="<?=$url_base?>/<?=ARTICLE_FEATURE_FOLDER;?>/view.php?id=<?=$each_article["article_id"]?>" title="<?=$each_article["article_title"]?>"><?=system_showTruncatedText($each_article["article_title"], 50);?></a><?php
					} else {
						?><?=system_showTruncatedText($each_article["article_title"], 50);?><?php
					}
					?>
				</td>
                <?php if (PAYMENT_FEATURE == "on") { ?>
                    <?php if ((CREDITCARDPAYMENT_FEATURE == "on") || (PAYMENT_INVOICE_STATUS == "on")) { ?>
						<td><?=$each_article["discount_id"]?></td>
                    <?php } ?>
                <?php } ?>
				<td><?=($each_article["renewal_date"] == "0000-00-00") ? system_showText(LANG_NA) : format_date($each_article["renewal_date"],DEFAULT_DATE_FORMAT,"date")?></td>
				<td>
					<?=$each_article["amount"]." (".$transaction["transaction_currency"].")";?>
				</td>
			</tr>
        <?php } ?>
	</table>
<?php } ?>

<?php if ($transaction_custominvoice_log) { ?>
	<h2 class="theme-title"><?=system_showText(LANG_CUSTOM_INVOICES);?></h2>

	<table class="table table-striped table-bordered">
		<tr>
			<th><?=system_showText(LANG_LABEL_TITLE);?></th>
			<th><?=system_showText(LANG_LABEL_ITEMS);?></th>
			<th><?=system_showText(LANG_LABEL_DATE);?></th>
			<th><?=system_showText(LANG_LABEL_ITEMPRICE);?></th>
		</tr>
        <?php foreach ($transaction_custominvoice_log as $each_custominvoice) { ?>
			<tr>
				<td>
                    <?php
					$transactionCustomInvoiceObj = new CustomInvoice($each_custominvoice["custom_invoice_id"]);

					if ($transactionCustomInvoiceObj->getNumber("id") > 0) {
						if (string_strpos($url_base, "/".SITEMGR_ALIAS."") !== false) {
							?><a href="<?=$url_base?>/custominvoices/view.php?id=<?=$each_custominvoice["custom_invoice_id"]?>" title="<?=$each_custominvoice["title"]?>"><?=system_showTruncatedText($each_custominvoice["title"], 50);?></a><?php
						} else {
							?><?=system_showTruncatedText($each_custominvoice["title"], 50);?><?php
						}
					} else {
						?><?=$each_custominvoice["title"]?><?php
					}
					?>
				</td>

				<td><a data-toggle="modal" data-target="#modal-custominvoice-<?=$each_custominvoice["custom_invoice_id"]?>" class="link-table" style="cursor: pointer;"><?=ucfirst(system_showText(LANG_VIEWITEMS))?></a></td>
				<td><?=format_date($each_custominvoice["date"])?></td>
				<td>
					<?=$each_custominvoice["amount"]." (".$transaction["transaction_currency"].")";?>
				</td>
			</tr>
        <?php } ?>
	</table>
    <?php
	foreach ($transaction_custominvoice_log as $each_custominvoice) {
		$id = $each_custominvoice["custom_invoice_id"];
		include(INCLUDES_DIR."/modals/modal-custominvoice.php");
	}
} ?>

<?php if ($transaction_package_log) { ?>
	<h2 class="theme-title"><?=system_showText(LANG_PACKAGE_SING);?></h2>

	<table class="table table-striped table-bordered">
		<tr>
			<th><?=system_showText(LANG_LABEL_TITLE);?></th>
			<th><?=system_showText(LANG_LABEL_ITEMS);?></th>
			<th><?=system_showText(LANG_LABEL_ITEMPRICE);?></th>
		</tr>
        <?php foreach ($transaction_package_log as $each_package) { ?>
			<tr>
				<td>
                    <?php
					$transactionPackageObj = new Package($each_package["package_id"]);

					if ($transactionPackageObj->getNumber("id") > 0) {
						if (string_strpos($url_base, "/".SITEMGR_ALIAS."") !== false) {
							?><a href="<?=$url_base?>/package/view.php?id=<?=$each_package["package_id"]?>" title="<?=$each_package["package_title"]?>"><?=system_showTruncatedText($each_package["package_title"], 50);?></a><?php
						} else {
							?><?=system_showTruncatedText($each_package["package_title"], 50);?><?php
						}
					} else {
						?><?=$each_package["package_title"]?><?php
					}
					?>
				</td>
                <?php
				if (string_strpos($url_base, "/".SITEMGR_ALIAS."") !== false) {
					$popup_url = DEFAULT_URL."/".SITEMGR_ALIAS."/package/view_items.php?";
				} else {
                    $popup_url = DEFAULT_URL."/popup/popup.php?pop_type=package_items&";
				}
				?>
				<td><a data-toggle="modal" data-target="#modal-package-<?=$each_package["package_id"]?>" class="link-table" style="cursor: pointer;"><?=ucfirst(system_showText(LANG_VIEWITEMS))?></a></td>
				<td>
					<?=$each_package["amount"]." (".$transaction["transaction_currency"].")";?>
				</td>
			</tr>
        <?php } ?>
	</table>
    <?php
	foreach ($transaction_package_log as $each_package) {
		$id = $each_package["package_id"];
		$items = $each_package["items"];
		$items_price = $each_package["items_price"];
		include(INCLUDES_DIR."/modals/modal-packageitems.php");
	}

}

if (STRIPEPAYMENT_FEATURE == 'on' && RECURRING_FEATURE == 'on' && $transaction["transaction_status"] === 'Approved') { ?>
    <!-- This form is used to process the subscription cancellation request -->
    <form name="subscription" id="form_subscription" action="<?=system_getFormAction($_SERVER['PHP_SELF'])?>?id=<?=$log_id?>" method="post">
        <input type="hidden" name="cancel_sub" value="1">
    </form>
<?php }
