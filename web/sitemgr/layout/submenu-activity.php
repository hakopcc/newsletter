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
    $linkAddItem = false;
    $labelAddItem = false;
    $linkClaim = true;
    $manageSearch = true;
    $bulkUpdateOption = false;
    $moduleFolder = LISTING_FEATURE_FOLDER;
    $labelManage = LANG_SITEMGR_LISTING_PLURAL;

    /* ModStores Hooks */
    HookFire( 'submenuactivity_after_load_modules', [
        'linkClaim'          => &$linkClaim,
        'manageSearch'       => &$manageSearch,
        'moduleFolder'       => &$moduleFolder,
        'labelManage'        => &$labelManage,
        'subMenuManage'      => &$subMenuManage,
        'labelAddItem'       => &$labelAddItem,
        'linkAddItem'        => &$linkAddItem,
        'manageModuleFolder' => &$manageModuleFolder,
    ]);
?>
    <div class="content-control header-bar" id="search-all">
        <form class="form-inline" role="search" action="<?=system_getFormAction($_SERVER["PHP_SELF"]);?>" method="get">
            <div class="control-searchbar">
                <div class="bulk-check-all">
                    <label class="sr-only">Check all</label>
                    <input type="checkbox" id="check-all">
                </div>
                <div class="form-group">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control search hidden-xs" name="search_title" value="<?=$search_title?>" onblur="populateField(this.value, 'search_title');" placeholder="<?=system_showText(LANG_LABEL_SEARCHKEYWORD);?>">
                        <div class="input-group-btn">
                            <!-- Button and dropdown menu -->
                            <button class="btn btn-default hidden-xs" onclick="$('#search').submit();"><?=system_showText(LANG_SITEMGR_SEARCH);?></button>
                            <button type="button" class="btn btn-default dropdown-toggle"  data-toggle="modal" data-target="#modal-search" >
                                <span class="hidden-xs caret"></span>
                                <i class="visible-xs icon-ion-ios7-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="control-responsive">
            <span class="btn btn-info btn-responsive" data-toggle="dropdown" title="Groups"><i class="icon-ion-ios7-folder-outline"></i></span>
        </div>
    </div>
</div>
