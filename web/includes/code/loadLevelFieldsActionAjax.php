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
# * FILE: /includes/code/loadLevelFieldsActionAjax.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
use ArcaSolutions\ListingBundle\Entity\ListingLevelField;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\ListingBundle\Entity\ListingTFieldGroup;

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

$fields = [
    ListingLevelField::COVER_IMAGE,
    ListingLevelField::DESCRIPTION,
    ListingLevelField::IMAGES,
    ListingLevelField::REVIEW,
    ListingLevelField::DEALS,
    ListingLevelField::SOCIAL_NETWORK,
    ListingLevelField::CLASSIFIEDS,
    ListingLevelField::LOCATIONS,
    ListingLevelField::FEATURES,
    ListingLevelField::BADGES,
    ListingLevelField::HOURS_WORK,
    ListingLevelField::LONG_DESCRIPTION,
    ListingLevelField::ATTACHMENT_FILE,
    ListingLevelField::VIDEO,
    ListingLevelField::ADDITIONAL_PHONE,
    ListingLevelField::PHONE,
    ListingLevelField::EMAIL,
    ListingLevelField::URL,
    ListingLevelField::LOGO_IMAGE
];

$levelFields = $container->get('listinglevelfield.service')->getListingLevelFieldsNameByLevel($level);

$displayFields = [];
$blockFields = [];
foreach($fields as $field) {
    if(in_array($field, array_column($levelFields, 'field'), true)) {
        $displayFields[] = $field;
    } else {
        $blockFields[] = $field;
    }
}

if($templateId) {
    $template = $container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->find($templateId);

    $customFields = $container->get('listingtemplatefield.service')->getCustomFieldsByTemplate($template);

    foreach($customFields as $customField) {
        $customLevelFields = $container->get('listinglevelfield.service')->getListingLevelFieldsByTemplateAndLevel($templateId, $level);

        if ($customField instanceof ListingTField) {
            if(!empty($customLevelFields['listingtfield_id']) && in_array($customField->getId(), array_column($customLevelFields['listingtfield_id'], 'listingTFieldId'), true)) {
                $displayFields[] = 'field-' . $customField->getId();
            } else {
                $blockFields[] = 'field-' . $customField->getId();
            }
        } elseif ($customField instanceof ListingTFieldGroup) {
            if (!empty($customLevelFields['listingtfieldgroup_id']) && in_array($customField->getId(), array_column($customLevelFields['listingtfieldgroup_id'], 'listingTFieldGroupId'), true)) {
                $displayFields[] = 'group-' . $customField->getId();
            } else {
                $blockFields[] = 'group-' . $customField->getId();
            }
        }
    }
}

echo json_encode([
    'displayFields'     => $displayFields,
    'blockFields'       => $blockFields
]);
