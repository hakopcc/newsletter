<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\TailoredMapListing\Services;

use ArcaSolutions\ModStoresBundle\Plugins\TailoredMapListing\Entity\Sorters\TailoredMapSorter;
use ArcaSolutions\SearchBundle\Entity\Sorters\DistanceSorter;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use Ivory\GoogleMap\Base\Bound;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Event\Event;
use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\MapHelperBuilder;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Overlay\MarkerCluster;
use Ivory\GoogleMap\Overlay\MarkerClusterType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TailoredMapListingService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function buildTailoredMap($lat, $long, $zoom, $where = null)
    {
        $mapJsVariable = 'tailoredMap';
        $clustererJSVariable = 'tailoredMarkerClusterer';

        if (!empty($_COOKIE['edirectory_geolocation_coordinates'])) {
            $cookie_geolocations = explode(',', $_COOKIE['edirectory_geolocation_coordinates']);
            if ($cookie_geolocations[0] != -55 && $cookie_geolocations[1] != -10) {
                $lat = $cookie_geolocations[0];
                $long = $cookie_geolocations[1];
                $zoom = 14;
            }
        }

        /* Retrieving icon names from configuration files */
        $searchOptions = $this->container->getParameter('search.config');
        $icons = $searchOptions['map']['icons'];
        $domain = $this->container->get('multi_domain.information')->getId();
        $theme = lcfirst($this->container->get('theme.service')->getSelectedTheme()->getTitle());
        $defaultIconPath = 'assets/' . $theme . '/icons/';
        $customIconPath = 'custom/domain_' . $domain . '/theme/' . $theme . '/icons/';

        $zoom = $zoom !== null ? (int)$zoom : (int)$this->container->getParameter('tailored_map_listing.default_zoom');
        $lat = $zoom !== null ? (float)$lat : (float)$this->container->getParameter('tailored_map_listing.default_lattitude');
        $long = $zoom !== null ? (float)$long : (float)$this->container->getParameter('tailored_map_listing.default_longtide');

        $event = new SearchEvent('', true, ['lat' => $lat, 'long' => $long], $where);
        $event->setDefaultSorter(new DistanceSorter($this->container));
        $this->container->get('event_dispatcher')->dispatch('search.listing.map', $event);

        $map = new Map();

        $map->setVariable($mapJsVariable);
        $map->setMapOption('scrollwheel', false);
        $map->setCenter(new Coordinate((float) $lat, (float) $long));
        $map->setMapOption('minZoom', 2);
        $map->setMapOption('zoom', $zoom);

        $event = new Event(
            $mapJsVariable,
            'idle',
            'function(){idle()}'
        );

        $map->getEventManager()->addDomEvent($event);

        if (file_exists($customIconPath . $icons['group']['url'])) {
            $iconPath = $customIconPath . $icons['group']['url'];
        } else {
            $iconPath = $defaultIconPath . $icons['group']['url'];
        }

        /* Creates and configures the clusterer */
        $cluster = new MarkerCluster();
        $cluster->setType(MarkerClusterType::MARKER_CLUSTERER);
        $cluster->setVariable($clustererJSVariable);
        $cluster->setOption('styles', [
            [
                'textColor' => $icons['group']['textColor'],
                'url'       => $this->container->get('request')->getSchemeAndHttpHost() . '/' . $iconPath,
                'height'    => $icons['group']['height'],
                'width'     => $icons['group']['width'],
            ],
        ]);

        $event = new Event(
            $clustererJSVariable,
            'clusterclick',
            'function(cluster){clusterClick(cluster)}'
        );

        $map->getEventManager()->addDomEvent($event);

        $map->getOverlayManager()->setMarkerCluster($cluster);

        /* Adds all necessary JS files for this to work */
        $jsHandler = $this->container->get('javascripthandler');

        $mapJSHelper = MapHelperBuilder::create()->build()->renderJavascript($map);
        $apiHelper = ApiHelperBuilder::create()->setKey($this->container->get('settings')->getDomainSetting('google_api_key'))->build()->render([$map]);

        $jsHandler->addJSBlock('TailoredMapListingBundle::js/mapTailored.html.twig');
        $jsHandler->addTwigParameter('mapJsVariable', $mapJsVariable);
        $jsHandler->addTwigParameter('clustererJSVariable', $clustererJSVariable);
        $jsHandler->addTwigParameter('mapJSHelper', $mapJSHelper);
        $jsHandler->addTwigParameter('apiHelper', $apiHelper);
        $jsHandler->addTwigParameter('searchEngine', true);

        return $map;
    }

    public function searchBounds($coordinates, $module = 'listing', $avois_ids = [], $where = null)
    {
        $searchEngine = $this->container->get('search.engine');

        $options = [
            'top_left'     => $coordinates[0],
            'bottom_right' => $coordinates[1],
            'avoid_ids'    => $avois_ids,
        ];

        $event = new SearchEvent('', true, $options, $where);

        $event->setDefaultSorter(new TailoredMapSorter($this->container));

        $this->container->get('event_dispatcher')->dispatch('search.tailoredplacement.map', $event);

        $return = null;

        /* In order not to overload the user's machine, we limit map results to five thousand */
        if ($search = $searchEngine->search($event,
            $this->container->getParameter('tailored_map_listing.max_pins_map'))) {

            $results = $search->search();

            if ($results->getTotalHits() > 0 && $this->container->get('settings')->getDomainSetting('google_map_status') == 'on') {

                $listings = [];
                $count = 0;

                foreach ($results->getResults() as $result) {

                    $iconId = null;
                    $data = $result->getData();

                    if (isset($data['scriptedFieldData'])) {
                        $data = $data['scriptedFieldData'][0];
                    }

                    $categoriesArray = [];
                    if (!empty($data['categoryId'])) {

                        $iconRepository = $this->container->get('doctrine')->getRepository('TailoredMapListingBundle:ModuleCategoryIcon');

                        if (!empty($data['categoryId'])) {

                            $categories = explode(' ', $data['categoryId']);

                            foreach ($categories as $category) {

                                $categoryId = explode(':', $category)[1];
                                $iconId = null;

                                do {

                                    if ($categoryIconObj = $iconRepository->findOneBy(['categoryId' => $categoryId])) {
                                        $iconId = $categoryIconObj->getPinId();
                                        if (!empty($iconId)) {
                                            $imageRepository = $this->container->get('doctrine')->getRepository('ImageBundle:Image');
                                            $imageType = $imageRepository->find($iconId)->getType();
                                            break;
                                        }

                                        if (empty($categoriesArray[$categoryId]) && $categoryObj = $this->container->get('doctrine')->getRepository('ListingBundle:ListingCategory')->find($categoryId)) {
                                            $categoriesArray[$categoryId] = [
                                                'featured' => $categoryObj->getFeatured(),
                                            ];
                                        }
                                    }


                                    if (empty($iconId) && $categoryObj = $this->container->get('doctrine')->getRepository('ListingBundle:ListingCategory')->find($categoryId)) {
                                        $categoryId = $categoryObj->getCategoryId();
                                    }

                                } while (!empty($categoryId));

                                if (!empty($iconId)) {
                                    break;
                                }

                            }

                        }

                        if (empty($data['geoLocation'])) {
                            continue;
                        }

                        $listings[$count] = $data;
                        if ($itemGeoLocation = $data['geoLocation'] and !empty($itemGeoLocation['lat']) and !empty($itemGeoLocation['lon'] and ($data['suggest']['what']['payload']['type'] == $module or $module == 'global'))) {
                            if (!empty($iconId)) {
                                $listings[$count]['icon'] = '/custom/domain_'.$this->container->get('multi_domain.information')->getId().'/image_files/sitemgr_photo_'.$iconId.'.'.strtolower($imageType);
                            } else {
                                $domain = $this->container->get('multi_domain.information')->getId();
                                $theme = lcfirst($this->container->get('theme.service')->getSelectedTheme()->getTitle());
                                $defaultIconPath = 'assets/' . $theme . '/icons/listing.svg';
                                $customIconPath = 'custom/domain_' . $domain . '/theme/' . $theme . '/icons/listing.svg';

                                if (file_exists($customIconPath)) {
                                    $listings[$count]['icon'] = $customIconPath;
                                } else {
                                    $listings[$count]['icon'] = $defaultIconPath;
                                }
                            }
                        }

                        $listings[$count]['itemElement']['item'] = $result->getId();
                        $listings[$count]['itemElement']['itemtype'] = $result->getType();
                        $listings[$count]['itemId'] = $result->getId();

                        //Set the pins z-index by featured category and listing level
                        $zIndex = 0;

                        if (!empty($result->categoryId)) {
                            $categoryId = explode(':', $result->categoryId);
                            $categoryId = $categoryId[1];
                            $zIndex = empty($categoriesArray[$categoryId]) ? 0 : 1000;
                        }

                        $listings[$count]['zIndex'] = 1000 - $result->level + $zIndex;

                        $count++;

                    }
                }

                $return = $listings;
            }
        }

        return $return;
    }
}