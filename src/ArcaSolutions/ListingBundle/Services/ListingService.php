<?php

namespace ArcaSolutions\ListingBundle\Services;

use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\ListingBundle\Repository\ListingCategoryRepository;
use ArcaSolutions\ListingBundle\Repository\ListingRepository;
use ArcaSolutions\ListingBundle\Repository\ListingTemplateRepository;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\SearchBundle\Services\SearchEngine;
use ArcaSolutions\WebBundle\Entity\DiscountCode;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Elastica\Query;
use Elastica\Result;
use Exception;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Overlay\Icon;
use Ivory\GoogleMap\Overlay\Marker;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ListingService
 * @package ArcaSolutions\ListingBundle\Services
 */
class ListingService
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var DoctrineRegistry
     */
    private $doctrine;

    /**
     * ListingService constructor.
     * @param ContainerInterface $container
     * @param DoctrineRegistry $doctrine
     */
    public function __construct(ContainerInterface $container, DoctrineRegistry $doctrine)
    {
        $this->container = $container;
        $this->doctrine = $doctrine;
    }

    /**
     * @param string $hoursWork
     * @return array
     */
    public function formatHoursWork($hoursWork = '')
    {
        $hours = [];

        if(!empty($hoursWork)) {
            $hours = array_fill_keys([0, 1, 2, 3, 4, 5, 6], []);
            $hoursWork = json_decode($hoursWork, true);

            foreach ($hoursWork as $hourWork) {
                $hours[$hourWork['weekday']][] = ['hours_start' => $hourWork['hours_start'], 'hours_end' => $hourWork['hours_end']];
            }

            // sort by weekday
            ksort($hours);

            // sort by hours_start
            foreach ($hours as &$hour) {
                usort($hour, function ($a, $b) {
                    if ($a['hours_start'] === $b['hours_start']) {
                        return 0;
                    }

                    return $a['hours_start'] < $b['hours_start'] ? -1 : 1;
                });
            }
            unset($hour);
        }

        HookFire('listingservice_after_formathourswork', [
            'hours' => &$hours
        ]);

        return $hours;
    }

    /**
     * @param Listing $listing
     * @return string
     */
    public function getCoverImage(Listing $listing)
    {
        $coverImage = '';

        if($listing->getCoverImage() !== null) {
            if ($listing->getCoverImage()->getUnsplash()) {
                $coverImage = $listing->getCoverImage()->getUnsplash();
            } else {
                $coverImage = $this->container->get('templating.helper.assets')
                    ->getUrl($this->container->get('imagehandler')->getPath($listing->getCoverImage()), 'domain_images');
            }
        }

        return $coverImage;
    }

    /**
     * @param Listing $listing
     * @return array
     */
    public function getLogoImage(Listing $listing)
    {
        $smallLogoImage = '';
        $logoImage = '';

        $imagine_filter = $this->container->get('liip_imagine.cache.manager');

        if($listing->getLogoImage() !== null) {
            $logo = $this->container->get('templating.helper.assets')
                ->getUrl($this->container->get('imagehandler')->getPath($listing->getLogoImage()), 'domain_images');

            $smallLogoImage = $imagine_filter->getBrowserPath($logo, 'logo_icon_2');
            $logoImage = $imagine_filter->getBrowserPath($logo, 'logo_icon_3');
        }

        return [
            '80x80' => $smallLogoImage,
            '96x96' => $logoImage,
        ];
    }

    /**
     * @param Listing $listing
     * @return string
     */
    public function getAddress(Listing $listing)
    {
        $addressArray = [];

        $locations = $this->container->get('location.service')->getLocations($listing);

        if(!empty($listing->getAddress())) {
            $addressArray[] = $listing->getAddress();
        }

        if(!empty($listing->getAddress2())) {
            $addressArray[] = $listing->getAddress2();
        }

        foreach (array_filter($locations) as $levelLocation => $location) {
            if($location->level !== 1) {
                if (!empty($location->getName())) {
                    $addressArray[] = $location->getName();
                }
            } else {
                $country = $location->getName();
            }
        }

        if(!empty($listing->getZipCode()) && (!empty($addressArray) || !empty($country))) {
            if(!empty($addressArray)) {
                end($addressArray);
                $addressArray[key($addressArray)] .= ' ' . $listing->getZipCode();
            } else {
                $addressArray[] = $listing->getZipCode();
            }
        }

        if(!empty($country)) {
            $addressArray[] = $country;
        }

        return implode(', ', $addressArray);
    }

    /**
     * 01/04/2020
     * Mateus Cabana
     * Function return listing with id
     * @param $id
     * @return Listing|object|null
     */
    public function getListing($id)
    {
        return $this->doctrine->getRepository('ListingBundle:Listing')->find($id);
    }

    /**
     * @param $listingId
     * @param $listingField
     * @return array
     */
    public function getLinkedListings($listingId = null, $listingField = null)
    {
        if(empty($listingId) || empty($listingField)) {
            return [];
        }

        return $this->container->get('doctrine')->getRepository('ListingBundle:LinkedListings')->findBy([
            'sourceListing' => $listingId,
            'field'         => $listingField
        ]);
    }

    /**
     * @param null $limit
     * @param null $accountId
     * @return Listing[]
     */
    public function getOrderedListings($limit = null, $accountId = null)
    {
        return $this->container->get('doctrine')->getRepository('ListingBundle:Listing')->getOrderedListings($limit, $accountId);
    }

    /**
     * @param null $limit
     * @param null $accountId
     * @param null $term
     * @return Listing[]
     */
    public function getOrderedListingsByTerm($limit = null, $accountId = null, $term = null)
    {
        return $this->container->get('doctrine')->getRepository('ListingBundle:Listing')->getOrderedListingsByTerm($limit, $accountId, $term);
    }

    /**
     * @param $template
     * @param null $term
     * @param array $addedListings
     * @param null $accountId
     */
    public function buildListingContainerByTerm(&$template, $term = null, $addedListings = [], $accountId = null)
    {
        if(empty($term)) {
            $listings = $this->getOrderedListings(10, $accountId);
        } else {
            $listings = $this->getOrderedListingsByTerm(10, $accountId, $term);
        }

        if(!empty($listings)) {
            foreach ($listings as $listing) {
                if(!empty($addedListings)) {
                    if ($listing instanceof Result) {
                        $addedListing = in_array($listing->getId(), $addedListings);
                    } else {
                        $addedListing = in_array($listing['id'], $addedListings);
                    }
                } else {
                    $addedListing = false;
                }

                $template .= $this->container->get('templating')->render('@Listing/listingForm/linkedListing.html.twig', [
                    'listing'      => $listing,
                    'addedListing' => $addedListing
                ]);
            }
        }
    }

    /**
     * @param $term
     * @return Query\MatchAll
     */
    public function getListingFromElasticByTerm($term)
    {
        $searchEngine = $this->container->get('search.engine');

        $resultSet = null;

        $elasticaClient = $searchEngine->getElasticaClient();

        $indexName = $searchEngine->getElasticIndexName();

        if(!empty($elasticaClient)) {
            $elasticaIndex = $elasticaClient->getIndex($indexName);
            $listingType = $elasticaIndex->getType('listing');

            $qB = SearchEngine::getElasticaQueryBuilder();
            if($qB !== null) {
                $query = $qB->query();
                $analyzedQuery = $query->match()->setFieldQuery('title.analyzed', $term);

                $resultSet = $listingType->search($analyzedQuery)->getResults();
            }
        }

        return $resultSet;
    }

    /**
     * @param Listing $sourceListing
     * @param ListingTField $listingTField
     */
    public function clearLinkedListingsBySource(Listing $sourceListing, ListingTField $listingTField)
    {
        $em = $this->container->get('doctrine')->getManager();

        $linkedListings = $this->container->get('doctrine')->getRepository('ListingBundle:LinkedListings')->findBy([
            'sourceListing' => $sourceListing,
            'field'         => $listingTField
        ]);

        foreach($linkedListings as $linkedListing) {
            $em->remove($linkedListing);
        }

        $em->flush();
    }

    /**
     * Get price of listing. Returns null if informed renewal period is invalid
     * @param Listing $sourceListing
     * @param string $renewalPeriod
     * @param bool $applyDiscount
     * @return float|int|null
     * @throws Exception
     */
    public function getPrice(Listing $sourceListing, $renewalPeriod = '', $applyDiscount = true)
    {
        $returnValue = null;
        $logger = $this->container->get('logger');
        try {
            $listingTemplateFeatureConstantValue = constant('LISTINGTEMPLATE_FEATURE');
            if ($listingTemplateFeatureConstantValue === null) {
                $listingTemplateFeatureConstantValue = 'on';//Consider the default domain value for this
            }
            $customListingTemplateFeatureValue = constant('CUSTOM_LISTINGTEMPLATE_FEATURE');
            if ($customListingTemplateFeatureValue === null) {
                $customListingTemplateFeatureValue = 'on';//Consider the default domain value for this
            }

            /** @var ListingLevelService $listingLevelService */
            $listingLevelService = $this->container->get('listinglevel.service');
            $discountService = $this->container->get('discountcode.service');
            if ($sourceListing !== null && $listingLevelService !== null && $discountService !== null) {
                /** @var ListingRepository $listingRepository */
                $listingRepository = $this->doctrine->getRepository('ListingBundle:Listing');
                /** @var ListingCategoryRepository $listingCategoryRepository */
                $listingCategoryRepository = $this->doctrine->getRepository('ListingBundle:ListingCategory');
                /** @var ListingTemplateRepository $listingTemplateRepository */
                $listingTemplateRepository = $this->doctrine->getRepository('ListingBundle:ListingTemplate');
                /** @var EntityRepository $listingTemplateRepository */
                $discountCodeRepository = $this->doctrine->getRepository('WebBundle:DiscountCode');
                if ($listingRepository !== null &&
                    $listingCategoryRepository !== null &&
                    $listingTemplateRepository !== null &&
                    $discountCodeRepository !== null) {
                    /*
                     * Fix to normalize variable standard. It should be monthly or yearly, but some places are sending it as M or Y.
                     * Kept to allow usage on legacy code.
                     */
                    if ($renewalPeriod === 'M' || $renewalPeriod === 'monthly') {
                        $renewalPeriod = $listingLevelService::PRICE_PERIOD_MONTHLY;
                    } elseif ($renewalPeriod === 'Y' || $renewalPeriod === 'yearly') {
                        $renewalPeriod = $listingLevelService::PRICE_PERIOD_YEARLY;
                    }
                    $price = null;

                    /* Check if have price by package */
                    $listingLevel = $sourceListing->getLevelObj();

                    $listingLevelMonthlyPriceObj = $listingLevelService->getPrice($listingLevel, $listingLevelService::PRICE_PERIOD_MONTHLY);
                    $listingLevelYearlyPriceObj = $listingLevelService->getPrice($listingLevel, $listingLevelService::PRICE_PERIOD_YEARLY);
                    if ($listingLevelMonthlyPriceObj !== null && $listingLevelYearlyPriceObj !== null) {
                        $listingLevelMonthlyPrice = $listingLevelMonthlyPriceObj;
                        $listingLevelYearlyPrice = $listingLevelYearlyPriceObj;

                        /*
                         * Workaround for the scenario where the monthly price is 0 and the yearly price > 0, but the variable $renewal_period comes empty
                         * In this case, the system reads the monthly price and considers the item as a free item
                         */
                        if (empty($renewalPeriod) &&
                            $listingLevelMonthlyPrice <= 0 &&
                            $listingLevelYearlyPrice > 0) {
                            $renewalPeriod = $listingLevelService::PRICE_PERIOD_YEARLY;
                        }


                        $listingPackageId = $sourceListing->getPackageId();
                        if (!empty($listingPackageId)) {
                            $price = (float)$sourceListing->getPackagePrice();
                        } else {
                            $priceFromLevel = $listingLevelService->getPrice($listingLevel, $renewalPeriod);
                            if ($priceFromLevel !== null) {
                                $price = $priceFromLevel;
                            } else {
                                $price = (float)0;
                            }
                            unset($priceFromLevel);
                        }

                        $listingId = $sourceListing->getId();
                        $category_amount = 0;
                        if (!empty($listingId)) {
                            $persistedListing = $listingRepository->find($listingId);
                            if($persistedListing!==null) {
                                $persistedCategories = $persistedListing->getCategories();
                                if ($persistedCategories !== null) {
                                    $category_amount = $persistedCategories->count();
                                }
                                unset($persistedCategories);
                            }
                        } else {
                            $sourceCategories = $sourceListing->getCategories();
                            if ($sourceCategories !== null) {
                                $category_amount = $sourceCategories->count();
                            }
                            unset($sourceCategories);
                        }

                        $listingLevelFreeCategory = $listingLevel->getFreeCategory();

                        if (($category_amount > 0) && (($category_amount - $listingLevelFreeCategory) > 0)) {
                            $extra_category_amount = (float)($category_amount - $listingLevelFreeCategory);
                        } else {
                            $extra_category_amount = (float)0;
                        }

                        $listingLevelCategoryPrice = (float)$listingLevel->getCategoryPrice();

                        if ($extra_category_amount > 0) {
                            if ($renewalPeriod === $listingLevelService::PRICE_PERIOD_YEARLY && $listingLevelYearlyPrice !== null && $listingLevelMonthlyPrice !== null && $listingLevelYearlyPrice > 0 && $listingLevelMonthlyPrice > 0) {
                                $price += (($listingLevelCategoryPrice * $extra_category_amount) * ($listingLevelYearlyPrice / $listingLevelMonthlyPrice));
                            } else {
                                $price += ($listingLevelCategoryPrice * $extra_category_amount);
                            }
                        }

                        if ($listingTemplateFeatureConstantValue === 'on' && $customListingTemplateFeatureValue === 'on') {
                            $listingListingTemplateId = $sourceListing->getListingTemplateId();
                            if (!empty($listingListingTemplateId)) {
                                /** @var ListingTemplate $listingTemplate */
                                $listingTemplate = $listingTemplateRepository->find($listingListingTemplateId);
                                if ($listingTemplate !== null) {
                                    $listingTemplateStatus = $listingTemplate->getStatus();
                                    $listingTemplateTemplateFree = $listingTemplate->getTemplateFree();
                                    $listingTemplatePrice = (float)$listingTemplate->getPrice();

                                    if ($listingTemplateStatus === 'enabled'){
                                        if($listingTemplateTemplateFree === 'enabled') {
                                            $price = 0;//If status price is enabled, the listings that use the template will be free
                                        } else {
                                            if ($renewalPeriod === $listingLevelService::PRICE_PERIOD_YEARLY && $listingLevelYearlyPrice !== null && $listingLevelMonthlyPrice !== null && $listingLevelYearlyPrice > 0 && $listingLevelMonthlyPrice > 0) {
                                                $price += ($listingTemplatePrice * ($listingLevelYearlyPrice / $listingLevelMonthlyPrice));
                                            } else {
                                                $price += $listingTemplatePrice;
                                            }
                                        }
                                    }
                                    unset($listingTemplateStatus,
                                        $listingTemplateTemplateFree,
                                        $listingTemplatePrice);
                                }
                                unset($listingTemplate);
                            }
                            unset($listingListingTemplateId);
                        }

                        if($price>0) {
                            $listingDiscountId = $sourceListing->getDiscountId();
                            if (!empty($listingDiscountId) && $applyDiscount) {
                                /** @var DiscountCode $discountCode */
                                $discountCode = $discountCodeRepository->find($listingDiscountId);
                                if ($discountCode !== null) {
                                    if ($discountService->discountCodeIsValid($discountCode, $sourceListing, $discount_message, $discount_error)) {
                                        $discountCodeExpireDate = $discountCode->getExpireDate();
                                        if ($discountCodeExpireDate >= date('Y-m-d')) {
                                            $discountCodeType = $discountCode->getType();
                                            $discountCodeAmount = (float)$discountCode->getAmount();
                                            if ($discountCodeType === 'percentage') {
                                                $discountCodeAmount = ($discountCodeAmount <= 100) ? $discountCodeAmount : 100;
                                                $price *= (1 - $discountCodeAmount / 100);
                                            } elseif ($discountCodeType === 'monetary value') {
                                                $discountCodeAmount = ($discountCodeAmount <= $price) ? $discountCodeAmount : $price;
                                                $price -= $discountCodeAmount;
                                            }
                                            unset($discountCodeType,
                                                $discountCodeAmount);
                                        }
                                        unset($discountCodeExpireDate);
                                    }
                                }
                                unset($discountCode);
                            }
                            unset($listingDiscountId);
                        }
                        unset($listingLevelMonthlyPrice,
                            $listingLevelYearlyPrice,
                            $listingPackageId,
                            $listingId,
                            $category_amount,
                            $listingLevelFreeCategory,
                            $listingLevelCategoryPrice);
                    }
                    $returnValue = $price;
                    unset($price,
                        $listingLevel,
                        $listingLevelMonthlyPriceObj,
                        $listingLevelYearlyPriceObj);
                }
                unset($listingRepository,
                    $listingCategoryRepository,
                    $listingTemplateRepository,
                    $discountCodeRepository);
            }
            unset($listingTemplateFeatureConstantValue,
                $customListingTemplateFeatureValue,
                $listingLevelService,
                $discountService);
        } catch (Exception $e) {
            $logger->critical('Unexpected error on getPrice method of ListingService.php', ['exception' => $e]);
            throw $e;
        } finally {
            unset($logger,
                $listingTemplateFeatureConstantValue,
                $customListingTemplateFeatureValue,
                $listingLevelService,
                $discountService,
                $listingRepository,
                $listingCategoryRepository,
                $listingTemplateRepository,
                $discountCodeRepository,
                $price,
                $listingLevel,
                $listingLevelMonthlyPriceObj,
                $listingLevelYearlyPriceObj,
                $listingLevelMonthlyPrice,
                $listingLevelYearlyPrice,
                $listingPackageId,
                $priceFromLevel,
                $listingId,
                $category_amount,
                $persistedListing,
                $sourceCategories,
                $listingLevelFreeCategory,
                $listingLevelCategoryPrice,
                $listingListingTemplateId,
                $listingTemplate,
                $listingTemplateStatus,
                $listingTemplateTemplateFree,
                $listingTemplatePrice,
                $listingDiscountId,
                $discountCode,
                $discountCodeExpireDate,
                $discountCodeType,
                $discountCodeAmount
            );
        }
        return $returnValue;
    }

    /**
     * Check if listing has renewal date. Returns null if was problem with renewal period
     * @param Listing $sourceListing
     * @return bool|null
     * @throws Exception
     */
    public function hasRenewalDate(Listing $sourceListing)
    {
        $returnValue = null;
        $logger = $this->container->get('logger');
        try {
            $paymentFeatureConstantValue = constant('PAYMENT_FEATURE');
            if ($paymentFeatureConstantValue === null) {
                $paymentFeatureConstantValue = 'on';//Consider the default domain value for this
            }
            $creditcardpaymentFeatureConstantValue = constant('CREDITCARDPAYMENT_FEATURE');
            if ($creditcardpaymentFeatureConstantValue === null) {
                $creditcardpaymentFeatureConstantValue = 'on';//Consider the default domain value for this
            }
            $paymentInvoiceStatusConstantValue = constant('PAYMENT_INVOICE_STATUS');
            if ($paymentInvoiceStatusConstantValue === null) {
                $paymentInvoiceStatusConstantValue = 'on';//Consider the default domain value for this
            }
            $paymentManualStatusConstantValue = constant('PAYMENT_MANUAL_STATUS');
            if ($paymentManualStatusConstantValue === null) {
                $paymentManualStatusConstantValue = 'on';//Consider the default domain value for this
            }

            if ($paymentFeatureConstantValue !== 'on') {
                $returnValue = false;
            } elseif (($creditcardpaymentFeatureConstantValue !== 'on') && ($paymentInvoiceStatusConstantValue !== 'on') && ($paymentManualStatusConstantValue !== 'on')) {
                $returnValue = false;
            } else {
                $sourceListingMonthlyRenewalPrice = $this->getPrice($sourceListing, ListingLevelService::PRICE_PERIOD_MONTHLY);
                $sourceListingYearlyRenewalPrice = $this->getPrice($sourceListing, ListingLevelService::PRICE_PERIOD_YEARLY);
                if ($sourceListingMonthlyRenewalPrice !== null && $sourceListingYearlyRenewalPrice !== null) {
                    if ($sourceListingMonthlyRenewalPrice <= 0 && $sourceListingYearlyRenewalPrice <= 0) {
                        $returnValue = false;
                    } else {
                        $returnValue = true;
                    }
                }
                unset($sourceListingMonthlyRenewalPrice,
                    $sourceListingYearlyRenewalPrice);
            }
            unset($paymentFeatureConstantValue,
                $creditcardpaymentFeatureConstantValue,
                $paymentInvoiceStatusConstantValue,
                $paymentManualStatusConstantValue);
        } catch (Exception $e) {
            $logger->critical('Unexpected error on hasRenewalDate method of ListingService.php', ['exception' => $e]);
            throw $e;
        } finally {
            unset($logger,
                $paymentFeatureConstantValue,
                $creditcardpaymentFeatureConstantValue,
                $paymentInvoiceStatusConstantValue,
                $paymentManualStatusConstantValue,
                $sourceListingMonthlyRenewalPrice,
                $sourceListingYearlyRenewalPrice);
        }
        return $returnValue;
    }

    /**
     * Check if listing needs check-out. Returns null if was problem with renewal date check
     * @param Listing $sourceListing
     * @return bool|null
     * @throws Exception
     */
    public function needToCheckOut(Listing $sourceListing)
    {
        $returnValue = null;
        $logger = $this->container->get('logger');
        try {
            $sourceListingHasRenewalDate = $this->hasRenewalDate($sourceListing);
            if($sourceListingHasRenewalDate!==null) {
                if ($sourceListingHasRenewalDate) {
                    $sourceListingRenewaldate = $sourceListing->getRenewalDate();
                    $timestamp_renewaldate = $sourceListingRenewaldate->getTimestamp();

                    $today = new DateTime('today', $sourceListingRenewaldate->getTimezone());
                    $timestamp_today = $today->getTimestamp();

                    $sourceListingStatus = $sourceListing->getStatus();
                    if (($sourceListingStatus === 'E') || ($sourceListingRenewaldate === null) || ($timestamp_today > $timestamp_renewaldate)) {
                        $returnValue = true;
                    } else {
                        $returnValue = false;
                    }
                } else {
                    $returnValue = false;
                }
            }
        } catch (Exception $e) {
            $logger->critical('Unexpected error on hasRenewalDate method of ListingService.php', ['exception' => $e]);
            throw $e;
        } finally {
            unset($logger,
                $sourceListingHasRenewalDate,
                $today,
                $timestamp_today,
                $sourceListingRenewaldate,
                $timestamp_renewaldate,
                $sourceListingStatus);
        }
        return $returnValue;
    }

    /**
     * @param $item
     * @return Map
     */
    public function getDetailMap($item)
    {
        $map = new Map();
        $map->setMapOption('scrollwheel', false);
        $map->setStylesheetOptions([
            'width'  => '100%',
            'height' => '255px',
        ]);
        $domain = $this->container->get('multi_domain.information')->getId();
        $theme = lcfirst($this->container->get('theme.service')->getSelectedTheme()->getTitle());
        $defaultIconPath = '/assets/' . $theme . '/icons/listing.svg';
        $customIconPath = 'custom/domain_' . $domain . '/theme/' . $theme . '/icons/listing.svg';

        $mapZoom = ($item->getMapZoom() ? $item->getMapZoom() : 15);
        $map->setMapOption('zoom', $mapZoom);

        /* sets the item's location the center of the map */
        $map->setCenter(new Coordinate((float) $item->getLatitude(), (float) $item->getLongitude()));

        $marker = new Marker(new Coordinate((float) $item->getLatitude(), (float) $item->getLongitude(), true));

        /* mark item in map */
        $marker->setOptions([
            'clickable' => false,
            'flat'      => true,
        ]);

        if (file_exists($customIconPath)) {
            $iconPath = '/' . $customIconPath;
        } else {
            $iconPath = $defaultIconPath;
        }

        $marker->setIcon(new Icon($this->container->get('request')->getSchemeAndHttpHost() . '/' . $iconPath));

        $map->getOverlayManager()->addMarker($marker);

        return $map;
    }
}
