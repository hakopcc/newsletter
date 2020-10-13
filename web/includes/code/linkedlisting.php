<?php
$listingObj = new Listing($id);
$level = $listingObj->getLevel($id);

$container = SymfonyCore::getContainer();

$linkedListingField = $container->get('doctrine')->getRepository('ListingBundle:ListingTField')->find($fieldId);
$listings = $container->get('listing.service')->getLinkedListings($id, $linkedListingField);

$limit = current(system_getFormFields('listing', $level, null, $fieldId)['listingtfield_id']) ?? 0;
