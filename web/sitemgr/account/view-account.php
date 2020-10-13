<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/account/view-account.php
	# ----------------------------------------------------------------------------------------------------

    if (is_array($previewAccount)) {
        if(setting_get('userconsent_status') == "on"){
            //container to consent
            $container = SymfonyCore::getContainer();
            $consentService = $container->get('consent.service');

        }
        foreach ($previewAccount as $prevAccount) {
            if(setting_get('userconsent_status') == "on"){
                $consent = $consentService->getConsentByAccount($prevAccount['id']);
                $leads = $consentService->getLeadConsentByAccount($prevAccount['id']);
                $prevAccount["accountConsent"] = $consent;
                $prevAccount["leads"] = $leads;

            }
            ?>
        <section class="view-content-info" id="view-content-info-<?=$prevAccount["id"]?>" style="display:none">
            <div class="control-view">
                <div class="btn-toolbar pull-left">
                    <div class="btn-group btn-group-sm">
                        <a class="btn btn-icon btn-info" href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/account/".($prevAccount["sponsor"] == "y" ? "sponsor/sponsor.php" : "visitor/visitor.php")."?id={$prevAccount["id"]}&screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : "")?>" title="<?=system_showText(LANG_LABEL_EDIT)?>"><i class="icon-edit38"></i>  <span class="hidden-xs"><?=system_showText(LANG_LABEL_EDIT)?></span></a>
                        <? if ($prevAccount["active"] == "n") { ?>
                            <a class="btn btn-icon btn-info" href="javascript:void(0);" onclick="approveAccount(<?=$prevAccount["id"]?>);" title="<?=system_showText(LANG_SITEMGR_APPROVE_ACC)?>"><i class="icon-flag25"></i>  <span class="hidden-xs"><?=system_showText(LANG_SITEMGR_APPROVE_ACC)?></a>
                        <? } ?>

                        <a class="btn btn-icon btn-info" data-toggle="modal" data-target="#modal-forgot" href="#" onclick="$('#forgot-id').val(<?=$prevAccount["id"]?>); $('#forgot-username').val('<?=$prevAccount["username"]?>');" title="<?=system_showText(LANG_LABEL_RESET_PASSWORD);?>"><i class="icon-ion-ios7-locked-outline"></i>  <span class="hidden-xs"><?=system_showText(LANG_LABEL_RESET_PASSWORD);?></a>

                        <? if (!DEMO_LIVE_MODE || ($prevAccount["username"] != "demo@demodirectory.com")) { ?>
                            <a class="btn btn-icon btn-danger" data-toggle="modal" data-target="#modal-delete" href="#" onclick="$('#delete-id').val(<?=$prevAccount["id"]?>)" title="<?=system_showText(LANG_LABEL_DELETE);?>"><i class="icon-waste2"></i>  <span class="hidden-xs"><?=system_showText(LANG_LABEL_DELETE);?></span></a>
                        <? } ?>
                    </div>
                </div>
                <button type="button" class="close close-view" aria-hidden="true">&times;</button>
            </div>

            <div class="view-item">
                <div class="row">
                    <? if (SOCIALNETWORK_FEATURE == "on" && $prevAccount["profile"] == "y") { ?>
                    <div class="pull-right text-center profile-image col-sm-6 col-xs-12">
                        <div class="objectfit">
                            <img class="img-circle img-objectfit" src="<?=system_getUserImage($prevAccount["id"]);?>" alt="<?=$prevAccount["name"]?>">
                        </div>
                        <p>
                            <a class="btn btn-icon btn-info" href="javascript:void(0);" onclick="accountLogin('profile', '<?=$prevAccount["username"]?>');">
                                <i class="icon-ion-person"></i><?=system_showText(LANG_SITEMGR_VIEW_USER_PROFILE)?>
                            </a>
                        </p>
                    </div>
                    <? } ?>
                    <div class="col-xs-12 col-sm-6">
                        <h1><?=$prevAccount["name"]?></h1>
                        <p><?=$prevAccount["email"]?></p>
                    </div>

                    <div class="col-xs-12">
                        <? if ($prevAccount["company"]) { ?>
                        <h5><?=system_showText(LANG_SITEMGR_LABEL_COMPANY)?></h5>
                        <p><?=$prevAccount["company"]?></p>
                        <? } ?>

                        <? if ($prevAccount["address"]) { ?>
                        <h5><?=system_showText(LANG_SITEMGR_LABEL_ADDRESS)?></h5>
                        <address><?=$prevAccount["address"]?></address>
                        <? } ?>

                        <? if ($prevAccount["phone"]) { ?>
                        <h5><?=system_showText(LANG_SITEMGR_LABEL_PHONE)?></h5>
                        <p><?=$prevAccount["phone"]?></p>
                        <? } ?>

                        <? if ($prevAccount["url"]) { ?>
                        <h5><?=system_showText(LANG_SITEMGR_LABEL_URL)?></h5>
                        <p><?=$prevAccount["url"]?></p>
                        <? } ?>

                        <h5><?=system_showText(LANG_SITEMGR_DATECREATED)?></h5>
                        <p><?=$prevAccount["created"]?></p>

                        <h5><?=system_showText(LANG_SITEMGR_LASTUPDATED)?></h5>
                        <p><?=$prevAccount["updated"]?></p>
                        <? if ($prevAccount["accountConsent"] && (count($prevAccount["accountConsent"])!=0)) { ?>
                            <h5><?=system_showText(LANG_CONSENT)?></h5>
                            <?  foreach ($prevAccount["accountConsent"] as $consent){ ?>
                                <? if (strtoupper($consent->getConsentId()->getValue()) == "SIGNUP") {?>
                                    <p><?=ucfirst(system_showText(LANG_SITEMGR_LABEL_NAME))?>, <?=lcfirst(system_showText(LANG_SITEMGR_LABEL_EMAIL))?> <?=system_showText(LANG_SITEMGR_LABEL_AND)?> <?=system_showText(LANG_SITEMGR_LABEL_PHONE_NUMBER)?> <?=system_showText(LANG_SITEMGR_LABEL_AFTER_SIGN_IN)?> <?=format_date($consent->getDate()->format('Y-m-d'))?></p>
                                <?  } else if (strtoupper($consent->getConsentId()->getValue()) == "PAYMENT") {?>
                                    <p><?=ucfirst(system_showText(LANG_SITEMGR_LABEL_NAME))?>, <?=system_showText(LANG_SITEMGR_LABEL_BILLING_ADDRESS)?> <?=system_showText(LANG_SITEMGR_LABEL_AND)?> <?=system_showText(LANG_SITEMGR_LABEL_AFTER_SUBMIT_PAYMENT)?> <?=format_date($consent->getDate()->format('Y-m-d'))?></p>
                                <?  }  else if (strtoupper($consent->getConsentId()->getValue()) == "REVIEW") { ?>
                                    <p><?=ucfirst(system_showText(LANG_SITEMGR_LABEL_NAME))?>, <?=lcfirst(system_showText(LANG_SITEMGR_LABEL_EMAIL))?> <?=system_showText(LANG_SITEMGR_LABEL_AND)?> <?=system_showText(LANG_SITEMGR_LABEL_AFTER_SUBMIT_REVIEW)?> <?=format_date($consent->getDate()->format('Y-m-d'))?></p>
                                <?  } else if (strtoupper($consent->getConsentId()->getValue()) == "CONTACTUS") {?>
                                    <p><?=ucfirst(system_showText(LANG_SITEMGR_LABEL_NAME))?>, <?=lcfirst(system_showText(LANG_SITEMGR_LABEL_EMAIL))?> <?=system_showText(LANG_SITEMGR_LABEL_AND)?>  <?=system_showText(LANG_SITEMGR_LABEL_PHONE_NUMBER)?> <?=system_showText(LANG_SITEMGR_LABEL_AFTER_SUBMIT_CONTACT)?> <?=format_date($consent->getDate()->format('Y-m-d'))?></p>
                                <?  }  else  if (strtoupper($consent->getConsentId()->getValue()) == "NEWSLETTER") {?>
                                    <p><?=ucfirst(system_showText(LANG_SITEMGR_LABEL_NAME))?> <?=system_showText(LANG_SITEMGR_LABEL_AND)?> <?=lcfirst(system_showText(LANG_SITEMGR_LABEL_EMAIL))?> <?=system_showText(LANG_SITEMGR_LABEL_AFTER_NEWSLETTER)?> <?=format_date($consent->getDate()->format('Y-m-d'))?></p>
                                <?  } ?>
                            <? } ?>
                        <? } ?>
                        <? if ($prevAccount["leads"] && (count($prevAccount["leads"])!=0)) { ?>
                            <? if (!($prevAccount["accountConsent"] && (count($prevAccount["accountConsent"])!=0))) { ?>
                                <h5><?=system_showText(LANG_CONSENT)?></h5>
                            <?  } ?>
                            <?  foreach ($prevAccount["leads"] as $leads){ ?>
                                <? if (strtoupper($leads->title) == "LEADFORM") {?>
                                    <p><?=system_showText(LANG_SITEMGR_LABEL_CUSTOM_FORM)?> <?=format_date($leads->getDatetimeConsent()->format('Y-m-d'))?> </p>
                                <?  } else {?>
                                    <p><?=system_showText(LANG_SITEMGR_LABEL_CONTACT_INFORMATION_SENDING_MESSAGE)?> <?=$leads->title?> <?=system_showText(LANG_SITEMGR_LABEL_ON_LOCATION)?>  <?=format_date($leads->getDatetimeConsent()->format('Y-m-d'))?> </p>
                                <?  } ?>
                            <? } ?>
                        <? } ?>
                </div>
                </div>
            </div>

        </section>

<?      }
    }
?>
