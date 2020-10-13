<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\YelpIntegration\Services;

use ArcaSolutions\CoreBundle\Inflector;
use DateTime;
use DateTimeZone;
use Exception;
use GuzzleHttp\Client as HttpClient;
use Symfony\Component\DependencyInjection\Container;
use Throwable;

class YelpService
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
     * @return void
     * @throws Exception
     */
    public function storeYelpBusiness($item)
    {
        $manager = $this->container->get('doctrine')->getManager();
        $translate = $this->container->get('translator');
        $yelp = $this->container->get('api.yelp');
        $helper = $this->container->get('helper.yelp');

        $yelpBusiness = [];

        if ($yelp->hasPrivateKey()) {

            $attributes = $this->container->get('helper.yelp')->retrieveSearchParametersGivenObject($item);

            if ($cachedSearch = $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->findOneBy(['searchCriteria' => json_encode($attributes)])) {

                $yelpBusiness = $cachedSearch->getResponse();

            } else {

                try {
                    $yelpBusinessessToSaveOnCache = [];
                    $yelp->setHttpClientVerify($this->container->get('request_stack')->getCurrentRequest()->isSecure());
                    if ($request = $yelp->search($attributes)) {
                        $yelpBusinessesFromSearch = $request['businesses'];
                        foreach ($yelpBusinessesFromSearch as $yelpBusinessFromSearch) {
                            if (array_key_exists('distance', $yelpBusinessFromSearch)) {
                                $distanceString = $yelpBusinessFromSearch['distance'];
                                if ($distanceString !== "" && is_numeric($distanceString)) {
                                    $distance = $distanceString + 0; //Force conversion to int or float
                                    if ($distance < 100) {
                                        $yelpBusinessessToSaveOnCache[] = $yelpBusinessFromSearch;
                                    }
                                    unset($distance);
                                }
                                unset($distanceString);
                            }
                        }
                        unset($yelpBusinessesFromSearch);
                        if (!empty($yelpBusinessessToSaveOnCache)) {
                            $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->save(json_encode($attributes),
                                $yelpBusinessessToSaveOnCache, $manager);
                        }

                    }
                    $yelpBusiness = $yelpBusinessessToSaveOnCache;
                    unset($yelpBusinessessToSaveOnCache);

                } catch (Exception $e) {
                    $logger = $this->container->get('logger');
                    $logger->critical('Yelp exception: ' . $e);
                }

            }


            foreach ($yelpBusiness as $eachBusiness) {

                if (Inflector::friendly_title($eachBusiness['name'], '-',
                        true) == Inflector::friendly_title($helper->titleHandler($item->getTitle(), true),
                        '-', true)) {
                    $yelpBusiness = $eachBusiness;
                    break;
                }
            }

            if (isset($yelpBusiness['id']) && $yelpAdditional = $yelp->getBusiness($yelpBusiness['id'])) {

                if (isset($yelpBusiness['price'])) {

                    $yelpBusiness['price'] = $yelpAdditional['price'];
                    $yelpBusinessCount = strlen($yelpAdditional['price']);

                    switch ($yelpBusinessCount) {
                        case '1':
                            $yelpBusiness['priceLevel'] = 'Inexpensive';
                            break;
                        case '2':
                            $yelpBusiness['priceLevel'] = 'Moderate';
                            break;
                        case '3':
                            $yelpBusiness['priceLevel'] = 'Pricey';
                            break;
                        case '4':
                            $yelpBusiness['priceLevel'] = 'Ultra High-End';
                            break;
                        default:
                            $yelpBusiness['priceLevel'] = 'Not available';
                            break;
                    }
                }


                if (array_key_exists('hours', $yelpAdditional) && is_array($yelpAdditional['hours'])) {
                    if (isset($yelpAdditional['hours'][0])) {

                        $hourFormat = $this->container->get('languagehandler')->getTimeFormat();

                        $yelpBusiness['hours'] = [];
                        for ($i = 0; $i <= 6; $i++) {
                            $yelpBusiness['hours'][$i] = [
                                'day' => $translate->transChoice('week.days', $i + 1, [], 'units'),
                                'hours' => $translate->trans('Closed'),
                            ];
                        }
                        if (array_key_exists('is_open_now', $yelpAdditional['hours'][0])) {
                            $yelpBusiness['is_open_now'] = $yelpAdditional['hours'][0]['is_open_now'];
                        }

                        foreach ($yelpAdditional['hours'][0]['open'] as $dayInfo) {
                            $sDateFromDayInfoStr = substr_replace($dayInfo['start'], ':', -2, -2);
                            $eDateFromDayInfoStr = substr_replace($dayInfo['end'], ':', -2, -2);
                            if ($sDateFromDayInfoStr === $eDateFromDayInfoStr) {
                                $explodedHour = explode(':', $sDateFromDayInfoStr);
                                if (!empty($explodedHour) && count($explodedHour) === 2) {
                                    $sHourStr = $explodedHour[0];
                                    $sMinuteStr = $explodedHour[1];
                                    if (is_numeric($sHourStr) && is_numeric($sMinuteStr)) {
                                        $sHour = intval($sHourStr);
                                        $sMinute = intval($sMinuteStr);
                                        $eHour = $sHour;
                                        $eMinute = $sMinute;
                                        if ($sMinute === 0) {
                                            if ($sHour === 0) {
                                                $eHour = 23;
                                            } else {
                                                $eHour = $sHour - 1;
                                            }
                                            $eMinute = 59;
                                        } else {
                                            $eMinute = $sMinute - 1;
                                        }
                                        $eDateFromDayInfoStr = sprintf("%02d:%02d", $eHour, $eMinute);
                                        unset($sHour,
                                            $sMinute,
                                            $eHour,
                                            $eMinute);
                                    }
                                    unset($sHourStr,
                                        $sMinuteStr);
                                }
                                unset($explodedHour);
                            }

                            $sDate = new DateTime($sDateFromDayInfoStr);
                            $start = $sDate->format($hourFormat);
                            $eDate = new DateTime($eDateFromDayInfoStr);
                            $end = $eDate->format($hourFormat);

                            if (isset($dayInfo['day']) && is_numeric($dayInfo['day'])) {
                                $dayIndexFromYelp = intval($dayInfo['day']);
                                $dayIndex = ($dayIndexFromYelp == 6) ? 0 : $dayIndexFromYelp + 1;
                                if (array_key_exists('is_overnight', $dayInfo) && array_key_exists('hours', $yelpBusiness)) {
                                    if (!is_array($yelpBusiness['hours'])) {
                                        $yelpBusiness['hours'] = [];
                                    }
                                    if (!array_key_exists($dayIndex, $yelpBusiness['hours'])) {
                                        $yelpBusiness['hours'][$dayIndex] = [
                                            'day' => $translate->transChoice('week.days', $dayIndex + 1, [], 'units'),
                                            'hours' => $translate->trans('Closed'),
                                        ];
                                    }

                                    if ($dayInfo['is_overnight'] === false || $sDate < $eDate) {
                                        $hoursEntry = [
                                            'hours_start' => $start,
                                            'hours_end' => $end
                                        ];

                                        if (!empty($hoursEntry)) {
                                            if (!is_array($yelpBusiness['hours'][$dayIndex]['hours'])) {
                                                $yelpBusiness['hours'][$dayIndex]['hours'] = [];
                                            }
                                            $yelpBusiness['hours'][$dayIndex]['hours'][] = $hoursEntry;
                                        }
                                    }
                                }
                            }
                        }
                        unset($dateNow);
                    }
                }
                unset($serverDateTimeZone,
                    $businessFromYelpTimeZone);
                if (array_key_exists('photos', $yelpAdditional) && is_array($yelpAdditional['photos'])) {
                    $yelpBusiness['photos'] = $yelpAdditional['photos'];
                }
            }
        }

        if (isset($yelpBusiness['id']) && !empty($yelpBusiness)) {
            $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
            if ($yelpReviewsArray = $yelp->getReviews($yelpBusiness['id'])) {
                $this->container->get('modstore.storage.service')->store('yelpReviews', $yelpReviewsArray['reviews']);
            }
        }
    }
}
