<?php

/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2020 Arca Solutions, Inc. All Rights Reserved.           #
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
# * FILE: /includes/code/loadCustomFieldsActionAjax.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
use Symfony\Component\HttpFoundation\JsonResponse;

if (isset($_GET["domain_id"]) && is_numeric($_GET["domain_id"])) {
    define("SELECTED_DOMAIN_ID", $_GET["domain_id"]);
}
$loadSitemgrLangs = true;

include '../../conf/loadconfig.inc.php';

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSMSession();

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------

$templateId = $_GET["templateId"];
$listingId = $_GET["listingId"];
$level = $_GET["level"];

$container = SymfonyCore::getContainer();

$fieldValues = $container->get('listingfieldvalue.service')->getFieldValues($listingId);

if(empty($level)) {
    $levelFields = $container->get('listinglevelfield.service')->getListingLevelFieldsByTemplate($templateId);
} else {
    $levelFields = $container->get('listinglevelfield.service')->getListingLevelFieldsByTemplateAndLevel($templateId, $level);
}

$templateFieldsBlock = $container->get('listingtemplatefield.service')->renderListingTemplateFields($templateId, $levelFields, $fieldValues);

echo json_encode([
    'block' => $templateFieldsBlock
]);
