<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\GeoTargetedBanner;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\CoreBundle\Services\Utility;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use Exception;

class GeoTargetedBannerBundle extends Bundle
{
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
                Hooks::Register('generalsettings_after_save', function (&$params = null) {
                    return $this->getGeneralSettingsAfterSave($params);
                });
                Hooks::Register('generalsettings_after_render_form', function (&$params = null) {
                    return $this->getGeneralSettingsAfterRenderForm($params);
                });
                Hooks::Register('bannercode_after_save_new', function (&$params = null) {
                    return $this->getBannerCodeAfterSaveNew($params);
                });
                Hooks::Register('bannercode_after_save_existing', function (&$params = null) {
                    return $this->getBannerCodeAfterSaveExisting($params);
                });
                Hooks::Register('classbanner_after_makerow', function (&$params = null) {
                    return $this->getClassBannerAfterMakeRow($params);
                });
                Hooks::Register('classbanner_before_delete', function (&$params = null) {
                    return $this->getClassBannerBeforeDelete($params);
                });
                Hooks::Register('bannercode_after_setup_form', function (&$params = null) {
                    return $this->getBannerCodeAfterSetupForm($params);
                });
                Hooks::Register('bannercode_after_fill_formdata', function (&$params = null) {
                    return $this->getBannerCodeAfterFillFormData($params);
                });
                Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                    return $this->getModulesFooterAfterRenderJs($params);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('bannercode_after_save_new', function (&$params = null) {
                    return $this->getBannerCodeAfterSaveNew($params);
                });
                Hooks::Register('bannercode_after_save_existing', function (&$params = null) {
                    return $this->getBannerCodeAfterSaveExisting($params);
                });
                Hooks::Register('classbanner_after_makerow', function (&$params = null) {
                    return $this->getClassBannerAfterMakeRow($params);
                });
                Hooks::Register('classbanner_before_delete', function (&$params = null) {
                    return $this->getClassBannerBeforeDelete($params);
                });
                Hooks::Register('bannercode_after_setup_form', function (&$params = null) {
                    return $this->getBannerCodeAfterSetupForm($params);
                });
                Hooks::Register('bannercode_after_fill_formdata', function (&$params = null) {
                    return $this->getBannerCodeAfterFillFormData($params);
                });
                Hooks::Register('modulesfooter_after_render_js', function (&$params = null) {
                    return $this->getModulesFooterAfterRenderJs($params);
                });
                Hooks::Register('bannerextension_after_setup_bannerrepository', function (&$params = null) {
                    return $this->getBannerExtensionAfterSetupBannerRepository($params);
                });
                Hooks::Register('base_after_add_js', function (&$params = null) {
                    return $this->getBaseAfterAddJs($params);
                });
            }

            // Todo: revise hooks names
            Hooks::Register('location_general_defines', function (&$params = null) {
                return $this->getLocationGeneralDefines($params);
            });
            Hooks::Register('location_field_values', function (&$params = null) {
                return $this->getLocationFieldValues($params);
            });
            Hooks::Register('sitemgr_form_banner_location', function (&$params = null) {
                return $this->getSitemgrFormBanner($params);
            });
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of GeoTargetedBannerBundle.php', ['exception' => $e]);
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

    private function getGeneralSettingsAfterSave(&$params = null)
    {
        if(!empty($params['http_post_array']) && is_array($params['http_post_array']) && array_key_exists('save_plugin',$params['http_post_array'])) {
            $this->container->get('settings')->setSetting('miles_distance', $_POST['miles']);
            $this->container->get('settings')->setSetting('distance_type', $_POST['distance_type']);

            $params['success'] = true;
        }
    }

    private function getGeneralSettingsAfterRenderForm(&$params = null)
    {
        echo $this->container->get('templating')->render('GeoTargetedBannerBundle::sitemgr-general-settings.html.twig',
            [
                'miles'         => $this->container->get('settings')->getDomainSetting('miles_distance', true),
                'distance_type' => $this->container->get('settings')->getDomainSetting('distance_type', true),
            ]);
    }

    private function getBannerCodeAfterSaveNew(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        if ($_POST['new_location2_field'] != '' || $_POST['new_location3_field'] != '' || $_POST['new_location4_field'] != '' || $_POST['new_location5_field'] != '') {

            $locationsToSave = [];

            $_locations = explode(',', EDIR_LOCATIONS);
            $_defaultLocations = explode(',', EDIR_DEFAULT_LOCATIONS);
            $_nonDefaultLocations = array_diff_assoc($_locations, $_defaultLocations);

            foreach ($_defaultLocations as $defLoc) {
                $locationsToSave[$defLoc] = $_POST['location_'.$defLoc];
            }

            $stop_insert_location = false;

            foreach ($_nonDefaultLocations as $nonDefLoc) {

                if (trim($_POST['location_'.$nonDefLoc]) != '') {

                    $locationsToSave[$nonDefLoc] = $_POST['location_'.$nonDefLoc];

                } else if (!$stop_insert_location) {

                    if (!$_POST['new_location'.$nonDefLoc.'_field']) {

                        $stop_insert_location = true;

                    } else {

                        $objNewLocationLabel = 'Location'.$nonDefLoc;
                        $objNewLocation = new $objNewLocationLabel;

                        foreach ($locationsToSave as $level => $value) {
                            $objNewLocation->setString('location_'.$level, $value);
                        }

                        $objNewLocation->setString('name', $_POST['new_location'.$nonDefLoc.'_field']);
                        $objNewLocation->setString('friendly_url', $_POST['new_location'.$nonDefLoc.'_friendly']);
                        $objNewLocation->setString('default', 'n');
                        $objNewLocation->setString('featured', 'n');

                        $newLocationFlag = $objNewLocation->retrievedIfRepeated($_locations);

                        if ($newLocationFlag) {
                            $objNewLocation->setNumber('id', $newLocationFlag);
                        } else {
                            $objNewLocation->Save();
                        }

                        $_POST['location_'.$nonDefLoc] = $objNewLocation->getNumber('id');
                        $locationsToSave[$nonDefLoc] = $_POST['location_'.$nonDefLoc];

                    }

                }

            }
        }

        $latitude = (isset($_POST['latitude']) && !empty($_POST['latitude'])) ? $_POST['latitude'] : null;
        $longitude = (isset($_POST['longitude']) && !empty($_POST['longitude'])) ? $_POST['longitude'] : null;
        $map_zoom = (isset($_POST['map_zoom']) && !empty($_POST['map_zoom'])) ? $_POST['map_zoom'] : 0;
        $location_1 = (isset($_POST['location_1']) && !empty($_POST['location_1'])) ? $_POST['location_1'] : 0;
        $location_2 = (isset($_POST['location_2']) && !empty($_POST['location_2'])) ? $_POST['location_2'] : 0;
        $location_3 = (isset($_POST['location_3']) && !empty($_POST['location_3'])) ? $_POST['location_3'] : 0;
        $location_4 = (isset($_POST['location_4']) && !empty($_POST['location_4'])) ? $_POST['location_4'] : 0;
        $location_5 = (isset($_POST['location_5']) && !empty($_POST['location_5'])) ? $_POST['location_5'] : 0;
        $distance = (isset($_POST['distance']) && !empty($_POST['distance'])) ? $_POST['distance'] : null;

        $statement = $connection->prepare('SELECT * FROM Banner_GeoTargeted WHERE id = :id');
        $statement->bindValue('id', $params['bannerObj']->id);
        $statement->execute();

        $results = $statement->fetch();

        if ($results) {
            $query = 'UPDATE
                Banner_GeoTargeted
            SET
                latitude = :latitude,
                longitude = :longitude,
                map_zoom = :map_zoom,
                location_1 = :location_1,
                location_2 = :location_2,
                location_3 = :location_3,
                location_4 = :location_4,
                location_5 = :location_5,
                distance = :distance
            WHERE
                id = :id';
        } else {
            $query = 'INSERT INTO Banner_GeoTargeted VALUES (:id, :location_1, :location_2, :location_3, :location_4, :location_5, :latitude, :longitude, :map_zoom, :distance)';
        }

        $statement = $connection->prepare($query);
        $statement->bindValue('id', $params['bannerObj']->id);
        $statement->bindValue('latitude', $latitude);
        $statement->bindValue('longitude', $longitude);
        $statement->bindValue('map_zoom', $map_zoom);
        $statement->bindValue('location_1', $location_1);
        $statement->bindValue('location_2', $location_2);
        $statement->bindValue('location_3', $location_3);
        $statement->bindValue('location_4', $location_4);
        $statement->bindValue('location_5', $location_5);
        $statement->bindValue('distance', $distance);
        $statement->execute();

    }

    private function getBannerCodeAfterSaveExisting(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        if ($_POST['new_location2_field'] != '' || $_POST['new_location3_field'] != '' || $_POST['new_location4_field'] != '' || $_POST['new_location5_field'] != '') {

            $locationstosave = [];

            $_locations = explode(',', edir_locations);
            $_defaultlocations = explode(',', edir_default_locations);
            $_nondefaultlocations = array_diff_assoc($_locations, $_defaultlocations);

            foreach ($_defaultlocations as $defloc) {
                $locationstosave[$defloc] = $_post['location_'.$defloc];
            }

            $stop_insert_location = false;

            foreach ($_nondefaultlocations as $nondefloc) {

                if (trim($_post['location_'.$nondefloc]) != '') {

                    $locationstosave[$nondefloc] = $_post['location_'.$nondefloc];

                } else if (!$stop_insert_location) {

                    if (!$_post['new_location'.$nondefloc.'_field']) {

                        $stop_insert_location = true;

                    } else {

                        $objnewlocationlabel = 'location'.$nondefloc;
                        $objnewlocation = new $objnewlocationlabel;

                        foreach ($locationstosave as $level => $value) {
                            $objnewlocation->setstring('location_'.$level, $value);
                        }

                        $objnewlocation->setstring('name', $_post['new_location'.$nondefloc.'_field']);
                        $objnewlocation->setstring('friendly_url', $_post['new_location'.$nondefloc.'_friendly']);
                        $objnewlocation->setstring('default', 'n');
                        $objnewlocation->setstring('featured', 'n');

                        $newlocationflag = $objnewlocation->retrievedifrepeated($_locations);

                        if ($newlocationflag) {
                            $objnewlocation->setnumber('id', $newlocationflag);
                        } else {
                            $objnewlocation->save();
                        }

                        $_post['location_'.$nondefloc] = $objnewlocation->getnumber('id');
                        $locationstosave[$nondefloc] = $_post['location_'.$nondefloc];

                    }

                }
            }
        }

        $latitude = (isset($_POST['latitude']) && !empty($_POST['latitude'])) ? $_POST['latitude'] : null;
        $longitude = (isset($_POST['longitude']) && !empty($_POST['longitude'])) ? $_POST['longitude'] : null;
        $map_zoom = (isset($_POST['map_zoom']) && !empty($_POST['map_zoom'])) ? $_POST['map_zoom'] : 0;
        $location_1 = (isset($_POST['location_1']) && !empty($_POST['location_1'])) ? $_POST['location_1'] : 0;
        $location_2 = (isset($_POST['location_2']) && !empty($_POST['location_2'])) ? $_POST['location_2'] : 0;
        $location_3 = (isset($_POST['location_3']) && !empty($_POST['location_3'])) ? $_POST['location_3'] : 0;
        $location_4 = (isset($_POST['location_4']) && !empty($_POST['location_4'])) ? $_POST['location_4'] : 0;
        $location_5 = (isset($_POST['location_5']) && !empty($_POST['location_5'])) ? $_POST['location_5'] : 0;
        $distance = (isset($_POST['distance']) && !empty($_POST['distance'])) ? $_POST['distance'] : null;

        $statement = $connection->prepare('SELECT * FROM Banner_GeoTargeted WHERE id = :id');
        $statement->bindValue('id', $params['bannerObj']->id);
        $statement->execute();

        $results = $statement->fetch();

        if ($results) {
            $query = 'UPDATE
                Banner_GeoTargeted
            SET
                latitude   = :latitude,
                longitude  = :longitude,
                map_zoom   = :map_zoom,
                location_1 = :location_1,
                location_2 = :location_2,
                location_3 = :location_3,
                location_4 = :location_4,
                location_5 = :location_5,
                distance   = :distance
            WHERE
                id         = :id';
        } else {
            $query = 'INSERT INTO Banner_GeoTargeted VALUES (:id, :location_1, :location_2, :location_3, :location_4, :location_5, :latitude, :longitude, :map_zoom, :distance)';
        }

        $statement = $connection->prepare($query);
        $statement->bindValue('id', $params['bannerObj']->id);
        $statement->bindValue('latitude', $latitude);
        $statement->bindValue('longitude', $longitude);
        $statement->bindValue('map_zoom', $map_zoom);
        $statement->bindValue('location_1', $location_1);
        $statement->bindValue('location_2', $location_2);
        $statement->bindValue('location_3', $location_3);
        $statement->bindValue('location_4', $location_4);
        $statement->bindValue('location_5', $location_5);
        $statement->bindValue('distance', $distance);
        $statement->execute();
    }

    private function getClassBannerAfterMakeRow(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('SELECT * FROM Banner_GeoTargeted WHERE id = :id LIMIT 1');
        $statement->bindValue('id', $params['that']->id);
        $statement->execute();

        $results = $statement->fetch();

        $params['that']->latitude = $results['latitude'] ? $results['latitude'] : ($params['that']->latitude ? $params['that']->latitude : '');
        $params['that']->longitude = $results['longitude'] ? $results['longitude'] : ($params['that']->longitude ? $params['that']->longitude : '');
        $params['that']->map_zoom = $results['map_zoom'] ? $results['map_zoom'] : 0;
        $params['that']->location_1 = $results['location_1'] ? $results['location_1'] : 0;
        $params['that']->location_2 = $results['location_2'] ? $results['location_2'] : 0;
        $params['that']->location_3 = $results['location_3'] ? $results['location_3'] : 0;
        $params['that']->location_4 = $results['location_4'] ? $results['location_4'] : 0;
        $params['that']->location_5 = $results['location_5'] ? $results['location_5'] : 0;
        $params['that']->distance = $results['distance'] ? $results['distance'] : null;
    }

    private function getClassBannerBeforeDelete(&$params = null)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $statement = $connection->prepare('DELETE FROM Banner_GeoTargeted WHERE id = :id');
        $statement->bindValue('id', $params['that']->id);
        $statement->execute();
    }

    private function getBannerCodeAfterSetupForm(&$params = null)
    {
        $params['hasValidCoord'] = false;
        $params['loadMap'] = false;

        if (GOOGLE_MAPS_ENABLED == 'on' && $this->container->get('settings')->getDomainSetting('google_map_status') == 'on') {
            $params['loadMap'] = true;
            $params['formLoadMap'] = 'document.banner';

            if ($params['latitude'] && $params['longitude'] && is_numeric($params['latitude']) && is_numeric($params['longitude'])) {
                $params['hasValidCoord'] = true;
            }

            if ($params['id'] || $params['hasValidCoord']) {
                $_COOKIE['showMapForm'] = 0;
            }
        }
    }

    private function getBannerCodeAfterFillFormData(&$params = null)
    {
        $params['latitude'] = $_POST['latitude'] ? $_POST['latitude'] : $params['bannerObj']->getString('latitude');
        $params['longitude'] = $_POST['longitude'] ? $_POST['longitude'] : $params['bannerObj']->getString('longitude');

        foreach ($params['non_default_locations'] as $non_default_locations) {
            $params['location_'.$non_default_locations] = $_POST['location_'.$non_default_locations] ? $_POST['location_'.$non_default_locations] : $params['bannerObj']->getString('location_'.$non_default_locations);
        }

        $stop_search_locations = false;

        if ($params['non_default_locations']) {

            foreach ($params['non_default_locations'] as $_location_level) {

                if ($params['location_'.$_location_level]) {
                    $params['location_'.$_location_level] = $params['location_'.$_location_level];
                } else {
                    $stop_search_locations = true;
                }

                system_retrieveLocationRelationship($params['non_default_locations'], $_location_level,
                    $_location_father_level, $_location_child_level);

                if ($params['location_'.$_location_level] && $_location_child_level) {
                    if (!$stop_search_locations) {
                        $objLocationLabel = 'Location'.$_location_child_level;
                        $params['Location'.$_location_child_level] = new $objLocationLabel;
                        $params['Location'.$_location_child_level]->SetString('location_'.$_location_level,
                            $params['location_'.$_location_level]);
                        $params['locations'.$_location_child_level] = $params['Location'.$_location_child_level]->retrieveLocationByLocation($_location_level);
                    } else {
                        $params['locations'.$_location_child_level] = '';
                    }
                } else {
                    $stop_search_locations = true;
                }

            }

            unset ($_location_father_level);
            unset ($_location_child_level);
            unset ($_location_level);
        }
    }

    private function getModulesFooterAfterRenderJs(&$params = null)
    {
        if (string_strpos($_SERVER['PHP_SELF'], '/banner/') !== false) {
            echo $this->container->get('templating')->render('GeoTargetedBannerBundle::js/footer-scripts.js.twig');
        }
    }

    private function getBannerExtensionAfterSetupBannerRepository(&$params = null)
    {
        $doctrine = $this->container->get('doctrine');
        $params['repository'] = $doctrine->getRepository('GeoTargetedBannerBundle:BannerGeoTargeted');

        $distanceMax = $this->container->get('settings')->getDomainSetting('miles_distance');
        if ($this->container->get('settings')->getDomainSetting('distance_type') == 'kilometers') {
            $distanceMax /= 1.60934;
        }

        $locations = Utility::extractGeoPoint($this->container->get('request_stack')->getCurrentRequest()
            ->cookies->get($this->container->get('search.engine')->getGeoLocationCookieName()));

        $geolocation = [
            'distanceMax' => !empty($distanceMax) ? $distanceMax : '2500',
            'latitude'    => isset($locations['lat']) ? $locations['lat'] : null,
            'longitude'   => isset($locations['lon']) ? $locations['lon'] : null,
        ];

        $params['categorizedSections'] = [
            'sections'    => $params['categorizedSections'],
            'geolocation' => $geolocation,
        ];
    }

    // Todo: methods to hooks revision names
    private function getLocationGeneralDefines(&$params)
    {
        $params['non_default_locations'] = '';
        $params['default_locations_info'] = '';

        if (EDIR_DEFAULT_LOCATIONS) {

            system_retrieveLocationsInfo($params['non_default_locations'], $params['default_locations_info']);

            $last_default_location = $params['default_locations_info'][count($params['default_locations_info']) - 1]['type'];
            $last_default_location_id = $params['default_locations_info'][count($params['default_locations_info']) - 1]['id'];

            if ($params['non_default_locations']) {
                $objLocationLabel = 'Location'.$params['non_default_locations'][0];
                $params['Location'.$params['non_default_locations'][0]] = new $objLocationLabel;
                $params['Location'.$params['non_default_locations'][0]]->SetString('location_'.$last_default_location,
                    $last_default_location_id);
                $params['locations'.$params['non_default_locations'][0]] = $params['Location'.$params['non_default_locations'][0]]->retrieveLocationByLocation($last_default_location);
            }

        } else {

            $params['non_default_locations'] = explode(',', EDIR_LOCATIONS);
            $objLocationLabel = 'Location'.$params['non_default_locations'][0];
            $params['Location'.$params['non_default_locations'][0]] = new $objLocationLabel;
            $params['locations'.$params['non_default_locations'][0]] = $params['Location'.$params['non_default_locations'][0]]->retrieveAllLocation();

        }
    }

    private function getLocationFieldValues(&$params = null)
    {
        $url_redirect = $params['url_redirect'];
        $screen = $params['screen'];
        $letter = $params['letter'];
        $url_search_params = $params['url_search_params'];

        if (string_strpos($params['url_base'], '/'.MEMBERS_ALIAS.'')) {
            $by_key = ['id', 'account_id'];
            $by_value = [db_formatNumber($params['id']), sess_getAccountIdFromSession()];
            $banner = db_getFromDB('banner', $by_key, $by_value, 1, '', 'object', SELECTED_DOMAIN_ID);
        } else {
            $banner = db_getFromDB('banner', 'id', db_formatNumber($params['id']), 1, '', 'object', SELECTED_DOMAIN_ID);
        }

        if ((sess_getAccountIdFromSession() != $banner->getNumber('account_id')) && (!string_strpos($params['url_base'],
                '/'.SITEMGR_ALIAS.''))) {
            header("Location: $url_redirect/".($params['search_page'] ? 'search.php' : 'index.php').'?message='.$params['message']."&screen=$screen&letter=$letter".($params['url_search_params'] ? "&$url_search_params" : '').'');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $banner->extract();
        }

        $stop_search_locations = false;

        if ($params['non_default_locations']) {

            foreach ($params['non_default_locations'] as $_location_level) {

                system_retrieveLocationRelationship($params['non_default_locations'], $_location_level,
                    $_location_father_level, $_location_child_level);

                if (${'location_'.$_location_level} && $_location_child_level) {

                    if (!$stop_search_locations) {

                        $objLocationLabel = 'Location'.$_location_child_level;
                        $params['Location'.$_location_child_level] = new $objLocationLabel;
                        $params['Location'.$_location_child_level]->SetString('location_'.$_location_level,
                            $params['location_'.$_location_level]);
                        $params['locations'.$_location_child_level] = $params['Location'.$_location_child_level]->retrieveLocationByLocation($_location_level);

                    } else {

                        $params['locations'.$_location_child_level] = '';

                    }

                } else {

                    $stop_search_locations = true;

                }
            }

            unset($_location_father_level);
            unset($_location_child_level);
            unset($_location_level);

        }

    }

    private function getSitemgrFormBanner(&$params = null)
    {
        echo $this->container->get('templating')->render('GeoTargetedBannerBundle::sitemgr-form-banner-customdistance.html.twig',
            [
                'distance' => $params['distance'],
            ]);

        $loadMap = $params['loadMap'];
        $_default_locations_info = $params['default_locations_info'];
        $_non_default_locations = $params['non_default_locations'];
        $highlight = $params['highlight'];
        $sitemgrSearch = $params['sitemgrSearch'];
        $formLoadMap = $params['formLoadMap'];

        for ($i = 0, $iMax = count($_non_default_locations); $i < $iMax; $i++) {
            ${'location'.$_non_default_locations[$i]} = $params['location'.$_non_default_locations[$i]];
            ${'locations'.$_non_default_locations[$i]} = $params['locations'.$_non_default_locations[$i]];
            ${'location_'.$_non_default_locations[$i]} = $params['location_'.$_non_default_locations[$i]];
        }

        $_location_father_level = $params['location_father_level'];
        $_location_child_level = $params['location_child_level'];

        include EDIRECTORY_ROOT.'/includes/code/load_location.php';

        if ($params['loadMap']) {

            echo $this->container->get('templating')->render('GeoTargetedBannerBundle::sitemgr-form-banner.html.twig', [
                'hasValidCoord'      => $params['hasValidCoord'],
                'latitude_longitude' => $params['latitude_longitude'],
                'map_zoom'           => $params['map_zoom'],
                'maptuning_done'     => $params['maptuning_done'],
                'latitude'           => $params['latitude'],
                'longitude'          => $params['longitude'],
            ]);
        }

        echo $this->container->get('templating')->render('GeoTargetedBannerBundle::sitemgr-form-banner-closediv.html.twig');
    }

    private function getBaseAfterAddJs(&$params = null)
    {
        if(empty($this->container->get('modstore.storage.service')->retrieve('needGeoLocation'))) {
            $this->container->get('modstore.storage.service')->store('needGeoLocation', 'true');

            echo $this->container->get('templating')->render('GeoTargetedBannerBundle:js:html5geo.html.twig');
        }
    }
}
