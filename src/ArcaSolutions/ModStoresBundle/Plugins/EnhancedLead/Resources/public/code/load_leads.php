<?php
/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2005 Arca Solutions, Inc. All Rights Reserved.           #
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
# * FILE: /members/ajax.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------

if (strpos(__DIR__, 'public') !== false) {
    include '../../../../../../../../web/conf/loadconfig.inc.php';
} else {
    include '../../../conf/loadconfig.inc.php';
}

header('Content-Type: text/html; charset='.EDIR_CHARSET, true);
header('Accept-Encoding: gzip, deflate');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check', false);
header('Pragma: no-cache');

function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = [];
            foreach ($data as $key => $row) {
                $tmp[$key] = $row[$field];
            }
            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);

    return array_pop($args);
}

extract($_POST);

if ($item_id) {

    // Page Browsing /////////////////////////////////////////
    $itemObj = new Listing($item_id);
    $levelObj = new ListingLevel(false);
    $acctId = sess_getAccountIdFromSession();
    $limit = false;
    $maxItems = 3;
    $where = " Leads.type = 'listing' AND Leads.item_id = '$item_id' AND Leads.item_id = Listing.id AND Listing.account_id = '$acctId'";

    $show_leadsTables = true;

    // Build Where to get only Leads in this month
    $where .= ' AND YEAR(Leads.entered) = '.($filter_year ? "'".$filter_year."'" : 'YEAR(NOW())');
    $where .= ' AND MONTH(Leads.entered) = '.($filter_month ? "'".$filter_month."'" : 'MONTH(NOW())');


    //Get leads per level
    $leadsLevel = 0;
    $dbMain = db_getDBObject(DEFAULT_DB, true);
    if (defined('SELECTED_DOMAIN_ID')) {
        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
    } else {
        $dbObj = db_getDBObject();
    }
    $sql = 'SELECT field FROM ListingLevel_FieldLeads WHERE level = '.$itemObj->getNumber('level');
    $result = $dbObj->query($sql);

    if (mysqli_num_rows($result) > 0) {
        $aux_array = mysqli_fetch_assoc($result);
        $leadsLevel = $aux_array['field'];
    }

    // Get Limit of Leads to show to this user
    $limit = $itemObj->getNumber('leads_max') != 'NULL' ? $itemObj->getNumber('leads_max') : $leadsLevel;

    $pageObj = new pageBrowsing('Leads, Listing', $screen, $limit, 'Leads.entered ASC', 'Leads.first_name', $letter,
        $where, 'Leads.*');
    $leadsArrTmp2 = $pageObj->retrievePage('array');
    if ($leadsArrTmp2 && $limit) {
        $leadsArrTmp = array_orderby($leadsArrTmp2, 'entered', SORT_DESC);

        $newLeads = 0;
        if ($leadsArrTmp) {
            foreach ($leadsArrTmp as $each_leadssArrTmp) {
                $auxLeadObj = new Lead($each_leadssArrTmp['id']);
                $leadsArr[] = $auxLeadObj->data_in_array;
                if ($each_leadssArrTmp['new'] == 'y') {
                    $newLeads++;
                }
            }
        }
    }

    $item_status = $itemObj->getString('status');
    if ($newLeads == 1) {
        $newLeadsTip = str_replace('[x]', $newLeads, system_showText(LANG_LABEL_NEW_LEAD));
    } else {
        $newLeadsTip = str_replace('[x]', $newLeads, system_showText(LANG_LABEL_NEW_LEADS));
    }

    if ($levelObj && $levelObj->getDetail($itemObj->getNumber('level')) == 'y') {
        $linkTwitter = $moduleURL.'/'.$itemObj->getString('friendly_url').'.html';
        $linkFacebook = $moduleURL.'/'.ALIAS_SHARE_URL_DIVISOR.'/'.$itemObj->getString('friendly_url').'.html';
    } else {
        $linkTwitter = $moduleURL.'/results.php?id='.$itemObj->getNumber('id');
        $linkFacebook = $moduleURL.'/'.ALIAS_SHARE_URL_DIVISOR.'/'.$itemObj->getString('friendly_url').'.html';
    }

    $shareFacebook = 'href="http://www.facebook.com/sharer.php?u='.$linkFacebook.'&amp;t='.urlencode($itemObj->getString('title')).'" target="_blank"';
    $shareTwitter = 'href="http://twitter.com/?status='.$linkTwitter.'" target="_blank"';
}
?>

<div class="hidden" id="leads-count"><?=count($leadsArr)?> <?=(count($leadsArr) == 1 ? LANG_LABEL_LEAD : LANG_LABEL_LEADS)?></div>
<div class="content-list" id="leads_list_section">
    <?php
        $countLead = 1;

        if ($leadsArr) {
            foreach ($leadsArr as $each_lead) {
                $auxMessage = @unserialize($each_lead['message']);

                if (is_array($auxMessage)) {
                    $each_lead['message'] = '';
                    foreach ($auxMessage as $key => $value) {
                        $each_lead['message'] .= (defined($key) ? constant($key) : $key).($value ? ': '.$value : '')."\n";
                    }
                }

                $replied = false;

                if ($each_lead['reply_date'] != '0000-00-00 00:00:00' && !empty($each_lead['reply_date'])) {
                    $replied = true;
                    $titleIco = system_showText(LANG_LEAD_REPLIED_ICO).' ('.format_date($each_lead['reply_date'], DEFAULT_DATE_FORMAT, 'datestring').')';
                }

                $titleIcoToday = system_showText(LANG_LEAD_REPLIED_ICO).' ('.format_date(date('Y').'-'.date('m').'-'.date('d'), DEFAULT_DATE_FORMAT, 'datestring').')';

                $lead_name = $each_lead['first_name'].($each_lead['last_name'] ? ' '.$each_lead['last_name'] : '');
    ?>
                <div class="content-item" data-id="<?=$countLead?>" is-new="<?=($each_lead['new'] == 'y' ? 'new' : '');?>" id="item-lead_<?=$countLead?>" <?=$countLead > $maxItems ? 'style="display:none;"' : '' ?>>
                    <div class="content-header no-flex">
                        <h4 class="heading h-4 content-title"><?=$lead_name?> - <time><?=$each_lead['entered'] ? format_date($each_lead['entered'], DEFAULT_DATE_FORMAT,
                        'datetime') : system_showText(LANG_NA);?></time></h4>
                        <h5 class="heading h-5 content-author" style="margin-top: 8px"><strong><?= $each_lead['subject']; ?></strong></h5>
                        <div class="content-from">
                            <?=system_showText(LANG_LABEL_FROM);?>
                            <strong><?=$lead_name?></strong>
                        </div>
                        <button class="button button-sm is-primary button-edit-reply" data-ref="<?=$countLead?>" onclick="leadBox(this);" data-text='["<?= system_showText(LANG_LABEL_REPLY);?>", "<?=system_showText(LANG_BUTTON_CANCEL);?>"]'><?= system_showText(LANG_LABEL_REPLY);?></button>
                    </div>
                    <div class="content-body">
                        <div class="reply-block">
                            <blockquote class="content-reply">
                                <div class="reply-text">
                                    <?=nl2br($each_lead['message']);?>
                                </div>
                            </blockquote>
                        </div>
                        <form name="formLead<?= $each_lead['id']; ?>" action="javascript:" method="post" class="reply-form" data-action="lead" id="reply-form-<?=$countLead?>">
                            <div class="reply-message" id="msgLeadS<?= $each_lead['id']; ?>" data-type="success"><?= system_showText(LANG_LEAD_REPLIED); ?></div>
                            <div class="reply-message" id="msgLeadE<?= $each_lead['id']; ?>" data-type="error"></div>

                            <input type="hidden" name="item_id" value="<?= $item_id; ?>">
                            <input type="hidden" name="item_type" value="<?= $item_type; ?>">
                            <input type="hidden" name="type" value="<?= $item_type; ?>">
                            <input type="hidden" name="idLead" value="<?= $each_lead['id']; ?>">
                            <input type="hidden" name="action" value="reply">
                            <input type="hidden" name="ajax_type" value="lead_reply">

                            <div class="form-group">
                                <label for="lead-mail<?= $each_lead['id']; ?>"><?= system_showText(LANG_LABEL_TO); ?>: </label>
                                <input id="lead-mail" class="input" type="email" name="to" value="<?= ($to && $action == 'reply' && $idLead == $each_lead['id'] ? $to : $each_lead['email']); ?>">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="lead-message<?= $each_lead['id']; ?>"><?= system_showText(LANG_LABEL_MESSAGE); ?>:</label>
                                <textarea id="lead-message<?= $each_lead['id']; ?>" class="input" name="message" rows="5"><?= ($message && $action == 'reply' && $idLead == $each_lead['id'] ? $message : ''); ?></textarea>
                            </div>

                            <div class="text-center">
                                <button type="button" class="button button-md is-primary" name="submit" id="submitLead<?= $each_lead['id']; ?>" onclick="saveLead(<?= $each_lead['id']; ?>);"><?= system_showText(LANG_BUTTON_SUBMIT) ?></button>
                            </div>
                        </form>
                    </div>
                </div>
    <?php
                $countLead++;
            }

            if ($countLead > ($maxItems + 1)) {
    ?>
                <br>
                <div class="content-viewmore">
                    <a href="javascript:" class="button button-md is-secondary text-center" full-width="true" id="linkMoreleads" onclick="showmore('item-lead_', 'linkMoreleads', <?= $countLead ?>, <?= $maxItems ?>);"><?= system_showText(LANG_VIEWMORE); ?></a>
                    <input type="hidden" id="item-lead_" value="<?= $maxItems ?>">
                </div>
    <?php
            }
        }
    ?>
</div>
