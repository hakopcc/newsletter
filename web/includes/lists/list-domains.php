<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/lists/list-domains.php
	# ----------------------------------------------------------------------------------------------------
    
    if ($domains) {
        
        foreach ($domains as $domain) {
            
            $id = $domain->getNumber("id");
            $url = $domain->getString("url");
            $domain->changeActivationStatus();
            $itemStatus = new ItemStatus();
        ?>

        <div class="col-md-4 col-sm-6 col-xs-12">
           

            <div class="panel panel-domain">
                <div class="panel-heading">
                    <?=$domain->getString("name");?>
                    <div class="btn-group pull-right">
                        <a href="javascript:void(0);" onclick="changeDomainInfo(<?=$id?>, '<?=DEFAULT_URL?>', '<?="/".SITEMGR_ALIAS."/"?>', '<?=$_SERVER["QUERY_STRING"]?>', 'false')" title="<?=system_showText(LANG_SITEMGR_MANAGE_SITE);?>" class="btn btn-default btn-xs"> <?=system_showText(LANG_SITEMGR_MANAGE_SITE);?></a>
                        <? if (!sess_getSMIdFromSession() && $id != SELECTED_DOMAIN_ID) { ?>
                        <a data-toggle="modal" class="btn btn-default btn-xs" data-target="#modal-delete" href="#" onclick="$('#delete-id').val(<?=$id?>)" title="<?=system_showText(LANG_LABEL_DELETE);?>"><?=system_showText(LANG_LABEL_DELETE);?></a>
                        <? } ?>
                    </div>
                </div>
                <div class="panel-body">
                    <p><em><?=$url;?></em></p> 
                    <p><a href="http://<?=$url;?>" target="_blank"><?=system_showText(LANG_SITEMGR_VISIT_SITE);?></a></p>                 
                    
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th><?=string_ucwords(system_showText(LANG_SITEMGR_DATECREATED))?></th>
                            <th><?=string_ucwords(system_showText(LANG_SITEMGR_DOMAIN_ACTIVATION))?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?=$domain->getDate("created");?></td>
                            <td><?=$itemStatus->getStatusWithStyle($domain->getString("activation_status"))?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        
        </div>

         <? }
        } ?>
