<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/submenu-content.php
	# ----------------------------------------------------------------------------------------------------

    $subMenuManage = true;
    $bulkUpdateOption = true;
    $labelAddMultItem = null;
    if (string_strpos($_SERVER['PHP_SELF'], LISTING_FEATURE_FOLDER. '/index.php') !== false) {
        $linkAddItem = DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .LISTING_FEATURE_FOLDER. '/listing.php';
        $labelAddItem = system_showText(LANG_MENU_ADDLISTING);
        $manageSearch = true;
        $moduleFolder = LISTING_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_LISTING_PLURAL;
    } elseif (string_strpos($_SERVER['PHP_SELF'], LISTING_FEATURE_FOLDER. '/categories/index.php') !== false) {
        $linkAddItem = false;
        $labelAddItem = system_showText(LANG_SITEMGR_LANG_SITEMGR_CREATECATEGORY);
        $labelAddMultItem = system_showText(LANG_SITEMGR_LANG_SITEMGR_BULKCREATOR);
        $manageSearch = false;
        $moduleFolder = LISTING_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_LISTING_PLURAL;
        $categoryItem = true;
    } elseif (string_strpos($_SERVER['PHP_SELF'], LISTING_FEATURE_FOLDER. '/claim/index.php') !== false) {
        $linkAddItem = false;
        $labelAddItem = false;
        $manageSearch = true;
        $bulkUpdateOption = false;
        $moduleFolder = LISTING_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_LISTING_PLURAL;
    } elseif (string_strpos($_SERVER['PHP_SELF'], ARTICLE_FEATURE_FOLDER. '/index.php') !== false) {
        $linkAddItem = DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .ARTICLE_FEATURE_FOLDER. '/article.php';
        $labelAddItem = system_showText(LANG_MENU_ADDARTICLE);
        $manageSearch = true;
        $moduleFolder = ARTICLE_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_ARTICLE_PLURAL;
    } elseif (string_strpos($_SERVER['PHP_SELF'], ARTICLE_FEATURE_FOLDER. '/categories/index.php') !== false) {
        $linkAddItem = false;
        $labelAddItem = system_showText(LANG_SITEMGR_LANG_SITEMGR_CREATECATEGORY);
        $labelAddMultItem = system_showText(LANG_SITEMGR_LANG_SITEMGR_BULKCREATOR);
        $manageSearch = false;
        $moduleFolder = ARTICLE_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_ARTICLE_PLURAL;
        $categoryItem = true;
    }  elseif (string_strpos($_SERVER['PHP_SELF'], CLASSIFIED_FEATURE_FOLDER. '/index.php') !== false) {
        $linkAddItem = DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .CLASSIFIED_FEATURE_FOLDER. '/classified.php';
        $labelAddItem = system_showText(LANG_MENU_ADDCLASSIFIED);
        $manageSearch = true;
        $moduleFolder = CLASSIFIED_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_CLASSIFIED_PLURAL;
    } elseif (string_strpos($_SERVER['PHP_SELF'], CLASSIFIED_FEATURE_FOLDER. '/categories/index.php') !== false) {
        $linkAddItem = false;
        $labelAddItem = system_showText(LANG_SITEMGR_LANG_SITEMGR_CREATECATEGORY);
        $labelAddMultItem = system_showText(LANG_SITEMGR_LANG_SITEMGR_BULKCREATOR);
        $manageSearch = false;
        $moduleFolder = CLASSIFIED_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_CLASSIFIED_PLURAL;
        $categoryItem = true;
    } elseif (string_strpos($_SERVER['PHP_SELF'], EVENT_FEATURE_FOLDER. '/index.php') !== false) {
        $linkAddItem = DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .EVENT_FEATURE_FOLDER. '/event.php';
        $labelAddItem = system_showText(LANG_MENU_ADDEVENT);
        $manageSearch = true;
        $moduleFolder = EVENT_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_EVENT_PLURAL;
    } elseif (string_strpos($_SERVER['PHP_SELF'], EVENT_FEATURE_FOLDER. '/categories/index.php') !== false) {
        $linkAddItem = false;
        $labelAddItem = system_showText(LANG_SITEMGR_LANG_SITEMGR_CREATECATEGORY);
        $labelAddMultItem = system_showText(LANG_SITEMGR_LANG_SITEMGR_BULKCREATOR);
        $manageSearch = false;
        $moduleFolder = EVENT_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_EVENT_PLURAL;
        $categoryItem = true;
    } elseif (string_strpos($_SERVER['PHP_SELF'], BLOG_FEATURE_FOLDER. '/index.php') !== false) {
        $linkAddItem = DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .BLOG_FEATURE_FOLDER. '/blog.php';
        $labelAddItem = system_showText(LANG_MENU_ADDPOST);
        $manageSearch = true;
        $moduleFolder = BLOG_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_POST_BLOG_PLURAL;
    } elseif (string_strpos($_SERVER['PHP_SELF'], BLOG_FEATURE_FOLDER. '/categories/index.php') !== false) {
        $linkAddItem = false;
        $labelAddItem = system_showText(LANG_SITEMGR_LANG_SITEMGR_CREATECATEGORY);
        $labelAddMultItem = system_showText(LANG_SITEMGR_LANG_SITEMGR_BULKCREATOR);
        $manageSearch = false;
        $moduleFolder = BLOG_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_POST_BLOG_PLURAL;
        $categoryItem = true;
    } elseif (string_strpos($_SERVER['PHP_SELF'], PROMOTION_FEATURE_FOLDER. '/index.php') !== false) {
        $linkAddItem = DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .PROMOTION_FEATURE_FOLDER. '/deal.php';
        $labelAddItem = system_showText(LANG_MENU_ADDPROMOTION);
        $manageSearch = true;
        $moduleFolder = PROMOTION_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_PROMOTION_PLURAL;
        $subMenuManage = false;
    } elseif (string_strpos($_SERVER['PHP_SELF'], BANNER_FEATURE_FOLDER. '/index.php') !== false) {
        $linkAddItem = DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .BANNER_FEATURE_FOLDER. '/banner.php';
        $labelAddItem = system_showText(LANG_MENU_ADDBANNER);
        $manageSearch = true;
        $moduleFolder = BANNER_FEATURE_FOLDER;
        $labelManage = LANG_SITEMGR_BANNER_PLURAL;
        $subMenuManage = false;
    } elseif (string_strpos($_SERVER['PHP_SELF'], 'reviews-comments/index.php') !== false) {
        $linkAddItem = false;
        $labelAddItem = false;
        $manageSearch = true;
        $moduleFolder = 'reviews-comments';
        $subMenuManage = true;
    }  elseif (string_strpos($_SERVER['PHP_SELF'], 'leads/index.php') !== false) {
        $linkAddItem = false;
        $labelAddItem = false;
        $manageSearch = true;
        $moduleFolder = 'lead';
        $subMenuManage = true;
    } elseif (string_strpos($_SERVER['PHP_SELF'], 'terms/index.php') !== false) {
        $linkAddItem = false;
        $labelAddItem = false;
        $manageSearch = true;
        $moduleFolder = 'geography/terms';
        $subMenuManage = false;
    } elseif (string_strpos($_SERVER['PHP_SELF'], LISTING_FEATURE_FOLDER. '/template') !== false) {
        $linkAddItem  = 'javascript:void(0)';
        $labelAddItem = system_showText(LANG_SITEMGR_ADD_LISTING_TEMPLATE);
        $manageSearch = false;
        $moduleFolder = LISTING_FEATURE_FOLDER;
        $labelManage  = LANG_SITEMGR_LISTING_PLURAL;
        $buttonClass  = 'addNewListingTemplate';
    }elseif (string_strpos($_SERVER['PHP_SELF'], 'account/sponsor/') !== false) {
        $linkAddItem = DEFAULT_URL."/".SITEMGR_ALIAS."/account/sponsor/sponsor.php";
        $labelAddItem = system_showText(LANG_SITEMGR_ADD_SPONSOR);
        $manageSearch = true;
        $moduleFolder = 'accounts';
        $subMenuManage = true;
    }elseif (string_strpos($_SERVER['PHP_SELF'], '/account/visitor/') !== false){
        $linkAddItem = DEFAULT_URL."/".SITEMGR_ALIAS."/account/visitor/visitor.php";
        $labelAddItem = system_showText(LANG_SITEMGR_ADD_VISITOR);
        $manageSearch = true;
        $moduleFolder = 'accounts';
        $subMenuManage = true;
    }elseif (string_strpos($_SERVER['PHP_SELF'], '/account/manager/') !== false){
        $linkAddItem = DEFAULT_URL."/".SITEMGR_ALIAS."/account/manager/manager.php";
        $labelAddItem = system_showText(LANG_SITEMGR_ADD_MANAGER);
        $manageSearch = true;
        $moduleFolder = 'accounts';
        $subMenuManage = true;
    }

    $subMenuWrapper = false;

    if ($moduleFolder != "geography/terms" && $moduleFolder != "reviews-comments" && $moduleFolder != "lead" && $moduleFolder != "accounts" && $subMenuManage) {
        $subMenuWrapper = true;
    }

    /* ModStores Hooks */
    HookFire( 'submenucontent_after_load_modules', [
        'linkClaim'          => &$linkClaim,
        'manageSearch'       => &$manageSearch,
        'moduleFolder'       => &$moduleFolder,
        'labelManage'        => &$labelManage,
        'subMenuManage'      => &$subMenuManage,
        'labelAddItem'       => &$labelAddItem,
        'linkAddItem'        => &$linkAddItem,
        'manageModuleFolder' => &$manageModuleFolder,
        'categoryItem'      => &$categoryItem,
        'labelAddMultItem'  => &$labelAddMultItem,
    ]);

    if($manageSearch && !$labelAddItem && !$labelAddMultItem){
        $wrapperClass = 'has-form';
    } elseif (!$manageSearch && ($labelAddItem || $labelAddMultItem)) {
        $wrapperClass = 'has-action';
    } else {
        $wrapperClass = '';
    }
?>
    <div class="content-control header-bar <?=$wrapperClass;?>" id="search-all">
        <?php if ($manageSearch) { ?>
        <form class="form-inline" role="search" action="<?=system_getFormAction($_SERVER["PHP_SELF"]);?>" method="get">
            <div class="control-searchbar">
                <?php if ($bulkUpdateOption) { ?>
                    <div class="bulk-check-all">
                        <label class="sr-only">Check all</label>
                        <input type="checkbox" id="check-all">
                    </div>
                <?php } ?>
                <?php if ($moduleFolder != "reviews-comments" && $moduleFolder != "lead" ) { ?>
                    <div class="form-group">
                        <div class="input-group input-group-sm">
                            <?php if ($moduleFolder != "accounts") { ?>
                                <input type="text" class="form-control search hidden-xs" name="search_title" value="<?=$search_title;?>" onblur="populateField(this.value, 'search_title');" placeholder="<?=system_showText(LANG_LABEL_SEARCHKEYWORD);?>">
                            <?php }else{ ?>
                                <input type="text" name="search_username" value="<?=$search_username?>" class="form-control search" placeholder="<?=system_showText(LANG_SITEMGR_SEARCH_ACC);?>">
                            <?php } ?>
                            <div class="input-group-btn">
                                <!-- Button and dropdown menu -->
                                <button class="btn btn-default <?=$moduleFolder != "accounts"?"hidden-xs":"" ?>" onclick="$('#search').submit();"><?=system_showText(LANG_SITEMGR_SEARCH);?></button>
                                <?php if ($moduleFolder != "geography/terms" && $moduleFolder != "accounts") { ?>
                                <button type="button" class="btn btn-default dropdown-toggle"  data-toggle="modal" data-target="#modal-search" >
                                    <span class="hidden-xs caret"></span>
                                    <i class="visible-xs fa fa-search"></i>
                                </button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </form>
        <?php } ?>

        <?php if ($subMenuWrapper) { ?>
            <div class="header-bar-action">
                <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".$moduleFolder."/"?>" class="action-button <?=(string_strpos($_SERVER["PHP_SELF"], $moduleFolder."/index.php") !== false ? "is-active" : "")?>"><?=string_ucwords(system_showText($labelManage))?></a>
                <?php
                    /* ModStores Hooks */
                    HookFire( "submenucontent_after_render_modulebutton", [
                        "moduleFolder" => &$moduleFolder
                    ]);
                ?>

                <a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".$moduleFolder."/categories/"?>" class="action-button <?=(string_strpos($_SERVER["PHP_SELF"], $moduleFolder."/categories/index.php") !== false ? "is-active" : "")?>"><?=system_showText(LANG_SITEMGR_NAVBAR_CATEGORIES)?></a>
                <?php
                    /* ModStores Hooks */
                    HookFire( "submenucontent_after_render_categorybutton", [
                        "moduleFolder" => &$moduleFolder,
                    ]);
                ?>

                <?php if($moduleFolder === LISTING_FEATURE_FOLDER) { ?>
                    <a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .$moduleFolder. '/template/' ?>" class="action-button <?=(string_strpos($_SERVER['PHP_SELF'], $moduleFolder. '/template/index.php') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_PLURAL)?></a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ($labelAddItem || $labelAddMultItem) { ?>
            <div class="control-bar">
                <?php if($subMenuWrapper){ ?>
                    <div class="btn-group visible-md visible-sm visible-xs">
                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".$moduleFolder."/"?>" class="<?=(string_strpos($_SERVER["PHP_SELF"], $moduleFolder."/index.php") !== false ? "is-active" : "")?>"><?=string_ucwords(system_showText($labelManage))?></a></li>
                            <li><a href="<?=DEFAULT_URL."/".SITEMGR_ALIAS."/content/".$moduleFolder."/categories/"?>" class="<?=(string_strpos($_SERVER["PHP_SELF"], $moduleFolder."/categories/index.php") !== false ? "is-active" : "")?>"><?=system_showText(LANG_SITEMGR_MENU_MANAGECATEGORIES)?></a></li>
                            <?php if($moduleFolder === LISTING_FEATURE_FOLDER) { ?>
                                <li><a href="<?=DEFAULT_URL. '/' .SITEMGR_ALIAS. '/content/' .$moduleFolder. '/template/' ?>" class="<?=(string_strpos($_SERVER['PHP_SELF'], $moduleFolder. '/template/index.php') !== false ? 'is-active' : '')?>"><?=system_showText(LANG_SITEMGR_LISTINGTEMPLATE_PLURAL)?></a></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <?php if($categoryItem) { ?>
                    <a class="btn btn-sm btn-primary <?= $buttonClass ?? '' ?>" id="add-categories" data-domain="<?= SELECTED_DOMAIN_ID ?>" data-toggle="modal" data-target="#modal-create-categories">
                        <i class="fa fa-plus"></i> <span><?=$labelAddItem;?></span>
                    </a>
                <?php } else { ?>
                    <a class="btn btn-sm btn-primary <?=$buttonClass ?? ''?>" href="<?=$linkAddItem?>" data-domain="<?= SELECTED_DOMAIN_ID ?>">
                        <i class="fa fa-plus"></i> <span><?=$labelAddItem;?></span>
                    </a>
                <?php } ?>

                <?php if ($labelAddMultItem) { ?>
                    <a data-mixpanel-event='Clicked on button "Add multiple categories" on Manage <?= $moduleFolder ?> Categories section' class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal-add-mult-categories" id="add-mult-categories">
                        <i class="fa fa-clone"></i> <span><?=$labelAddMultItem;?></span>
                    </a>
                <?php } ?>

            </div>
        <?php } ?>
    </div>
