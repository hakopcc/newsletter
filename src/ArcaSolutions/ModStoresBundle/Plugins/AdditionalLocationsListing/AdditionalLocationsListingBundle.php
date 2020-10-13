<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdditionalLocationsListing;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\ModStoresBundle\Plugins\AdditionalLocationsListing\Entity\ListingExtraLocation;
use ArcaSolutions\ModStoresBundle\Plugins\AdditionalLocationsListing\Entity\ListingLevelFieldLocations;
use Exception;
use Ivory\GoogleMap\Base\Bound;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Event\Event;
use Ivory\GoogleMap\Overlay\Icon;
use Ivory\GoogleMap\Overlay\Marker;
use Ivory\GoogleMap\Overlay\MarkerCluster;
use Ivory\GoogleMap\Overlay\MarkerClusterType;
use ListingLevel;
use Symfony\Component\Translation\TranslatorInterface;

class AdditionalLocationsListingBundle extends Bundle
{
    /**
     * Method just to allow JMSTranslator include know strings that was not used directly by a trans method call or by a trans twig extension
     */
    private function dummyMethodToIncludeTranslatableString(){
        return;
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get('translator');
        if ($translator !== null) {
            $translator->trans('locationCount', array(), 'advertise');
        }
        unset($translator);
    }

    private $devEnvironment = false;
    /**
     * Boots the Bundle.
     */
    public function boot()
    {

        $logger = $this->container->get('logger');
        $notLoggedCriticalException = null;
        try {
            $this->devEnvironment = Kernel::ENV_DEV === $this->container->getParameter('kernel.environment');
            if ($this->isSitemgr()) {

                /*
                 * Register sitemgr only bundle hooks
                 */
                Hooks::Register('formpricing_after_add_fields', function (&$params = null) {
                    return $this->getFormPricingAfterAddFields($params);
                });
                Hooks::Register('paymentgateway_after_save_levels', function (&$params = null) {
                    return $this->getPaymentGatewayAfterSaveLevels($params);
                });
                Hooks::Register('formlevels_render_fields', function (&$params = null) {
                    return $this->getFormLevelsRenderFields($params);
                });
                Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                    return $this->getModulesFooterAfterRenderJs($params);
                });
                Hooks::Register('listingcode_after_save', function (&$params = null) {
                    return $this->getListingCodeAfterSave($params);
                });
                Hooks::Register('listingsynchronizable_before_return_document', function (&$params = null) {
                    return $this->getListingSynchronizableBeforeReturnDocument($params);
                });
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    return $this->getClassListingBeforeDelete($params);
                });
                Hooks::Register('listingcode_after_setup_form', function (&$params = null) {
                    return $this->getListingCodeAfterSetupForm($params);
                });
                Hooks::Register('classlisting_before_update_fulltextwhere', function (&$params = null) {
                    return $this->getMultipleLocationsFulltextsearch($params);
                });

            } else {
                Hooks::Register('detailextension_before_increaseoverviewcount', function (&$params = null) {
                    return $this->getDetailExtensionBeforeIncreaseOverviewCount($params);
                });
                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('listingcode_after_save', function (&$params = null) {
                    return $this->getListingCodeAfterSave($params);
                });
                Hooks::Register('listinglevel_construct', function (&$params = null) {
                    return $this->getListingLevelConstruct($params);
                });
                Hooks::Register('listinglevelfeature_before_return', function (&$params = null) {
                    return $this->getListingLevelFeatureBeforeReturn($params);
                });
                Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                    return $this->getModulesFooterAfterRenderJs($params);
                });
                Hooks::Register('listing_after_validate_itemdetail', function (&$params = null) {
                    return $this->getListingAfterValidateItemDetail($params);
                });
                Hooks::Register('listingsynchronizable_before_return_document', function (&$params = null) {
                    return $this->getListingSynchronizableBeforeReturnDocument($params);
                });
                Hooks::Register('classlisting_before_delete', function (&$params = null) {
                    return $this->getClassListingBeforeDelete($params);
                });
                Hooks::Register('listingcode_after_setup_form', function (&$params = null) {
                    return $this->getListingCodeAfterSetupForm($params);
                });
                Hooks::Register('listingsample_before_add_globalvars', function (&$params = null) {
                    return $this->getListingSampleBeforeAddGlobalVars($params);
                });
                Hooks::Register('searchengine_before_setup_mapclusters', function (&$params = null) {
                    return $this->getSearchEngineBeforeSetupMapClusters($params);
                });
                Hooks::Register('mapsummary_before_set_data', function (&$params = null) {
                    return $this->getMapSummaryBeforeSetData($params);
                });
                Hooks::Register('classlisting_before_update_fulltextwhere', function (&$params = null) {
                    return $this->getMultipleLocationsFulltextsearch($params);
                });
                Hooks::Register('listing_before_buildmapJSHelper', function (&$params = null) {
                    return $this->getListingBeforeBuildMapJSHelper($params);
                });

            }

            // Todo: revise hooks names
            Hooks::Register('sitemgr_code_ml_snippet_1', function (&$params = null) {
                return $this->getCodeMLSnippet1($params);
            });
            Hooks::Register('sitemgr_code_ml_snippet_2', function (&$params = null) {
                return $this->getCodeMLSnippet2($params);
            });
            Hooks::Register('sitemgr_ml_form', function (&$params = null) {
                return $this->getMlForm($params);
            });
            Hooks::Register('sitemgr_custom_js_locationjs', function (&$params = null) {
                return $this->customLocationJs($params);
            });
            Hooks::Register('listing_detail_multiplelocations', function (&$params = null) {
                return $this->listingDetailMl($params);
            });
            Hooks::Register('load_map_ml', function (&$params = null) {
                return $this->loadMapFunct($params);
            });
            Hooks::Register('listingsummary_after_extract_data', function (&$params = null) {
                return $this->getCustomSummaryData($params);
            });
            Hooks::Register('listingmapsummary_after_extract_data', function (&$params = null) {
                return $this->getCustomSummaryDataMap($params);
            });
            Hooks::Register('multiple_locations_moreplaces_link', function (&$params = null) {
                return $this->getMultipleLocationsMoreplacesLink($params);
            });
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of AdditionalLocationsListingBundle.php', ['exception' => $e]);
            } else {
                $notLoggedCriticalException = $e;
            }
        } finally {
            unset($logger);
            if (!empty($notLoggedCriticalException)) {
                throw $notLoggedCriticalException;
            }
        }
    }

    private function getDetailExtensionBeforeIncreaseOverviewCount(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $listingLevel = $params['listingLevel'];
                $listing = $params['listing'];
                $contentCountRef = &$params['contentCount'];
                $overviewCountRef = &$params['overviewCount'];
                if(!empty($listingLevel) && !empty($listing) && isset($params['contentCount']) && isset($params['overviewCount'])){
                    $doctrine = $this->container->get('doctrine');
                    if(!empty($doctrine)) {
                        $listingExtraLocationRepository = $doctrine->getRepository('AdditionalLocationsListingBundle:ListingExtraLocation');
                        if(!empty($listingExtraLocationRepository)) {
                            $listingExtraLocations = $listingExtraLocationRepository->findBy(['listingId' => $listing->getId()]);
                            if(!empty($listingExtraLocations)){
                                if(count($listingExtraLocations)>0){
                                    $contentCountRef++;
                                    $overviewCountRef++;
                                }
                            }
                            unset($listingExtraLocations);
                        }
                        unset($listingExtraLocationRepository);
                    }
                    unset($doctrine);
                }
                unset($listingLevel,
                    $listing);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getDetailExtensionBeforeIncreaseOverviewCount method of AdditionalLocationsListingBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    throw $notLoggedCriticalException;
                }
            }
        }
    }

    private function getFormPricingAfterAddFields(&$params = null)
    {
        $translator = $this->container->get('translator');

        if ($params['type'] == 'listing') {
            $params['levelOptions'][] = [
                'name'  => 'extralocations',
                'type'  => 'numeric',
                'title' => $translator->trans('Additional Locations'),
                'tip'   => $translator->trans('Allow owners to add additional locations reference to their listings?'),
                'min'   => 0,
                'max'   => 25,
            ];
        }
    }

    private function getPaymentGatewayAfterSaveLevels(&$params = null)
    {
        if ($params['type'] == 'listing' && $params['levelOptionData']['extralocations']) {

            $doctrine = $this->container->get('doctrine');
            $manager = $this->container->get('doctrine')->getManager();

            foreach ($params['levelOptionData']['extralocations'] as $level => $field) {

                $listingLevel = $doctrine->getRepository('AdditionalLocationsListingBundle:ListingLevelFieldLocations')->findOneBy([
                    'level' => $level,
                ]);

                if ($listingLevel) {
                    $listingLevel->setField($field);
                    $manager->persist($listingLevel);
                } else {
                    $listingLevel = new ListingLevelFieldLocations();
                    $listingLevel->setLevel($level);
                    $listingLevel->setField($field);
                    $manager->persist($listingLevel);
                }
            }

            $manager->flush();
        }
    }

    private function getFormLevelsRenderFields(&$params = null)
    {
        if (is_a($params['levelObj'], 'ListingLevel') && $params['option']['name'] == 'extralocations') {

            $params['levelObj']->extralocations = [];

            $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalLocationsListingBundle:ListingLevelFieldLocations')->findBy([],
                ['level' => 'DESC']);

            if ($resultLevel) {
                foreach ($resultLevel as $levelfield) {
                    $params['levelObj']->extralocations[] = $levelfield->getField();
                }
            }
        }
    }

    private function getModulesFooterAfterRenderJs(&$params = null)
    {
        if ($params['feedName'] == 'listing') {
            echo $this->container->get('templating')->render('AdditionalLocationsListingBundle::js/sitemgr-modules.html.twig',
                [
                    'google_map_status'      => $this->container->get('settings')->getDomainSetting('google_map_status'),
                    'totalExtraLocCoords'    => $this->container->get('modstore.storage.service')->retrieve('totalExtraLocCoords'),
                    'levelMaxExtraLocations' => $this->container->get('modstore.storage.service')->retrieve('levelMaxExtraLocations'),
                ]);
        }
    }

    private function getListingCodeAfterSave(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        extract($_POST);
        $levelMaxExtraLocations = $_POST['levelMaxExtraLocations'];
        $params['listing_id'] = $params['listing']->getNumber('id');

        unset($params['extraLocObj']);

        for ($k = 1; $k <= $levelMaxExtraLocations; $k++) {
            if ($_POST["address_extra_loc_{$k}"] || $_POST["address2_extra_loc_{$k}"] || $_POST["zip_code_extra_loc_{$k}"] || (!empty($location_fist_non_default) && $_POST["location_{$location_fist_non_default}_extra_loc_{$k}"])) {
                unset($params['extraLocObj']);
                unset($fields);
                unset($datas);
                unset($itemsToUpdateArr);

                $fields['listing_id'] = $params['listing_id'];

                if (isset($_POST["address_extra_loc_{$k}"])) {
                    $fields['loc_address'] = $_POST["address_extra_loc_{$k}"];
                }
                if (isset($_POST["address2_extra_loc_{$k}"])) {
                    $fields['loc_address2'] = $_POST["address2_extra_loc_{$k}"];
                }
                if (isset($_POST["zip_code_extra_loc_{$k}"])) {
                    $fields['loc_zip_code'] = $_POST["zip_code_extra_loc_{$k}"];
                }
                if (isset($_POST["location_1_extra_loc_{$k}"])) {
                    $fields['loc_location_1'] = !empty($_POST["location_1_extra_loc_{$k}"]) ? $_POST["location_1_extra_loc_{$k}"] : 0;
                }
                if (isset($_POST["location_2_extra_loc_{$k}"])) {
                    $fields['loc_location_2'] = !empty($_POST["location_2_extra_loc_{$k}"]) ? $_POST["location_2_extra_loc_{$k}"] : 0;
                }
                if (isset($_POST["location_3_extra_loc_{$k}"])) {
                    $fields['loc_location_3'] = !empty($_POST["location_3_extra_loc_{$k}"]) ? $_POST["location_3_extra_loc_{$k}"] : 0;
                }
                if (isset($_POST["location_4_extra_loc_{$k}"])) {
                    $fields['loc_location_4'] = !empty($_POST["location_4_extra_loc_{$k}"]) ? $_POST["location_4_extra_loc_{$k}"] : 0;
                }
                if (isset($_POST["location_5_extra_loc_{$k}"])) {
                    $fields['loc_location_5'] = !empty($_POST["location_5_extra_loc_{$k}"]) ? $_POST["location_5_extra_loc_{$k}"] : 0;
                }
                if (isset($_POST["loc_latitude_{$k}"])) {
                    $fields['loc_latitude'] = $_POST["loc_latitude_{$k}"];
                }
                if (isset($_POST["loc_longitude_{$k}"])) {
                    $fields['loc_longitude'] = $_POST["loc_longitude_{$k}"];
                }
                if (isset($_POST["loc_map_zoom_{$k}"])) {
                    $fields['loc_map_zoom'] = !empty($_POST["loc_map_zoom_{$k}"]) ? $_POST["loc_map_zoom_{$k}"] : 0;
                }
                if (isset($_POST["loc_map_tuning_{$k}"])) {
                    $fields['loc_map_tuning'] = $_POST["loc_map_tuning_{$k}"];
                }

                if ($_POST["extra_loc_{$k}_id"]) {

                    $updateFields = [];
                    foreach ($fields as $field => $value) {
                        $updateFields[] = $field.'= :'.$field;
                    }
                    $updateFields = implode(', ', $updateFields);

                    $statement = $connection->prepare('UPDATE Listing_ExtraLocation SET '.$updateFields.' WHERE id = :id');
                    $statement->bindValue('id', $_POST["extra_loc_{$k}_id"]);
                } else {

                    $insertFields = [];
                    $insertValues = [];
                    foreach ($fields as $field => $value) {
                        $insertFields[] = $field;
                        $insertValues[] = ':'.$field;
                    }
                    $insertFields = implode(', ', $insertFields);
                    $insertValues = implode(', ', $insertValues);

                    $statement = $connection->prepare('INSERT INTO Listing_ExtraLocation ('.$insertFields.') VALUES ('.$insertValues.')');
                }

                foreach ($fields as $field => $value) {
                    $statement->bindValue($field, $value);
                }

                $statement->execute();

            } else if ($_POST["extra_loc_{$k}_id"]) {
                $statement = $connection->prepare('DELETE FROM Listing_ExtraLocation WHERE id = :id');
                $statement->bindValue('id', $_POST["extra_loc_{$k}_id"]);
                $statement->execute();
            }

        }
    }

    private function getListingSynchronizableBeforeReturnDocument(&$params = null)
    {
        $locationIds = [];
        $mainLocationIds = [];
        $extraLocationsIds = [];

        $locationSynchronizable = $this->container->get('location.synchronization');

        /* @var $location1 Location1 */
        if ($location1 = $params['element']->getLocation1()) {
            $locationIds[] = $locationSynchronizable->formatId($location1, 1);
            $mainLocationIds[] = $locationSynchronizable->formatId($location1, 1);
        }

        /* @var $location2 Location2 */
        if ($location2 = $params['element']->getLocation2()) {
            $locationIds[] = $locationSynchronizable->formatId($location2, 2);
            $mainLocationIds[] = $locationSynchronizable->formatId($location2, 2);
        }

        /* @var $location3 Location3 */
        if ($location3 = $params['element']->getLocation3()) {
            $locationIds[] = $locationSynchronizable->formatId($location3, 3);
            $mainLocationIds[] = $locationSynchronizable->formatId($location3, 3);
        }

        /* @var $location4 Location4 */
        if ($location4 = $params['element']->getLocation4()) {
            $locationIds[] = $locationSynchronizable->formatId($location4, 4);
            $mainLocationIds[] = $locationSynchronizable->formatId($location4, 4);
        }

        /* @var $location5 Location5 */
        if ($location5 = $params['element']->getLocation5()) {
            $locationIds[] = $locationSynchronizable->formatId($location5, 5);
            $mainLocationIds[] = $locationSynchronizable->formatId($location5, 5);
        }

        $extralocObj = $this->container->get('doctrine')->getRepository('AdditionalLocationsListingBundle:ListingExtraLocation')->findBy(['listingId' => $params['element']->getId()]);

        foreach ($extralocObj as $extraloc) {

            /* @var $location1 Location1 */
            if ($location1 = $extraloc->getLocation1()) {
                $locationIds[] = $locationSynchronizable->formatId($location1, 1);
                $extraLocationsIds[] = $locationSynchronizable->formatId($location1, 1);
            }

            /* @var $location2 Location2 */
            if ($location2 = $extraloc->getLocation2()) {
                $locationIds[] = $locationSynchronizable->formatId($location2, 2);
                $extraLocationsIds[] = $locationSynchronizable->formatId($location2, 2);
            }

            /* @var $location3 Location3 */
            if ($location3 = $extraloc->getLocation3()) {
                $locationIds[] = $locationSynchronizable->formatId($location3, 3);
                $extraLocationsIds[] = $locationSynchronizable->formatId($location3, 3);
            }

            /* @var $location4 Location4 */
            if ($location4 = $extraloc->getLocation4()) {
                $locationIds[] = $locationSynchronizable->formatId($location4, 4);
                $extraLocationsIds[] = $locationSynchronizable->formatId($location4, 4);
            }

            /* @var $location5 Location5 */
            if ($location5 = $extraloc->getLocation5()) {
                $locationIds[] = $locationSynchronizable->formatId($location5, 5);
                $extraLocationsIds[] = $locationSynchronizable->formatId($location5, 5);
            }

        }

        $params['document'] = array_merge($params['document'], [
            'locationId'      => implode(' ', $locationIds),
            'mainLocationId'  => implode(' ', $mainLocationIds),
            'extraLocationId' => implode(' ', $extraLocationsIds),
        ]);
    }

    private function getClassListingBeforeDelete(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('DELETE FROM Listing_ExtraLocation WHERE listing_id = :id');
        $statement->bindValue('id', $params['that']->id);
        $statement->execute();
    }

    private function getListingCodeAfterSetupForm(&$params = null)
    {
        $_non_default_locations = '';
        $_default_locations_info = '';

        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $emMain = $this->container->get('doctrine')->getManager('main');
        $connectionMain = $emMain->getConnection();

        $level = isset($_GET['level']) ? $_GET['level'] : $params['listing']->level;

        $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalLocationsListingBundle:ListingLevelFieldLocations')->findOneBy([
            'level' => $level,
        ]);

        if (EDIR_DEFAULT_LOCATIONS) {

            system_retrieveLocationsInfo($_non_default_locations, $_default_locations_info);

            $last_default_location = $_default_locations_info[count($_default_locations_info) - 1]['type'];
            $last_default_location_id = $_default_locations_info[count($_default_locations_info) - 1]['id'];

            if ($_non_default_locations) {
                $objLocationLabel = 'Location'.$_non_default_locations[0];
                ${'Location'.$_non_default_locations[0]} = new $objLocationLabel;
                ${'Location'.$_non_default_locations[0]}->SetString('location_'.$last_default_location,
                    $last_default_location_id);
                ${'locations'.$_non_default_locations[0]} = ${'Location'.$_non_default_locations[0]}->retrieveLocationByLocation($last_default_location);
            }

        } else {

            $_non_default_locations = explode(',', EDIR_LOCATIONS);
            $objLocationLabel = 'Location'.$_non_default_locations[0];
            ${'Location'.$_non_default_locations[0]} = new $objLocationLabel;
            ${'locations'.$_non_default_locations[0]} = ${'Location'.$_non_default_locations[0]}->retrieveAllLocation();

        }

        if (!empty($resultLevel)) {
            $extralocations_allowed = $resultLevel->getField();
            $availableLocations = explode(',', EDIR_LOCATIONS);
            $objLocations = [];

            $this->container->get('modstore.storage.service')->store('levelMaxExtraLocations', $extralocations_allowed);
        }

        if ($extralocations_allowed) {
            if ($params['listing']->id) {
                $statement = $connection->prepare('SELECT * FROM Listing_ExtraLocation WHERE listing_id = :id ORDER BY id');
                $statement->bindValue('id', $params['listing']->id);
                $statement->execute();
                $extraLocs = $statement->fetchAll();

                if ($_non_default_locations) {
                    for ($i = 1; $i <= $extralocations_allowed; $i++) {
                        if ($i <= count($extraLocs) && $_non_default_locations) {
                            for ($j = 0, $jMax = count($availableLocations); $j < $jMax; $j++) {
                                if (in_array($availableLocations[$j], $_non_default_locations)) {
                                    if (!$last_default_location && $j == 0) {
                                        $statementLoc = $connectionMain->prepare("SELECT * FROM Location_{$availableLocations[$j]} ORDER BY name");
                                        $statementLoc->execute();
                                        ${'Locations'.$_non_default_locations[0]} = $statementLoc->fetchAll();
                                        $objLocations["locations{$availableLocations[$j]}_extra_loc_{$i}"] = ${'Locations'.$_non_default_locations[0]};
                                        unset($statementLoc);
                                    } else {
                                        $previousLocationIndex = $j - 1;
                                        $previousLocation = $extraLocs[$i - 1]["loc_location_{$availableLocations[$previousLocationIndex]}"];
                                        if ($previousLocation) {
                                            $objLocationLabel = "\Location".$availableLocations[$j];
                                            ${'Location'.$availableLocations[$j]} = new $objLocationLabel;
                                            ${'Location'.$availableLocations[$j]}->SetString('location_'.$availableLocations[$j - 1],
                                                $previousLocation);
                                            ${'Locations'.$_non_default_locations[0]} = ${'Location'.$availableLocations[$j]}->retrieveLocationByLocation($availableLocations[$j - 1]);
                                            $objLocations["locations{$availableLocations[$j]}_extra_loc_{$i}"] = ${'Locations'.$_non_default_locations[0]};
                                        }
                                    }
                                }
                            }
                        } else if ($_non_default_locations[0] == 1 || !$last_default_location) {
                            $statementLoc = $connectionMain->prepare("SELECT * FROM Location_{$_non_default_locations[0]} ORDER BY name");
                            $statementLoc->execute();
                            ${'locations'.$_non_default_locations[0].'_extra_loc_'.$i} = $statementLoc->fetchAll();
                            $objLocations["locations{$_non_default_locations[0]}_extra_loc_{$i}"] = ${'locations'.$_non_default_locations[0].'_extra_loc_'.$i};
                            unset($statementLoc);
                        } else {
                            $objLocationLabel = 'Location'.$_non_default_locations[0];
                            ${'Location'.$_non_default_locations[0]} = new $objLocationLabel;
                            ${'Location'.$_non_default_locations[0]}->SetString('location_'.$last_default_location,
                                $params['last_default_location_id']);
                            ${'locations'.$_non_default_locations[0].'_extra_loc_'.$i} = ${'Location'.$_non_default_locations[0]}->retrieveLocationByLocation($last_default_location);
                            $objLocations["locations{$_non_default_locations[0]}_extra_loc_{$i}"] = ${'locations'.$_non_default_locations[0].'_extra_loc_'.$i};
                        }
                    }
                }
            } else if ($_non_default_locations) {
                for ($i = 1; $i <= $extralocations_allowed; $i++) {
                    if ($_non_default_locations[0] == 1 || !$last_default_location) {
                        $statementLoc = $connectionMain->prepare("SELECT * FROM Location_{$_non_default_locations[0]} ORDER BY name");
                        $statementLoc->execute();
                        ${'locations'.$_non_default_locations[0].'_extra_loc_'.$i} = $statementLoc->fetchAll();
                        $objLocations["locations{$_non_default_locations[0]}_extra_loc_{$i}"] = ${'locations'.$_non_default_locations[0].'_extra_loc_'.$i};
                        unset($statementLoc);
                    } else {
                        $objLocationLabel = 'Location'.$_non_default_locations[0];
                        ${'Location'.$_non_default_locations[0]} = new $objLocationLabel;
                        ${'Location'.$_non_default_locations[0]}->SetString('location_'.$last_default_location,
                            $params['last_default_location_id']);
                        ${'locations'.$_non_default_locations[0].'_extra_loc_'.$i} = ${'Location'.$_non_default_locations[0]}->retrieveLocationByLocation($last_default_location);
                        $objLocations["locations{$_non_default_locations[0]}_extra_loc_{$i}"] = ${'locations'.$_non_default_locations[0].'_extra_loc_'.$i};
                    }
                }
            }

            if (!empty($_POST)) {
                $extraLocs = [];
                for ($i = 1; $i <= $extralocations_allowed; $i++) {
                    if ($_POST['address_extra_loc_'.$i] || $_POST['address2_extra_loc_'.$i] || $_POST['zip_code_extra_loc_'.$i] || (isset($location_fist_non_default) && $_POST['location_'.$location_fist_non_default.'_extra_loc_'.$i])) {

                        $extraLocs[$i - 1]['id'] = $i;
                        $extraLocs[$i - 1]['loc_address'] = $_POST["address_extra_loc_$i"];
                        $extraLocs[$i - 1]['loc_address2'] = $_POST["address2_extra_loc_$i"];
                        $extraLocs[$i - 1]['loc_zip_code'] = $_POST["zip_code_extra_loc_$i"];
                        $extraLocs[$i - 1]['loc_location_1'] = $_POST["location_1_extra_loc_$i"];
                        $extraLocs[$i - 1]['loc_location_2'] = $_POST["location_2_extra_loc_$i"];
                        $extraLocs[$i - 1]['loc_location_3'] = $_POST["location_3_extra_loc_$i"];
                        $extraLocs[$i - 1]['loc_location_4'] = $_POST["location_4_extra_loc_$i"];
                        $extraLocs[$i - 1]['loc_location_5'] = $_POST["location_5_extra_loc_$i"];
                        $extraLocs[$i - 1]['loc_latitude'] = $_POST["loc_latitude_$i"];
                        $extraLocs[$i - 1]['loc_longitude'] = $_POST["loc_longitude_$i"];
                        $extraLocs[$i - 1]['loc_map_zoom'] = $_POST["loc_map_zoom_$i"];
                        $extraLocs[$i - 1]['loc_map_tuning'] = $_POST["loc_map_tuning_$i"];

                        if ($_non_default_locations) {
                            for ($j = 0, $jMax = count($availableLocations); $j < $jMax; $j++) {
                                if (in_array($availableLocations[$j], $_non_default_locations)) {
                                    if (!$last_default_location && $j == 0) {
                                        $statementLoc = $connectionMain->prepare("SELECT * FROM Location_{$availableLocations[$j]} ORDER BY name");
                                        $statementLoc->execute();
                                        ${'Locations'.$_non_default_locations[0]} = $statementLoc->fetchAll();
                                        $objLocations["locations{$availableLocations[$j]}_extra_loc_{$i}"] = ${'Locations'.$_non_default_locations[0]};
                                        unset($statementLoc);
                                    } else {
                                        $previousLocationIndex = $j - 1;
                                        $previousLocation = $extraLocs[$i - 1]["loc_location_$availableLocations[$previousLocationIndex]"];
                                        if ($previousLocation) {
                                            $objLocationLabel = 'Location'.$availableLocations[$j];
                                            ${'Location'.$availableLocations[$j]} = new $objLocationLabel;
                                            ${'Location'.$availableLocations[$j]}->SetString('location_'.$availableLocations[$j - 1],
                                                $previousLocation);
                                            ${'Locations'.$_non_default_locations[0]} = ${'Location'.$availableLocations[$j]}->retrieveLocationByLocation($availableLocations[$j - 1]);
                                            $objLocations["locations{$availableLocations[$j]}_extra_loc_{$i}"] = ${'Locations'.$_non_default_locations[0]};
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($this->container->get('settings')->getDomainSetting('google_map_status') == 'on') {
            $totalExtraLocCoords = count($extraLocs);
            for ($t = 0; $t < $totalExtraLocCoords; $t++) {
                if ($extraLocs[$t]['loc_latitude'] && $extraLocs[$t]['loc_longitude'] && is_numeric($extraLocs[$t]['loc_latitude']) && is_numeric($extraLocs[$t]['loc_longitude'])) {
                    $extraLocs[$t]['hasValidCoord'] = true;
                }
            }

            $this->container->get('modstore.storage.service')->store('totalExtraLocCoords', $totalExtraLocCoords);
        }

        $this->container->get('modstore.storage.service')->store('extraLocs', $extraLocs);
        $this->container->get('modstore.storage.service')->store('objLocations', $objLocations);
    }

    private function getMultipleLocationsFulltextsearch(&$params = null)
    {
        for ($i = 1; $i <= $_POST['levelMaxExtraLocations']; $i++) {
            if ($_POST["address_extra_loc_$i"] || $_POST["location_1_extra_loc_$i"]) {
                if ($_POST["address_extra_loc_$i"]) {
                    $params['fulltextsearch_where'][] = string_substr($_POST["address_extra_loc_$i"], 0, 100);
                }

                if ($_POST["zip_code_extra_loc_$i"]) {
                    $params['fulltextsearch_where'][] = $_POST["zip_code_extra_loc_$i"];
                }

                $_locations = explode(',', EDIR_LOCATIONS);
                foreach ($_locations as $each_location) {
                    unset ($objLocation);
                    $objLocationLabel = 'Location'.$each_location;
                    $attributeLocation = 'location_'.$each_location;
                    $objLocation = new $objLocationLabel;
                    if ($_POST["location_{$each_location}_extra_loc_{$i}"]) {
                        $objLocation->SetString('id', $_POST["location_{$each_location}_extra_loc_{$i}"]);
                        $locationsInfo = $objLocation->retrieveLocationById();
                        if ($locationsInfo['id']) {
                            $params['fulltextsearch_where'][] = $locationsInfo['name'];
                            if ($locationsInfo['abbreviation']) {
                                $params['fulltextsearch_where'][] = $locationsInfo['abbreviation'];
                            }
                        }
                    }
                }
            }
        }
    }

    private function getListingLevelConstruct(&$params = null)
    {
        $params['that']->locationCount = 0;
    }

    private function getListingLevelFeatureBeforeReturn(&$params = null)
    {
        $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalLocationsListingBundle:ListingLevelFieldLocations')->findOneBy([
            'level' => $params['level']->getValue(),
        ]);

        $resultLevel and $params['listingLevel']->locationCount = $resultLevel->getField();
    }

    private function getListingAfterValidateItemDetail(&$params = null)
    {
        $twig = $this->container->get('twig');

        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalLocationsListingBundle:ListingLevelFieldLocations')->findOneBy([
            'level' => $params['item']->getLevel(),
        ]);

        if (!empty($resultLevel)) {

            $extralocs = [];
            $extLocationsIds = [];
            $extLocationsRows = [];
            $allowPins = [];

            $num_extralocations_allowed = $resultLevel->getField();

            $statement = $connection->prepare('SELECT * FROM Listing_ExtraLocation WHERE listing_id = :listing_id');
            $statement->bindValue('listing_id', $params['item']->getId());
            $statement->execute();

            $extraLocations = $statement->fetchAll();

            if ($extraLocations) {
                for ($arrCount = 0; $arrCount < $num_extralocations_allowed; $arrCount++) {

                    if (isset($extraLocations[$arrCount]['id'])) {

                        $allowPins[$arrCount] = [
                            $extraLocations[$arrCount]['id'],
                            $extraLocations[$arrCount]['loc_latitude'],
                            $extraLocations[$arrCount]['loc_longitude'],
                        ];

                        $extL = new ListingExtraLocation();
                        $extL->setId($extraLocations[$arrCount]['id']);
                        $extL->setListingId($extraLocations[$arrCount]['listing_id']);
                        $extL->setLocation1($extraLocations[$arrCount]['loc_location_1']);
                        $extL->setLocation2($extraLocations[$arrCount]['loc_location_2']);
                        $extL->setLocation3($extraLocations[$arrCount]['loc_location_3']);
                        $extL->setLocation4($extraLocations[$arrCount]['loc_location_4']);
                        $extL->setLocation5($extraLocations[$arrCount]['loc_location_5']);
                        $extL->setAddress($extraLocations[$arrCount]['loc_address']);
                        $extL->setAddress2($extraLocations[$arrCount]['loc_address2']);
                        $extL->setZipCode($extraLocations[$arrCount]['loc_zip_code']);
                        $extL->setLatitude($extraLocations[$arrCount]['loc_latitude']);
                        $extL->setLongitude($extraLocations[$arrCount]['loc_longitude']);
                        $extL->setMapzoom($extraLocations[$arrCount]['loc_map_zoom']);
                        $extL->setMaptuning($extraLocations[$arrCount]['loc_map_tuning']);

                        $extralocs[] = $extL;

                        $extLocationsRows[$arrCount] = [];
                        $extLocationsIds[$arrCount] = [];

                        $extLocations = $this->container->get('location.service')->getLocations($extL);
                        foreach (array_filter($extLocations) as $levelLocation => $extLocation) {
                            $key = substr($levelLocation, 0, 2).':'.$extLocation->getId();
                            $extLocationsIds[$arrCount][] = $key;
                            $extLocationsRows[$arrCount][$key] = $extLocation;
                        }
                    }
                }
            }

            $this->container->get('modstore.storage.service')->store('allowPins', $allowPins);

            $twig->addGlobal('extralocs', $extralocs);
            $twig->addGlobal('extLocationsIds', $extLocationsIds);
            $twig->addGlobal('extLocationsRows', $extLocationsRows);
        }
    }

    private function getListingSampleBeforeAddGlobalVars(&$params = null)
    {
        $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalLocationsListingBundle:ListingLevelFieldLocations')->findOneBy([
            'level' => $params['item']->getLevel(),
        ]);

        if(!empty($resultLevel)) {
            $extralocations_allowed = $resultLevel->getField();
        }

        if (!empty($extralocations_allowed)) {
            $extraLocs = [];
            for ($i = 0; $i < $extralocations_allowed; $i++) {
                $newLoc = new ListingExtraLocation();
                $newLoc->setAddress($this->container->get('translator')->trans('Address').' '.($i + 2))
                    ->setAddress2($this->container->get('translator')->trans('Zip Code'))
                    ->setLatitude($this->container->get('translator')->trans('-22.3344628'))
                    ->setLongitude($this->container->get('translator')->trans('-49.068844'));

                $extraLocs[] = $newLoc;
            }

            $this->container->get('twig')->addGlobal('extralocs', $extraLocs);
        }
    }

    private function getSearchEngineBeforeSetupMapClusters(&$params = null)
    {
        if ($params['results']->getTotalHits() > 0 && $this->container->get('settings')->getDomainSetting('google_map_status') == 'on') {

            foreach ($params['results']->getResults() as $result) {

                $extraLocations = $this->container->get('doctrine')->getRepository('AdditionalLocationsListingBundle:ListingExtraLocation')->findBy(['listingId' => $result->getid()]);

                foreach ($extraLocations as $location) {

                    if (!empty($location->getLatitude()) && !empty($location->getLongitude())) {
                        $marker = new Marker(new Coordinate((float)$location->getLatitude(), (float)$location->getLongitude(), true));
                        $marker->setOption('clickable', true);
                        $marker->setOption('flat', true);
                        $marker->setOption('itemElement',
                            [
                                'item'               => $location->getId(),
                                'itemtype'           => $result->getType(),
                                'additionalLocation' => true,
                            ]);

                        $marker->setIcon(new Icon($this->container->get('request')->getSchemeAndHttpHost().'/'.$params['iconPath']));

                        $params['cluster']->addMarker($marker);
                        $params['bound']->addExtendable($marker);
                    }
                }
            }
        }
    }

    private function getMapSummaryBeforeSetData(&$params = null)
    {
        if (!empty($params['data']['additionalLocation'])) {
            $additionalLocation = $this->container->get('doctrine')->getRepository('AdditionalLocationsListingBundle:ListingExtraLocation')->find($params['itemId']);
            if(!empty($additionalLocation)) {
                $params['itemId'] = $additionalLocation->getListingId();
            }
        }
    }

    // Todo: methods to hooks revision names

    private function getCodeMLSnippet1(&$params = null)
    {
        $params['_locations'] = explode(',', EDIR_LOCATIONS);
        $params['_defaultLocations'] = explode(',', EDIR_DEFAULT_LOCATIONS);
        $params['_nonDefaultLocations'] = array_diff_assoc($params['_locations'], $params['_defaultLocations']);
    }

    private function getCodeMLSnippet2(&$params = null)
    {
        $levelObj = new ListingLevel(true);

        if (!$params['level']) {
            $params['level'] = $levelObj->getDefaultLevel();
        }

        $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalLocationsListingBundle:ListingLevelFieldLocations')->findOneBy([
            'level' => $params['level'],
        ]);

        if(!empty($resultLevel)) {
            $extralocations_allowed = $resultLevel->getField();
        }
        $locationsToSave = [];

        for ($i = 1; $i <= $extralocations_allowed; $i++) {

            if ($_POST['new_location2_field_extra_loc_'.$i] != '' || $_POST['new_location3_field_extra_loc_'.$i] != '' || $_POST['new_location4_field_extra_loc_'.$i] != '' || $_POST['new_location5_field_extra_loc_'.$i] != '') {

                foreach ($params['_defaultLocations'] as $defLoc) {
                    $locationsToSave[$defLoc] = $_POST['location_'.$defLoc.'_extra_loc_'.$i];
                }

                $stop_insert_location = false;

                foreach ($params['_nonDefaultLocations'] as $nonDefLoc) {
                    if (trim($_POST['location_'.$nonDefLoc.'_extra_loc_'.$i]) != '') {
                        $locationsToSave[$nonDefLoc] = $_POST['location_'.$nonDefLoc.'_extra_loc_'.$i];
                    } else if (!$stop_insert_location) {
                        if (!$_POST['new_location'.$nonDefLoc.'_field'.'_extra_loc_'.$i]) {
                            $stop_insert_location = true;
                        } else {
                            $objNewLocationLabel = "\Location".$nonDefLoc;
                            $objNewLocation = new $objNewLocationLabel;

                            foreach ($locationsToSave as $level => $value) {
                                $objNewLocation->setString('location_'.$level, $value);
                            }

                            $objNewLocation->setString('name',
                                $_POST['new_location'.$nonDefLoc.'_field'.'_extra_loc_'.$i]);
                            $objNewLocation->setString('friendly_url',
                                $_POST['new_location'.$nonDefLoc.'_friendly'.'_extra_loc_'.$i]);
                            $objNewLocation->setString('default', 'n');
                            $objNewLocation->setString('featured', 'n');

                            $newLocationFlag = $objNewLocation->retrievedIfRepeated($params['_locations']);

                            if ($newLocationFlag) {
                                $objNewLocation->setNumber('id', $newLocationFlag);
                            } else {
                                $objNewLocation->Save();
                            }
                            $_POST['location_'.$nonDefLoc.'_extra_loc_'.$i] = $objNewLocation->getNumber('id');
                            ${'location_'.$nonDefLoc."_extra_loc_$i"} = $objNewLocation->getNumber('id');
                            $locationsToSave[$nonDefLoc] = $_POST['location_'.$nonDefLoc.'_extra_loc_'.$i];
                        }
                    }
                }
            }
        }
    }

    private function getMlForm(&$params = null)
    {
        $translator = $this->container->get('translator');

        $resultLevel = $this->container->get('doctrine')->getRepository('AdditionalLocationsListingBundle:ListingLevelFieldLocations')->findOneBy([
            'level' => $params['level'],
        ]);

        $extraLocs = $this->container->get('modstore.storage.service')->retrieve('extraLocs');
        $objLocations = $this->container->get('modstore.storage.service')->retrieve('objLocations');
        $_default_locations_info = $params['_default_locations_info'];
        $_non_default_locations = $params['_non_default_locations'];
        $_location_father_level = $params['_location_father_level'];
        $_location_child_level = $params['_location_child_level'];
        $_location_level = $params['_location_level'];
        $sitemgrSearch = $params['sitemgrSearch'];
        $formLoadMap = $params['formLoadMap'];
        $loadMap = $params['loadMap'];
        $members = $params['members'];

        if (!empty($objLocations)) {
            foreach ($objLocations as $key => $array) {
                $params[$key] = $array;
            }
        }

        if (!empty($resultLevel)) {
            $extralocations_allowed = $resultLevel->getField();
        }

        if ($extralocations_allowed > 0)
        { ?>
    <div class="panel panel-form">
        <div class="panel-heading"><?= $translator->trans('Additional Locations') ?></div>
            <?php
            for ($j = 1; $j <= $extralocations_allowed; $j++)
            { ?>
            <input type="hidden" name="extra_loc_<?= $j; ?>_id" value="<?= $extraLocs[$j - 1]['id'] ?>"/>
            <div id="box_<?= $j; ?>"
                 class="panel-body extraLocation"
                 <?= ($extraLocs[$j - 1]['id'] || $j == 1 ? '' : 'style="display: none"') ?>>
                <button type="button" class="btn btn-sm btn-danger button button-sm is-warning delete pull-right" onclick="removeExtraLocBlock(this)">
                <?php
                    if($members)
                    { ?>
                    <i class="fa fa-trash"></i>
                <?php
                    }
                    else
                    { ?>
                    <i class="icon-ion-ios7-trash-outline"></i>
                <?php
                    } ?>
                </button>
                <p><?= $translator->trans('Additional Locations') ?> <?= $j; ?></p>
                <div class="form-group row custom-content-row">
                    <div class="col-xs-12">
                <?php
                    if (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on' && $params['template_address_field'] !== false)
                    { ?>
                        <label for="address_extra_loc_<?= $j; ?>"><?= $params['template_address_field'][0]['label'] ?></label>
                <?php
                    }
                    else
                    { ?>
                        <label for="address_extra_loc_<?= $j; ?>"><?= $translator->trans('Address') ?></label>
                <?php
                    }
                ?>
                        <input type="text"
                               name="address_extra_loc_<?= $j; ?>"
                               id="address_extra_loc_<?= $j; ?>"
                               value="<?= $extraLocs[$j - 1]['loc_address'] ?>"
                               maxlength="100"
                               class="form-control <?= ($params['highlight'] == 'description' && !$extraLocs[$j - 1]['loc_address'] ? 'highlight' : '') ?>"
                               placeholder="<?= (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on' && $params['template_address_field'] !== false && $params['template_address_field'][0]['instructions'] ? $params['template_address_field'][0]['instructions'] : $translator->trans('Street Address, P.O. box')) ?>" <?= ($params['loadMap'] ? "onblur=\"loadMap(document.listing,undefined,$j);\"" : '') ?>/>
                    </div>
                </div>
                <div class="form-group row custom-content-row">
                    <div class="col-sm-6">
                <?php
                    if (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on' && $params['template_address2_field'] !== false)
                    { ?>
                        <label for="address2_extra_loc_<?= $j; ?>"><?= $params['template_address2_field'][0]['label'] ?></label>
                <?php
                    }
                    else
                    { ?>
                        <label for="address2_extra_loc_<?= $j; ?>"><?= $translator->trans('Address 2') ?></label>
                <?php
                    } ?>
                        <input type="text"
                               name="address2_extra_loc_<?= $j; ?>"
                               id="address2_extra_loc_<?= $j; ?>"
                               value="<?= $extraLocs[$j - 1]['loc_address2'] ?>"
                               maxlength="100"
                               class="form-control <?= ($params['highlight'] == 'description' && !$extraLocs[$j - 1]['loc_address2'] ? 'highlight' : '') ?>"
                               placeholder="<?= (LISTINGTEMPLATE_FEATURE == 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE == 'on' && $params['template_address2_field'] !== false && $params['template_address2_field'][0]['instructions'] ? $params['template_address2_field'][0]['instructions'] : $translator->trans('Apartment, suite, unit, building, floor, etc.')) ?>"/>
                    </div>
                    <div class="col-sm-6 custom-content-row">
                        <label for="zip_code_extra_loc_<?= $j; ?>"><?= $translator->trans('Zip Code') ?></label>
                        <input type="text"
                               name="zip_code_extra_loc_<?= $j; ?>"
                               id="zip_code_extra_loc_<?= $j; ?>"
                               value="<?= $extraLocs[$j - 1]['loc_zip_code'] ?>"
                               maxlength="20"
                               class="form-control <?= ($params['highlight'] == 'description' && !$extraLocs[$j - 1]['loc_zip_code'] ? 'highlight' : '') ?>" <?= ($params['loadMap'] ? "onblur=\"loadMap(document.listing,undefined,$j);\"" : '') ?>/>
                    </div>
                </div>
                    <?php
                    $use_sitemgr_function = false;
                    if (string_strpos($_SERVER['REQUEST_URI'],SITEMGR_ALIAS) || string_strpos($_SERVER['REQUEST_URI'],MEMBERS_ALIAS))
                    {
                        $use_sitemgr_function = true;
                    }

                    if (string_strpos($_SERVER['REQUEST_URI'], SITEMGR_ALIAS) !== false)
                    {
                    ?>
                <div id="formsLocation" class="form-location form-group row">
                        <?php
                        if ($_default_locations_info)
                        {
                            foreach ($_default_locations_info as $_default_location_info)
                            {
                        ?>
                    <div class="col-xs-6">
                        <label for="location_1"><?= system_showText(constant('LANG_LABEL_'.constant('LOCATION'.$_default_location_info['type'].'_SYSTEM'))) ?></label>
                        <input type="text" class="form-control" disabled="" value="<?= $_default_location_info['name'] ?>"/>
                        <input type="hidden"
                               name="location_<?= $_default_location_info['type'] ?>_extra_loc_<?= $j; ?>"
                               value="<?= $_default_location_info['id'] ?>"/>
                    </div>
                        <?php
                            }
                        }
                        if ($_non_default_locations)
                        {
                        ?>
                    <input type="hidden" name="location_fist_non_default" value="<?= $_non_default_locations[0] ?>"/>
                        <?php
                            $firstNonDefault = 0;
                            foreach ($_non_default_locations as $_location_level)
                            {
                                $objLocationLabel = 'Location'.$_location_level;
                                ${'extraLocation'.$_location_level} = new $objLocationLabel;
                                if ($_default_locations_info)
                                {
                                    $last_default_location = $_default_locations_info[count($_default_locations_info) - 1]['type'];
                                    $last_default_location_id = $_default_locations_info[count($_default_locations_info) - 1]['id'];
                                    ${'extraLocation'.$_location_level}->SetString('location_'.$last_default_location, $last_default_location_id);
                                    if ($firstNonDefault == 0 && $_POST['new_location'.$_location_level.'_field_extra_loc_'.$j] == '')
                                    {
                                        ${'extra_locations'.$_location_level} = ${'extraLocation'.$_location_level}->retrieveLocationByLocation($last_default_location);
                                        $firstNonDefault = 1;
                                    }
                                    else
                                    {
                                        unset (${'extraLocation'.$_location_level});
                                    }
                                }
                                else if ($firstNonDefault == 0 && $_POST['new_location'.$_location_level.'_field_extra_loc_'.$j] == '')
                                {
                                    ${'extra_locations'.$_location_level} = ${'extraLocation'.$_location_level}->retrieveAllLocation();
                                    $firstNonDefault = 1;
                                }
                                else
                                {
                                    unset (${'extraLocation'.$_location_level});
                                }

                                system_retrieveLocationRelationship($_non_default_locations, $_location_level,$_location_father_level, $_location_child_level);
                                $location_name = system_showText(constant('LANG_LABEL_'.constant('LOCATION'.$_location_level.'_SYSTEM')));
                        ?>
                    <div class="col-xs-6"
                         id="div_location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"
                         <?= (${'extraLocation'.$_location_level} || $params["locations{$_location_level}_extra_loc_{$j}"]) && $_POST['new_location'.$_location_level.'_field_extra_loc_'.$j] == '' ? '' : 'style="display:none;"' ?>>
                        <label for="location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"><?= $location_name ?></label>
                        <div class="field"
                              id="div_img_loading_<?= $_location_level ?>_extra_loc_<?= $j ?>"
                              style="display:none;">
                            <img src="<?= DEFAULT_URL ?>/<?= SITEMGR_ALIAS ?>/assets/img/preloader-32.gif" alt="<?= $translator->trans('Wait, Loading...') ?>"/>
                        </div>
                        <div id="div_select_<?= $_location_level ?>_extra_loc_<?= $j; ?>" class="field locationSelect">
                                <?php
                                if ($use_sitemgr_function)
                                { ?>
                            <select
                                <?= ((${'extraLocation'.$_location_level} || $params["locations{$_location_level}_extra_loc_{$j}"]) ? '' : 'style="display:none"') ?>
                                class="select <?= ($highlight == 'description' && !$extraLocs[$j - 1]['loc_location_'.$_location_level] ? 'highlight' : '') ?>"
                                name="<?= ($sitemgrSearch ? 'search_' : '') ?>location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"
                                id="location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"
                                <?php
                                    if ($_location_child_level)
                                    { ?>
                                onchange="loadLocationSitemgrMembers('<?= DEFAULT_URL ?>', '<?= EDIR_LOCATIONS ?>', <?= $_location_level ?>, <?= $_location_child_level ?>, this.value, <?= $j; ?>);<?php if ($loadMap) { ?>loadMap(<?=$formLoadMap?>, false, <?=$j;?>);<?php } ?>"
                                    <?php
                                    }
                                    elseif ($loadMap)
                                    { ?>
                                onchange="loadMap(<?= $formLoadMap ?>, false, <?= $j; ?>);"
                                    <?php
                                    } ?>>
                                <?php
                                }
                                else
                                { ?>
                            <select
                                <?= ((${'extraLocation'.$_location_level} || $params["locations{$_location_level}_extra_loc_{$j}"]) ? '' : 'style="display:none"') ?>
                                class="select"
                                name="<?= ($sitemgrSearch ? 'search_' : '') ?>location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"
                                id="location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"
                                    <?php
                                    if ($_location_child_level)
                                    { ?>
                                onchange="loadLocation('<?= DEFAULT_URL ?>', '<?= EDIR_LOCATIONS ?>', <?= $_location_level ?>, <?= $_location_child_level ?>, this.value);"
                                    <?php
                                    } ?>>
                                <?php
                                } ?>
                                <option
                                    id="l_location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"
                                    value=""><?= system_showText(constant('LANG_LABEL_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?>
                                </option>
                                <?php
                                if (is_array($params["locations{$_location_level}_extra_loc_{$j}"]))
                                {
                                    ${'extra_locations'.$_location_level} = $params["locations{$_location_level}_extra_loc_{$j}"];
                                }
                                if (is_array(${'extra_locations'.$_location_level}))
                                {
                                    foreach (${'extra_locations'.$_location_level} as $each_location)
                                    {
                                        $selected = ($extraLocs[$j - 1]['loc_location_'.$_location_level] == $each_location['id']) ? 'selected' : '';
                                ?>
                                <option
                                    <?= $selected ?>
                                    value="<?= $each_location['id'] ?>"><?= $each_location['name'] ?>
                                </option>
                                <?php
                                        unset($selected);
                                    }
                                }
                                ?>
                            </select>
                            <div
                                class="field"
                                id="box_no_location_found_<?= $_location_level ?>_extra_loc_<?= $j ?>"
                                <?= (!${'extraLocation'.$_location_level} && $params["location_{$_location_father_level}"] && !$_POST['new_location'.$_location_level."_field_extra_loc_$j"] ? '' : 'style="display:none;"') ?>>
                                <?= system_showText(constant('LANG_LABEL_NO_LOCATIONS_FOUND')) ?>
                            </div>
                        </div>
                        <div class="field">
                            <div id="div_new_location<?= $_location_level ?>_link_extra_loc_<?= $j; ?>" <?= ($_POST['new_location'.$_location_level."_field_extra_loc_$j"] == '' ? '' : 'style="display:none;"') ?> >
                                <?php
                                if ($_location_level != 1 && !string_strpos($_SERVER['PHP_SELF'],'index.php'))
                                { ?>
                                <a class="small"
                                   href="javascript:"
                                   onclick="showNewLocationField('<?= $_location_level ?>', '<?= EDIR_LOCATIONS ?>', true,false,<?= $j; ?>);"
                                   style=" cursor: pointer">+ <?= system_showText(constant('LANG_LABEL_ADD_A_NEW_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></a>
                                <?php
                                }
                                else
                                {
                                    echo '&nbsp;';
                                } ?>
                            </div>
                        </div>
                    </div>
                                <?php
                                if ($_location_level != 1 && !string_strpos($_SERVER['PHP_SELF'],'index.php'))
                                { ?>
                    <div class="col-xs-6" id="div_new_location<?= $_location_level ?>_field_extra_loc_<?= $j; ?>" <?= ($_POST['new_location'.$_location_level."_field_extra_loc_$j"] != '' ? '' : ($_POST['new_location'.$_location_father_level."_field_extra_loc_$j"] != '' ? '' : 'style="display:none;"')) ?>>
                        <div>
                            <label for="newlocation"><?= system_showText(constant('LANG_LABEL_ADD_A_NEW_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></label>
                        </div>
                        <div class="field">
                            <input type="text"
                                   class="form-control"
                                   name="new_location<?= $_location_level ?>_field_extra_loc_<?= $j; ?>"
                                   id="new_location<?= $_location_level ?>_field_extra_loc_<?= $j; ?>"
                                   value="<?= $_POST['new_location'.$_location_level."_field_extra_loc_$j"] ?>"
                                   <?php if ($_location_child_level) { ?> onfocus="showNewLocationField('<?= $_location_child_level ?>', '<?= EDIR_LOCATIONS ?>', false,false,<?= $j; ?>);" <?php } ?>
                                   onblur="easyFriendlyUrl(this.value, 'new_location<?= $_location_level ?>_friendly', '<?= FRIENDLYURL_VALIDCHARS ?>', '<?= FRIENDLYURL_SEPARATOR ?>'); <?php if ($loadMap) { ?> loadMap(<?= $formLoadMap ?>, false, <?= $j; ?>);" <?php } elseif ($loadMap) { ?>" onchange="loadMap(<?= $formLoadMap ?>, false, <?= $j; ?>);"<?php } else { ?>"<?php } ?> />
                            <input type="hidden"
                                   name="new_location<?= $_location_level ?>_friendly_extra_loc_<?= $j; ?>"
                                   id="new_location<?= $_location_level ?>_friendly_extra_loc_<?= $j; ?>"
                                   value="<?= $_POST['new_location'.$_location_level."_friendly_extra_loc_$j"] ?>"/>
                        </div>
                        <div class="field" colspan="2">
                            <div id="div_new_location<?= $_location_level ?>_back_extra_loc_<?= $j; ?>" <?= ($_POST['new_location'.$_location_father_level."_field_extra_loc_$j"] == '' ? '' : 'style="display:none;"') ?>>
                                <a class="small"
                                   href="javascript:"
                                   onclick="hideNewLocationField('<?= $_location_level ?>', '<?= EDIR_LOCATIONS ?>',<?= $j; ?>);"
                                   style=" cursor: pointer">- <?= system_showText(constant('LANG_LABEL_CHOOSE_AN_EXISTING_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></a>
                            </div>
                        </div>
                    </div>
                        <?php
                                }
                                unset (${'extra_locations'.$_location_level});
                                unset (${'extraLocation'.$_location_level});
                            }
                            unset ($_location_father_level);
                            unset ($_location_child_level);
                            unset ($_location_level);
                        }
                        ?>
                </div>
                    <?php
                    }
                    else
                    {
                    ?>
                <div id="formsLocation" class="form-location form-group row custom-content-row">
                    <?php
                        if ($_default_locations_info)
                        {
                            foreach ($_default_locations_info as $_default_location_info)
                            {
                                if ($_default_location_info['show'] == 'y')
                                {
                    ?>
                    <div class="col-xs-6">
                        <label><?= system_showText(constant('LANG_LABEL_'.constant('LOCATION'.$_default_location_info['type'].'_SYSTEM'))) ?></label>
                        <input type="text" class="form-control" disabled="" value="<?= $_default_location_info['name'] ?>"/>
                    </div>
                    <?php
                                }?>
                    <input type="hidden"
                           name="location_<?= $_default_location_info['type'] ?>_extra_loc_<?= $j; ?>"
                           value="<?= $_default_location_info['id'] ?>"/>
                        <?php
                            }
                        }
                        if ($_non_default_locations)
                        {
                        ?>
                    <input type="hidden"
                           name="location_fist_non_default"
                           value="<?= $_non_default_locations[0] ?>"/>
                            <?php
                            $firstNonDefault = 0;
                            foreach ($_non_default_locations as $_location_level)
                            {
                                $objLocationLabel = 'Location'.$_location_level;
                                ${'extraLocation'.$_location_level} = new $objLocationLabel;
                                if ($_default_locations_info)
                                {
                                    $last_default_location = $_default_locations_info[count($_default_locations_info) - 1]['type'];
                                    $last_default_location_id = $_default_locations_info[count($_default_locations_info) - 1]['id'];
                                    ${'extraLocation'.$_location_level}->SetString('location_'.$last_default_location, $last_default_location_id);
                                    if ($firstNonDefault == 0)
                                    {
                                        ${'extra_locations'.$_location_level} = ${'extraLocation'.$_location_level}->retrieveLocationByLocation($last_default_location);
                                        $firstNonDefault = 1;
                                    }
                                    else
                                    {
                                        unset (${'extraLocation'.$_location_level});
                                    }
                                }
                                else if ($firstNonDefault == 0)
                                {
                                    ${'extra_locations'.$_location_level} = ${'extraLocation'.$_location_level}->retrieveAllLocation();
                                    $firstNonDefault = 1;
                                }
                                else
                                {
                                    unset (${'extraLocation'.$_location_level});
                                }
                                system_retrieveLocationRelationship($_non_default_locations, $_location_level,$_location_father_level, $_location_child_level);
                                $location_name = system_showText(constant('LANG_LABEL_'.constant('LOCATION'.$_location_level.'_SYSTEM')));
                            ?>
                    <div class="col-sm-6"
                         id="div_location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"
                         <?= (${'extraLocation'.$_location_level} || $params["locations{$_location_level}_extra_loc_{$j}"]) && $_POST['new_location'.$_location_level."_field_extra_loc_$j"] == '' ? '' : 'style="display:none;"' ?>>
                        <label for="location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"><?= $location_name ?>:</label>
                        <div class="field"
                             id="div_img_loading_<?= $_location_level ?>_extra_loc_<?= $j; ?>"
                             style="display:none;">
                            <i class="fa fa-spinner fa-spin"></i>
                        </div>
                        <div id="div_select_<?= $_location_level ?>_extra_loc_<?= $j; ?>" class="field locationSelect">
                            <?php
                                if ($use_sitemgr_function)
                                { ?>
                            <select <?= ((${'extraLocation'.$_location_level} || $params["locations{$_location_level}_extra_loc_{$j}"]) ? '' : 'style="display:none"') ?>
                                class="select form-control cutom-select-appearence <?= ($highlight == 'description' && !$extraLocs[$j - 1]['location_'.$_location_level] ? 'highlight' : '') ?>"
                                name="<?= ($sitemgrSearch ? 'search_' : '') ?>location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"
                                id="location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"
                                    <?php if ($_location_child_level) { ?> onchange="loadLocationSitemgrMembers('<?= DEFAULT_URL ?>', '<?= EDIR_LOCATIONS ?>', <?= $_location_level ?>, <?= $_location_child_level ?>, this.value, <?= $j; ?>); <?php if ($loadMap) { ?> loadMap(<?=$formLoadMap?>, false, <?=$j;?>); <?php } ?>" <?php } elseif ($loadMap) { ?>" onchange="loadMap(<?= $formLoadMap ?>, false, <?= $j; ?>);" <?php } ?>>
                                <?php
                                }
                                else
                                { ?>
                            <select <?= ((${'extraLocation'.$_location_level} || $params["locations{$_location_level}_extra_loc_{$j}"]) ? '' : 'style="display:none"') ?>
                                class="select form-control cutom-select-appearence"
                                name="<?= ($sitemgrSearch ? 'search_' : '') ?>location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"
                                id="location_<?= $_location_level ?>_extra_loc_<?= $j; ?>"
                                    <?php if ($_location_child_level) { ?> onchange="loadLocation('<?= DEFAULT_URL ?>', '<?= EDIR_LOCATIONS ?>', <?= $_location_level ?>, <?= $_location_child_level ?>, this.value);" <?php } ?>>
                                <?php
                                } ?>
                                <option id="l_location_<?= $_location_level ?>_extra_loc_<?= $j; ?>" value=""><?= system_showText(constant('LANG_LABEL_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></option>
                                <?php
                                if (is_array($params["locations{$_location_level}_extra_loc_{$j}"]))
                                {
                                   ${'extra_locations'.$_location_level} = $params["locations{$_location_level}_extra_loc_{$j}"];
                                }
                                if (is_array(${'extra_locations'.$_location_level}))
                                {
                                    foreach (${'extra_locations'.$_location_level} as $each_location)
                                    {
                                        $selected = ($extraLocs[$j - 1]['loc_location_'.$_location_level] == $each_location['id']) ? 'selected' : '';
                                ?>
                                <option <?= $selected ?> value="<?= $each_location['id'] ?>"><?= $each_location['name'] ?></option>
                                <?php
                                        unset($selected);
                                    }
                                }
                                ?>
                            </select>
                            <div class="field"
                                 id="box_no_location_found_<?= $_location_level ?>_extra_loc_<?= $j ?>"
                                 <?= (!${'extraLocation'.$_location_level} && $params["location_{$_location_father_level}"] && !$_POST['new_location'.$_location_level."_field_extra_loc_$j"] ? '' : 'style="display:none;"') ?>>
                                <?= system_showText(constant('LANG_LABEL_NO_LOCATIONS_FOUND')) ?>.
                            </div>
                        </div>
                        <div class="field">
                            <div class=""
                                 id="div_new_location<?= $_location_level ?>_link_extra_loc_<?= $j; ?>"
                                 <?= ($_POST['new_location'.$_location_level."_field_extra_loc_$j"] == '' ? '' : 'style="display:none;"') ?>>
                                <?php
                                if ($_location_level != 1 && !string_strpos($_SERVER['PHP_SELF'],'search.php'))
                                {?>
                                <a href="javascript:"
                                   onclick="showNewLocationField('<?= $_location_level ?>', '<?= EDIR_LOCATIONS ?>', true,false,<?= $j; ?>);"
                                   style=" cursor: pointer">+ <?= system_showText(constant('LANG_LABEL_ADD_A_NEW_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></a>
                                <?php
                                }
                                else
                                {
                                    echo '&nbsp;';
                                }?>
                            </div>
                        </div>
                    </div>
                        <?php
                                if ($_location_level != 1 && !string_strpos($_SERVER['PHP_SELF'],'search.php'))
                                { ?>
                    <div class="col-sm-6"
                         id="div_new_location<?= $_location_level ?>_field_extra_loc_<?= $j; ?>"
                             <?= ($_POST['new_location'.$_location_level."_field_extra_loc_$j"] != '' ? '' : ($_POST['new_location'.$_location_father_level."_field_extra_loc_$j"] != '' ? '' : 'style="display:none;"')) ?>>
                        <label for="newlocation"><?= system_showText(constant('LANG_LABEL_ADD_A_NEW_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?>:</label>
                        <div class="field">
                            <input type="text"
                                   class="form-control"
                                   name="new_location<?= $_location_level ?>_field_extra_loc_<?= $j; ?>"
                                   id="new_location<?= $_location_level ?>_field_extra_loc_<?= $j; ?>"
                                   value="<?= $_POST['new_location'.$_location_level."_field_extra_loc_$j"] ?>"
                                   <?php if ($_location_child_level) { ?> onfocus="showNewLocationField('<?= $_location_child_level ?>', '<?= EDIR_LOCATIONS ?>', false,false,<?= $j; ?>);" <?php } ?>
                                   onblur="easyFriendlyUrl(this.value, 'new_location<?= $_location_level ?>_friendly', '<?= FRIENDLYURL_VALIDCHARS ?>', '<?= FRIENDLYURL_SEPARATOR ?>'); <?php if ($loadMap) { ?> loadMap(<?= $formLoadMap ?>, false, <?= $j; ?>); " <?php } elseif ($loadMap) { ?>" onchange="loadMap(<?= $formLoadMap ?>, false, <?= $j; ?>);" <?php } else { ?>"<?php } ?> />
                            <input type="hidden"
                                   name="new_location<?= $_location_level ?>_friendly_extra_loc_<?= $j; ?>"
                                   id="new_location<?= $_location_level ?>_friendly_extra_loc_<?= $j; ?>"
                                   value="<?= $_POST['new_location'.$_location_level."_friendly_extra_loc_$j"] ?>"/>
                        </div>
                        <div class="field" colspan="2">
                            <div id="div_new_location<?= $_location_level ?>_back_extra_loc_<?= $j; ?>" <?= ($_POST['new_location'.$_location_father_level."_field_extra_loc_$j"] == '' ? '' : 'style="display:none;"') ?>>
                                <a href="javascript:"
                                   onclick="hideNewLocationField('<?= $_location_level ?>', '<?= EDIR_LOCATIONS ?>',<?= $j; ?>);"
                                   style=" cursor: pointer">- <?= system_showText(constant('LANG_LABEL_CHOOSE_AN_EXISTING_'.constant('LOCATION'.$_location_level.'_SYSTEM'))) ?></a>
                            </div>
                        </div>
                    </div>
                        <?php
                                }
                                unset (${'extra_locations'.$_location_level});
                                unset (${'extraLocation'.$_location_level});
                            }
                            unset ($_location_father_level);
                            unset ($_location_child_level);
                            unset ($_location_level);
                        }
                        ?>
                </div>
                    <?php
                    }
                    if ($params['loadMap'])
                    {
                        if(!$members)
                        { ?>
                <div class="form-group row custom-content-row">
                    <div class="col-xs-12 map-extralocation" id="tableMapTuning<?= $j ?>" <?= ($extraLocs[$j - 1]['hasValidCoord'] ? '' : 'style="display: none"') ?>>
                        <div id="map<?= $j ?>" style="height: 200px"></div>
                        <input type="hidden"
                               name="latitude_longitude_<?= $j; ?>"
                               id="myLatitudeLongitude_<?= $j; ?>"
                               value="<?= $params["latitude_longitude_{$j}"] ?>"/>
                        <input type="hidden"
                               name="loc_map_zoom_<?= $j; ?>"
                               id="loc_map_zoom_<?= $j; ?>"
                               value="<?= $extraLocs[$j - 1]['loc_map_zoom'] ?>"/>
                        <input type="hidden"
                               name="loc_map_tuning_<?= $j; ?>"
                               id="loc_map_tuning_<?= $j; ?>"
                               value="<?= $extraLocs[$j - 1]['loc_map_tuning'] ?>"/>
                    </div>
                </div>
                    <?php
                        }
                        else
                        { ?>
                <div class="map-extralocation" id="tableMapTuning<?= $j ?>" <?= ($extraLocs[$j - 1]['hasValidCoord'] ? '' : 'style="display: none"') ?>>
                    <div id="map<?= $j ?>" style="height: 200px"></div>
                    <input type="hidden"
                           name="latitude_longitude_<?= $j; ?>"
                           id="myLatitudeLongitude_<?= $j; ?>"
                           value="<?= $params["latitude_longitude_{$j}"] ?>"/>
                    <input type="hidden"
                           name="loc_map_zoom_<?= $j; ?>"
                           id="loc_map_zoom_<?= $j; ?>"
                           value="<?= $extraLocs[$j - 1]['loc_map_zoom'] ?>"/>
                    <input type="hidden"
                           name="loc_map_tuning_<?= $j; ?>"
                           id="loc_map_tuning_<?= $j; ?>"
                           value="<?= $extraLocs[$j - 1]['loc_map_tuning'] ?>"/>
                </div>
                <br/>
                        <?php
                        }
                    } ?>
                <input type="hidden"
                       name="loc_latitude_<?= $j; ?>"
                       id="loc_latitude_<?= $j; ?>"
                       value="<?= $extraLocs[$j - 1]['loc_latitude'] ?>"/>
                <input type="hidden"
                       name="loc_longitude_<?= $j; ?>"
                       id="loc_longitude_<?= $j; ?>"
                       value="<?= $extraLocs[$j - 1]['loc_longitude'] ?>"/>
                <input type="hidden"
                       name="levelMaxExtraLocations"
                       id="levelMaxExtraLocations"
                       value="<?= $extralocations_allowed ?>"/>
            </div>
                <?php
            } ?>
                <div id="loadExtraLocBlocks">
                    <div class="btn btn-primary button button-md is-primary" <?= $members ?: 'style="width:100%"';?> onclick="loadExtraLocBlock()"><?= $translator->trans('Add Additional Location') ?></div>
                </div>
                <br>
            </div>
        <?php
        }
    }

    private function customLocationJs(&$params = null)
    {
        echo "<script src=\"".DEFAULT_URL."/bundles/additionallocationslisting/js/additional_location_plugin.js\"></script>";
    }

    private function listingDetailMl(&$params = null)
    {
        echo $this->container->get('templating')->render('AdditionalLocationsListingBundle::multiplelocations-listingdetail.html.twig');
    }

    private function loadMapFunct(&$params = null)
    {
        echo $this->container->get('templating')->render('AdditionalLocationsListingBundle::js/loadmap.html.twig', [
            'map_zoom' => $params['map_zoom'] ? $params['map_zoom'] : 15,
        ]);
    }

    private function getCustomSummaryData(&$params = null)
    {
        $params['data']['locationId'] = $params['data']['mainLocationId'];
        $params['_return'] = $params['data'];
    }

    private function getCustomSummaryDataMap(&$params = null)
    {
        $params['data']['locationId'] = $params['data']['mainLocationId'];
        $params['_return'] = $params['data'];
    }

    private function getMultipleLocationsMoreplacesLink(&$params = null)
    {
        $translate = $this->container->get('translator');
        if ($params['level']->hasDetail && !empty($params['data']['extraLocationId'])) {
            echo '<a class="text-info" href="'.$params['detailURL'].'"><span class="fa fa-map-o text-success"></span>'.$translate->trans('See more places').'</a><span class="divisor">|</span>';
        }
    }

    private function getListingBeforeBuildMapJSHelper(&$params = null)
    {
        $allowPins = $this->container->get('modstore.storage.service')->retrieveAndDestroy('allowPins');

        if ($allowPins) {

            $bound = new Bound();
            $bound->setVariable('detailBound');

            $domain = $this->container->get('multi_domain.information')->getId();
            $theme = lcfirst($this->container->get('theme.service')->getSelectedTheme()->getTitle());
            $defaultIconPath = 'assets/' . $theme . '/icons/';
            $customIconPath = 'custom/domain_' . $domain . '/theme/' . $theme . '/icons/';

            if (file_exists($customIconPath.'listing.svg')) {
                $iconPath = '/' . $customIconPath.'listing.svg';
            } else {
                $iconPath = $defaultIconPath;
            }

            $jsHandler = $this->container->get('javascripthandler');

            $searchOptions = $this->container->getParameter('search.config');
            $icons = $searchOptions['map']['icons'];

            if (file_exists($customIconPath . $icons['group']['url'])) {
                $clusterPath = '/'.$customIconPath . $icons['group']['url'];
            } else {
                $clusterPath = '/'.$defaultIconPath . $icons['group']['url'];
            }
            $mapJSVariable = 'detailMap';
            $clustererJSVariable = 'detailMapCluster';

            $params['map']->setVariable($mapJSVariable);

            /* Creates and configures the clusterer */
            $cluster = new MarkerCluster();
            $cluster->setType(MarkerClusterType::MARKER_CLUSTERER);
            $cluster->setVariable($clustererJSVariable);
            $cluster->setOption('styles', [
                [
                    'textColor' => $icons['group']['textColor'],
                    'url'       => $clusterPath,
                    'height'    => $icons['group']['height'],
                    'width'     => $icons['group']['width'],
                ],
            ]);

            $event = new Event(
                $clustererJSVariable,
                'clusterclick',
                'function(cluster){clusterClick(cluster)}'
            );

            $params['map']->getEventManager()->addDomEvent($event);

            if(!empty($params['map']->getOverlayManager()->getMarkers()[0])) {
                $cluster->addMarker($params['map']->getOverlayManager()->getMarkers()[0]);
                $bound->addExtendable($params['map']->getOverlayManager()->getMarkers()[0]);
            }

            for ($p = 0, $pMax = count($allowPins); $p < $pMax; $p++) {
                if ($allowPins[$p][1] && $allowPins[$p][2]) {
                    $markerExLoc = new Marker(new Coordinate((float)$allowPins[$p][1], (float)$allowPins[$p][2], true));

                    $markerExLoc->setOptions([
                        'clickable' => false,
                        'flat'      => true,
                    ]);

                    $markerExLoc->setIcon(new Icon($this->container->get('request')->getSchemeAndHttpHost().'/'.$iconPath));
                    $cluster->addMarker($markerExLoc);
                    $bound->addExtendable($markerExLoc);
                }
            }

            $params['map']->getOverlayManager()->setMarkerCluster($cluster);
            $params['map']->setBound($bound);
            $params['map']->setAutoZoom(true);

            $jsHandler->addJSBlock('AdditionalLocationsListingBundle::js/detail-map.html.twig');
            $jsHandler->addTwigParameter('mapJsVariable', $mapJSVariable);
            $jsHandler->addTwigParameter('clustererJSVariable', $clustererJSVariable);
        }
    }
}
