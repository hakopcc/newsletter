<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/navbar.php
	# ----------------------------------------------------------------------------------------------------

    /*
	 * Get Domains
	 */
	$domainDropDown = domain_getDropDown(DEFAULT_URL, $_SERVER['REQUEST_URI'], $_SERVER['QUERY_STRING'], SELECTED_DOMAIN_ID);

    $sitemgrAbv = '';
    $sitemgrName = '';

    if($smId = sess_getSMIdFromSession()) {
        $sitemgrObj = new SMAccount($smId);
        $sitemgrFirstName = $sitemgrObj->getString('first_name');
        $sitemgrLastName = $sitemgrObj->getString('last_name');
        if(!empty($sitemgrObj->getNumber('image_id')) && $sitemgrObj->getNumber('image_id') !== 'NULL') {
            $imageObj = new Image($sitemgrObj->getNumber('image_id'), true);
        }
    } else {
        if(!empty(setting_get('sitemgr_firstname', $sitemgrFirstName))) {
            setting_get('sitemgr_firstname', $sitemgrFirstName);
        } else {
            setting_get('install_name', $sitemgrFirstName);
        }
        setting_get('sitemgr_lastname', $sitemgrLastName);

        if(!empty(setting_get('sitemgr_imageid', $imageId))) {
            $imageObj = new Image($imageId, true);
        }
    }

    if (isset($imageObj) && is_object($imageObj)) {
        $imageTagNavbar = $imageObj->getTag(true, SITEMGR_ACCOUNT_IMAGE_WIDTH, SITEMGR_ACCOUNT_IMAGE_HEIGHT);
    }

    !empty($sitemgrFirstName) and $sitemgrAbv .= ucwords($sitemgrFirstName[0]) and $sitemgrName .= $sitemgrFirstName;
    !empty($sitemgrLastName) and $sitemgrAbv .= ucwords($sitemgrLastName[0]) and $sitemgrName .= ' ' . $sitemgrLastName;

    if (EDIR_LANGUAGE === 'pt_br') {
        $supportUrl = 'http://edirectory.com.br/suporte';
    } else {
        $supportUrl = 'http://edirectory.com/support';
    }

    $accountActivePage = "";

    if((string_strpos($_SERVER['PHP_SELF'], '/account/myaccount.php') === false) && (string_strpos($_SERVER['PHP_SELF'], '/account/') !== false)){
        $accountActivePage = 'is-active';
    }

    $domainObj = new Domain(SELECTED_DOMAIN_ID);
    $domainURL = 'http://' . $domainObj->getString('url');
?>
    <div class="main-header">
        <?php if (sess_isSitemgrLogged() && string_strpos($_SERVER['PHP_SELF'], 'registration.php') === false && string_strpos($_SERVER['PHP_SELF'], 'resetpassword.php') === false && string_strpos($_SERVER['PHP_SELF'], 'setlogin.php') === false) { ?>
            <button type="button" class="toggle-sidebar"><i class="fa fa-bars"></i></button>
        <?php } ?>
        <div class="domain-toggle" id="navBrand">
            <div class="domain-selected">
                <?php if (string_strpos($_SERVER['PHP_SELF'], 'registration.php') === false) { ?>
                    <a href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/" class="domain-logo" <?=(trim(EDIRECTORY_TITLE) ? 'title="' .EDIRECTORY_TITLE. '"' : '')?> target="_parent" style="background-image: url(<?=image_getLogoImage();?>)"></a>
                <?php } ?>

                <?php if ($domainDropDown && string_strpos($_SERVER['PHP_SELF'], 'registration.php') === false && string_strpos($_SERVER['PHP_SELF'], 'forgot.php') === false && string_strpos($_SERVER['PHP_SELF'], 'resetpassword.php') === false && string_strpos($_SERVER['PHP_SELF'], 'setlogin.php') === false) { ?>
                    <button type="button" class="domain-select"><i class="fa fa-angle-down"></i></button>
                <?php } ?>
            </div>

            <?php if ($domainDropDown && string_strpos($_SERVER['PHP_SELF'], 'registration.php') === false && string_strpos($_SERVER['PHP_SELF'], 'forgot.php') === false && string_strpos($_SERVER['PHP_SELF'], 'resetpassword.php') === false && string_strpos($_SERVER['PHP_SELF'], 'setlogin.php') === false) { ?>
                <div class="domain-list">
                    <?php foreach ($domainDropDown as $domainItem) { ?>
                        <a href="javascript:void(0);" class="domain-item <?=($domainItem['id'] == SELECTED_DOMAIN_ID ? 'domain-active' : '');?>" data-id="<?=$domainItem['id'];?>" <?=($domainItem['disabled'] ? '' : $domainItem['onclick'])?>><?=$domainItem['name'];?></a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <?php if(sess_isSitemgrLogged() && string_strpos($_SERVER['PHP_SELF'], 'registration.php') === false && string_strpos($_SERVER['PHP_SELF'], 'resetpassword.php') === false && string_strpos($_SERVER['PHP_SELF'], 'setlogin.php') === false){ ?>
        <div class="header-actions">
            <a href="<?=$domainURL?>" class="visit-website" target="_blank" data-mixpanel-event="Clicked on the link Visit website"><?=LANG_SITEMGR_VISIT_WEBSITE?></a>

            <a href="<?=$supportUrl?>" class="notify-list" data-toggle="tooltip" data-placement="bottom" title="<?=system_showText(LANG_SITEMGR_SUPPORT);?>" target="_blank"  data-mixpanel-event="Clicked on the question mark for the support portal"><i class="fa fa-question-circle-o"></i></a>

            <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_SUPERADMIN)) { ?>
                <a href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/support/" class="notify-list" data-toggle="tooltip" data-placement="bottom" title="Config Checker">
                    <i class="fa fa-cog"></i>
                </a>
            <?php } ?>

            <?php if (sess_isSitemgrLogged() && string_strpos($_SERVER['PHP_SELF'], 'registration.php') === false && string_strpos($_SERVER['PHP_SELF'], 'resetpassword.php') === false && string_strpos($_SERVER['PHP_SELF'], 'setlogin.php') === false) { ?>
                <div class="user-actions">
                    <button type="button" class="user-account">
                        <span class="user-avatar">
                            <?php if(!empty($imageTagNavbar)){ ?>
                                <?=$imageTagNavbar;?>
                            <?php } else { ?>
                                <?=$sitemgrAbv;?>
                            <?php } ?>
                        </span>
                        <span class="user-name"><?=$sitemgrName?></span>
                        <i class="fa fa-angle-down"></i>
                    </button>
                    <div class="user-dropdown">
                        <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/account/myaccount.php';?>" class="dropdown-link <?=(string_strpos($_SERVER['PHP_SELF'], '/account/myaccount.php') !== false ? 'is-active' : '')?>" id="user-links-my-account"><?=system_showText(LANG_MENU_ACCOUNT);?></a>

                        <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_SITES)) { ?>
                            <a href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/sites/" class="dropdown-link <?=(string_strpos($_SERVER['PHP_SELF'], '/sites/') !== false ? 'is-active' : '')?>" id="user-links-sites"><?=LANG_SITEMGR_SITES?></a>
                        <?php } ?>

                        <div class="dropdown-divider"></div>
                        <a href="<?=$domainURL?>" class="dropdown-link dropdown-links-outside dropdown-link-visit" target="_blank"><?=LANG_SITEMGR_VISIT_WEBSITE?></a>
                        <a href="<?=$supportUrl?>" class="dropdown-link dropdown-links-outside dropdown-link-support" target="_blank" data-mixpanel-event="Clicked on Suport link at dashboard header"><?=LANG_SITEMGR_SUPPORT?></a>

                        <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_SUPERADMIN)) { ?>
                            <a href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/support/" class="dropdown-link dropdown-links-outside dropdown-link-config">Config checker</a>
                        <?php } ?>

                        <div class="dropdown-divider"></div>

                        <a href="javascript:void(0);" class="dropdown-link" data-toggle="modal" data-target="#about-modal"><?=LANG_SITEMGR_LABEL_ABOUT?></a>
                        <a href="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/logout.php" class="dropdown-link"><?=system_showText(LANG_SITEMGR_MENU_LOGOUT)?></a>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <input type="hidden" value="<?= SELECTED_DOMAIN_ID ?>" id="edDomainId">
