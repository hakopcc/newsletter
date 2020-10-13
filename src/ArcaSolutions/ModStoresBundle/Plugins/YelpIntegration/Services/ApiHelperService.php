<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\YelpIntegration\Services;

use ArcaSolutions\CoreBundle\Entity\Location1;
use ArcaSolutions\CoreBundle\Entity\Location2;
use ArcaSolutions\CoreBundle\Entity\Location3;
use ArcaSolutions\CoreBundle\Entity\Location4;
use ArcaSolutions\CoreBundle\Entity\Location5;
use ArcaSolutions\ListingBundle\Entity\Listing;
use Symfony\Component\DependencyInjection\Container;
use Throwable;

class ApiHelperService
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $item
     * @return array
     * @throws Throwable
     */
    public function retrieveSearchParameters(array $item)
    {
        isset($item['scriptedFieldData'][0]) and $item = $item['scriptedFieldData'][0];

        $location = [];
        $itemTitle = $this->titleHandler($item['title']);
        $keyword = ['term' => $itemTitle];

        if ($item['geoLocation']['lat'] && $item['geoLocation']['lon']) {

            $location = [
                'latitude'  => $item['geoLocation']['lat'],
                'longitude' => $item['geoLocation']['lon'],
            ];

        } elseif ($item['locationId']) {

            $locationsName = [];
            $locationsObj = $this->container->get('locationyelp.helper')->convertElasticStringToObjects($item['locationId']);

            foreach ($locationsObj as $location) {
                /**
                 * @var Location1|Location2|Location3|Location4|Location5 $location
                 */
                $locationsName[] = $location->getName();
            }

            $location = ['location' => implode(', ', $locationsName)];

        } else if (!empty($item['address'])||!empty($item['address2'])) {
            $location = [
                'location' => ((!empty($item['address']))?((!empty($item['address2']))?$item['address2'].' ':''):$item['address'])
            ];
        }

        return array_merge($keyword, $location);
    }

    public function titleHandler($title = null, $friendlyTitle = false)
    {
        $title = str_replace('’', '\'', $title);

        if (!$friendlyTitle && strpos($title, '&') !== false) {
            $title = substr($title, 0, strpos($title, '&'));
        }

        return $title;
    }

    public function retrieveSearchParametersGivenObject(Listing $item)
    {
        $location = [];
        $itemTitle = $this->titleHandler($item->getTitle());

        $keyword = ['term' => $itemTitle];

        if ($item->getLatitude() && $item->getLongitude()) {

            $location = [
                'latitude'  => $item->getLatitude(),
                'longitude' => $item->getLongitude(),
            ];

        } elseif ($item->getLocation1()) {

            $locationId = 'L1:'.$item->getLocation1();
            if ($item->getLocation3()) {
                $locationId .= ' L3:'.$item->getLocation3();
            }
            if ($item->getLocation4()) {
                $locationId .= ' L4:'.$item->getLocation4();
            }

            $locationsObj = $this->container->get('locationyelp.helper')->convertElasticStringToObjects($locationId);
            foreach ($locationsObj as $location) {
                /**
                 * @var Location1|Location2|Location3|Location4|Location5 $location
                 */
                $locationsName[] = $location->getName();
            }

            $location = ['location' => implode(', ', $locationsName)];
        } else if (!empty($item->getAddress())||!empty($item->getAddress2())) {
            $address = $item->getAddress();
            $address2 = $item->getAddress2();
            $location = [
                        'location' => ((!empty($address))?((!empty($address2))?$address.' ':''):$address).$address
            ];
        } else {
            if(!empty($this->container)){
                $settings = $this->container->get('settings');
                if(!empty($settings)){
                    $contact_country = $settings->getSetting('contact_country');
                    if(!empty($contact_country)) {
                        $location = ['location' => $contact_country];
                    }
                    unset($contact_country);
                }
                unset($settings);
            }
        }

        return array_merge($keyword, $location);
    }
}
