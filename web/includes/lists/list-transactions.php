<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/lists/list-transactions.php
	# ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
?>

    <section>
        <form name="item_list" role="form">
            <ul class="list-content-item list">
                <?php
                $cont = 0;

                if ($transactions) foreach ($transactions as $transaction) {
                    $cont++;
                    $id = $transaction["id"];
                    $str_time = format_getTimeString($transaction["transaction_datetime"]);
                    if (defined("LANG_LABEL_".$transaction["transaction_status"])) {
                        $labelStatus = @constant(string_strtoupper(("LANG_LABEL_".$transaction["transaction_status"])));
                    } else {
                        $labelStatus = $transaction["transaction_status"];
                    }
                    if ($transaction["transaction_amount"] > 0) {
                        $amount_field = $transaction["transaction_amount"]." (".$transaction["transaction_currency"].")";
                    } else {
                        $amount_field = "0.00 (".$transaction["transaction_currency"].")";
                    }
                    if (($transaction["system_type"] != "paypal") && ($transaction["system_type"] != "manual") && ($transaction["system_type"] != "pagseguro")) {
                        $type_field = system_showText(LANG_CREDITCARD);
                    } else {
                        $type_field = $transaction["system_type"];
                    }

                    //Prepare info to preview
                    $previewTransaction[$cont]["id"] = $id;

                    ?>

                <?php // --------------- HTML code ------------------- //?>

                <li class="content-item" data-id="<?=$id?>">

                    <div class="check-bulk">
                        <input type="checkbox" id="transaction_id<?=$cont?>" name="item_check[]" value="<?=$id?>" onclick="bulkSelect('transaction');"/>
                    </div>
                    <div class="item">
                        <h3 class="item-title"><?=$transaction["transaction_id"]?> - <span class="text-success"><?=$amount_field;?></span> </h3>
                        <p>
                            <?php if ($transaction["account_id"] > 0) {  ?>
                                <a href="<?=$url_base?>/account/sponsor/sponsor.php?id=<?=$transaction["account_id"]?>" class="link-table">
                                    <?=system_showAccountUserName($transaction["username"])?>
                                </a>
                            <?php } else { ?>
                                <?=system_showAccountUserName($transaction["username"])?>
                            <?php } ?>
                        </p>
                        <p>
                            <span class="pull-left"><?=format_date($transaction["transaction_datetime"], DEFAULT_DATE_FORMAT, "datetime")." - ".$str_time?></span>
                            <span class="pull-right"><?=$labelStatus?></span>
                        </p>

                    </div>
                </li>
            <?php } ?>

            </ul>

        </form>

    </section>
