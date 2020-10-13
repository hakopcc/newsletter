<?

/*==================================================================*\
######################################################################
#                                                                    #
# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
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
# * FILE: /includes/code/linkedListingActionAjax.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
use ArcaSolutions\ListingBundle\Entity\LinkedListings;

if (isset($_POST["domain_id"]) && is_numeric($_POST["domain_id"])) {
    define("SELECTED_DOMAIN_ID", $_POST["domain_id"]);
} elseif (isset($_GET["domain_id"]) && is_numeric($_GET["domain_id"])) {
    define("SELECTED_DOMAIN_ID", $_GET["domain_id"]);
}
$loadSitemgrLangs = true;

include('../../conf/loadconfig.inc.php');

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
if(!empty($_SESSION['SM_LOGGEDIN'])){
    sess_validateSMSession();
} else {
    sess_validateSession();
}

$container = SymfonyCore::getContainer();
$listingService = $container->get('listing.service');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'saveLinkedListings') {
        $listingTField = $container->get('doctrine')->getRepository('ListingBundle:ListingTField')->find($_POST['fieldId']);
        $locale = substr($container->get('settings')->getSetting('sitemgr_language'), 0, 2);
        $em = $container->get('doctrine')->getManager();

        if(!empty($_POST['linked_listings'])) {
            /** @var \ArcaSolutions\ListingBundle\Entity\Listing $listing */
            $sourceListing = $container->get('doctrine')->getRepository('ListingBundle:Listing')->find($_POST['listing_id']);
            $linkedListings = [];

            $listingService->clearLinkedListingsBySource($sourceListing, $listingTField);

            foreach ($_POST['linked_listings'] as $order => $linked_listing) {
                $linkedListing = $container->get('doctrine')->getRepository('ListingBundle:Listing')->find($linked_listing);

                $linkedListingAssociation = new LinkedListings();
                $linkedListingAssociation->setSourceListing($sourceListing);
                $linkedListingAssociation->setLinkedListing($linkedListing);
                $linkedListingAssociation->setField($listingTField);
                $linkedListingAssociation->setOrder($order);

                $em->persist($linkedListingAssociation);
            }
        } else {
            /** @var \ArcaSolutions\ListingBundle\Entity\Listing $sourceListing */
            $sourceListing = $container->get('doctrine')->getRepository('ListingBundle:Listing')->find($_POST['listing_id']);

            $linkedListings = $sourceListing->getLinkedListings()->getValues();

            if(!empty($linkedListings)) {
                foreach ($linkedListings as $linkedListing) {
                    $em->remove($linkedListing);
                }
            }
        }

        $em->flush();

        $return = [
            'success' => true,
            'message' => $container->get('translator')->trans('Linked listings saved successfully', [], 'administrator', $locale),
        ];

        echo json_encode($return);
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if($_GET['action'] === 'search') {
        $template = '';

        $listingService->buildListingContainerByTerm($template, $_GET['term'], $_GET['addedListings'], $_GET['accountId']);

        if (!empty($template)) {
            echo json_encode([
                'success' => 'true',
                'template' => $template
            ]);
        } else {
            echo json_encode([
                'success' => 'false'
            ]);
        }
    }
}
