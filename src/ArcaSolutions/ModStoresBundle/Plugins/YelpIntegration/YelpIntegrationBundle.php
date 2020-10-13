<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\YelpIntegration;

use ArcaSolutions\CoreBundle\Inflector;
use ArcaSolutions\CoreBundle\Kernel\Kernel;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingTemplateTab;
use ArcaSolutions\ModStoresBundle\Kernel\Hooks;
use ArcaSolutions\ModStoresBundle\Plugins\AbstractPluginBundle as Bundle;
use ArcaSolutions\WysiwygBundle\Entity\ListingTemplateListingWidget;
use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
use DateTime;
use Exception;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;
use Elastica\Result;

class YelpIntegrationBundle extends Bundle
{
    private $devEnvironment = false;
    /**
     * Boots the Bundle.
     *
     * @throws Exception
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
                Hooks::Register('generalsettings_before_render_js', function (&$params = null) {
                    return $this->getGeneralSettingsBeforeRenderForm($params);
                });

            } else {

                /*
                 * Register front only bundle hooks
                 */
                Hooks::Register('detailextension_before_settabhascontent', function (&$params = null) {
                    return $this->getDetailExtensionBeforeSetTabHasContent($params);
                });
                Hooks::Register('search_before_add_globalvars', function (&$params = null) {
                    return $this->getSearchBeforeAddGlobalVars($params);
                });
                Hooks::Register('dailymaintenance_after_load_configurations', function (&$params = null) {
                    return $this->getDailyMaintenanceAfterLoadConfigurations($params);
                });
                Hooks::Register('listing_after_validate_itemdetail', function (&$params = null) {
                    return $this->getListingAfterValidateItemDetail($params);
                });
                Hooks::Register('listingsummary_before_extract_data', function (&$params = null) {
                    return $this->getListingSummaryBeforeExtractData($params);
                });
                Hooks::Register('listingsummary_before_render_thumbnail', function (&$params = null) {
                    return $this->getListingSummaryBeforeRenderThumbnail($params);
                });
                Hooks::Register('listingsummary_after_render_email', function (&$params = null) {
                    return $this->getListingSummaryAfterRenderEmail($params);
                });
                Hooks::Register('detaileditor-about_before_render_descriptionimage', function (&$params = null) {
                    return $this->getListingDetailBeforeRenderGallery($params);
                });
                Hooks::Register('detaileditor-photogallery_will_renderphotos', function (&$params = null) {
                    return $this->getDetailEditorPhotoGalleryWillRenderPhotos($params);
                });
                Hooks::Register('detaileditor-photogallery_before_renderphotos', function (&$params = null) {
                    return $this->getDetailEditorPhotoGalleryBeforeRenderPhotos($params);
                });
                Hooks::Register('listingdetail_before_render_location', function (&$params = null) {
                    return $this->getListingDetailBeforeRenderLocation($params);
                });
                Hooks::Register('listingsummary_before_render_location', function (&$params = null) {
                    return $this->getListingSummaryBeforeRenderLocation($params);
                });
                Hooks::Register('themeboxvertical_override_noimage', function (&$params = null) {
                    return $this->getThemeBoxVerticalOverrideNoImage($params);
                });
                Hooks::Register('themeboxverticalxs_override_noimage', function (&$params = null) {
                    return $this->getThemeBoxVerticalXSOverrideNoImage($params);
                });
                Hooks::Register('themeboxhorizontal_override_noimage', function (&$params = null) {
                    return $this->getThemeBoxHorizonalOverrideNoImage($params);
                });
                Hooks::Register('themeboxhorizontalxs_override_noimage', function (&$params = null) {
                    return $this->getThemeBoxHorizonalXSOverrideNoImage($params);
                });
                Hooks::Register('themeboxfeatured_override_noimage', function (&$params = null) {
                    return $this->getThemeBoxFeaturedOverrideNoImage($params);
                });
                Hooks::Register('themeboxfeaturedsmall_override_image', function (&$params = null) {
                    return $this->getThemeBoxFeaturedSmallOverrideNoImage($params);
                });
                Hooks::Register('detailextension_before_increaseoverviewcount', function (&$params = null) {
                    return $this->getDetailExtensionBeforeIncreaseOverviewCount($params);
                });
                Hooks::Register('detailextension_overwrite_hasreview', function (&$params = null) {
                    return $this->getDetailExtensionOverwriteHasReview($params);
                });
                Hooks::Register('listingservice_after_formathourswork', function (&$params = null) {
                    return $this->getListingServiceAfterFormatHoursWork($params);
                });
                Hooks::Register('detaileditor-header_validate_review', function (&$params = null) {
                    return $this->getDetailContentValidateReview($params);
                });
                Hooks::Register('detailcontent_after_renderreview', function (&$params = null) {
                    return $this->getDetailContentAfterRenderReview($params);
                });
                Hooks::Register('listing_before_add_globalvars', function (&$params = null) {
                    return $this->getListingBeforeAddGlobalVars($params);
                });
                Hooks::Register('detaileditor-header_overwrite_reviewbutton', function (&$params = null) {
                    return $this->getDetailContentOverwriteReviewButton($params);
                });
                Hooks::Register('summary_check_noimage', function (&$params = null) {
                    return $this->getSummaryChechNoImage($params);
                });
                Hooks::Register('summary_overwrite_phone', function (&$params = null) {
                    return $this->getSummaryOverwritePhone($params);
                });
                Hooks::Register('detailcontent_overwrite_allreviewslink', function (&$params = null) {
                    return $this->getDetailContentOverwriteAllReviewsLink($params);
                });

                Hooks::Register('detailextension_before_settabhascontent', function (&$params = null) {
                    return $this->getDetailExtensionBeforeSetTabHasContent($params);
                });

                Hooks::Register('blocks-extension_before_return_rendered-card-type-block', function (&$params = null) {
                    return $this->getBlocksExtensionBeforeReturnRenderedCardTypeBlock($params);
                });

                Hooks::Register('views-listing-blocks-vertical-cards_overwrite_cardtagpicture', function (&$params = null) {
                    return $this->getViewsListingBlocksVerticalCardsOverwriteCardTagPicture($params);
                });
                Hooks::Register('views-listing-blocks-vertical-cards-plus-horizontal_overwrite_cardtagpicture', function (&$params = null) {
                    return $this->getViewsListingBlocksVerticalCardsPlusHorizontalOverwriteCardTagPicture($params);
                });
                Hooks::Register('views-listing-blocks-three-vertical-cards_overwrite_cardtagpicture', function (&$params = null) {
                    return $this->getViewsListingBlocksThreeVerticalCardsOverwriteCardTagPicture($params);
                });
                Hooks::Register('views-listing-blocks-one-horizontal-card_overwrite_cardtagpicture', function (&$params = null) {
                    return $this->getViewsListingBlocksOneHorizontalCardOverwriteCardTagPicture($params);
                });
                Hooks::Register('views-listing-blocks-list-of-horizontal-cards_overwrite_cardtagpicture', function (&$params = null) {
                    return $this->getViewsListingBlocksListOfHorizontalCardsOverwriteCardTagPicture($params);
                });
                Hooks::Register('views-listing-blocks-horizontal-cards_overwrite_cardtagpicture', function (&$params = null) {
                    return $this->getViewsListingBlocksHorizontalCardsOverwriteCardTagPicture($params);
                });
                Hooks::Register('views-listing-blocks-centralized-highlighted-card_overwrite_cardtagpicture', function (&$params = null) {
                    return $this->getViewsListingBlocksCentralizedHighlightedCardOverwriteCardTagPicture($params);
                });
                Hooks::Register('views-listing-blocks-2-columns-horizontal-cards_overwrite_cardtagpicture', function (&$params = null) {
                    return $this->getViewsListingBlocks2ColumnsHorizontalCardsOverwriteCardTagPicture($params);
                });

                Hooks::Register('views-listing-blocks-vertical-cards_overwrite_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksVerticalCardsOverwriteCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-vertical-cards-plus-horizontal_overwrite_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksVerticalCardsPlusHorizontalOverwriteCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-three-vertical-cards_overwrite_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksThreeVerticalCardsOverwriteCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-one-horizontal-card_overwrite_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksOneHorizontalCardOverwriteCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-list-of-horizontal-cards_overwrite_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksListOfHorizontalCardsOverwriteCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-horizontal-cards_overwrite_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksHorizontalCardsOverwriteCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-centralized-highlighted-card_overwrite_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksCentralizedHighlightedCardOverwriteCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-2-columns-horizontal-cards_overwrite_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocks2ColumnsHorizontalCardsOverwriteCardReviews($params);
                });

                Hooks::Register('views-listing-blocks-vertical-cards_willrender_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksVerticalCardsWillRenderCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-vertical-cards-plus-horizontal_willrender_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksVerticalCardsPlusHorizontalWillRenderCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-three-vertical-cards_willrender_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksThreeVerticalCardsWillRenderCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-one-horizontal-card_willrender_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksOneHorizontalCardWillRenderCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-list-of-horizontal-cards_willrender_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksListOfHorizontalCardsWillRenderCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-horizontal-cards_willrender_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksHorizontalCardsWillRenderCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-centralized-highlighted-card_willrender_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocksCentralizedHighlightedCardWillRenderCardReviews($params);
                });
                Hooks::Register('views-listing-blocks-2-columns-horizontal-cards_willrender_reviews', function (&$params = null) {
                    return $this->getViewsListingBlocks2ColumnsHorizontalCardsWillRenderCardReviews($params);
                });
                Hooks::Register('views-blocks-utility-detail-reviewstarsmacro_willrender_reviews', function (&$params = null) {
                    return $this->getViewsBlocksUtilityDetailWillRenderReviews($params);
                });
                Hooks::Register('views-blocks-utility-detail-reviewstarsmacro_before_reviews', function (&$params = null) {
                    return $this->getViewsBlocksUtilityDetailBeforeReviews($params);
                });
                Hooks::Register('views-blocks-utility-detail-reviewstarsmacro_ovewrite_reviewcount', function (&$params = null) {
                    return $this->getViewsBlocksUtilityDetailOverwriteReviewCount($params);
                });
                Hooks::Register('views-blocks-utility-detail-detailscontactmacro_before_contact', function (&$params = null) {
                    return $this->getViewsBlocksUtilityDetailDetailsContactMacroBeforeContact($params);
                });
            }
            parent::boot();
        } catch (Exception $e) {
            if (!empty($logger)) {
                $logger->critical('Unexpected error on boot method of YelpIntegrationBundle.php', ['exception' => $e]);
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

    /**
     * @param null $params
     */
    private function getGeneralSettingsAfterSave(&$params = null)
    {
        if(!empty($params['http_post_array']) && is_array($params['http_post_array']) && array_key_exists('save_plugin',$params['http_post_array'])) {
            if (!empty($_POST['yelp_appSecret'])) {
                $yelp = $this->container->get('api.yelp');
                $yelp->setYelpKey($_POST['yelp_appSecret']);

                $keyword = ['term' => 'Yelp'];

                $location = ['location' => '140 New Montgomery'];

                try {
                    $request = $yelp->search(array_merge($keyword, $location));

                    $this->container->get('settings')->setSetting('yelpAppSecret', $_POST['yelp_appSecret']);

                    $params['success'] = true;
                } catch (Exception $e) {
                    $params['error'] = true;
                }
            } else {
                $this->container->get('settings')->setSetting('yelpAppSecret', '');
                $params['_return'] = false;
            }
        }
    }

    /**
     * @param null $params
     */
    private function getGeneralSettingsAfterRenderForm(&$params = null)
    {
        echo $this->container->get('templating')->render('YelpIntegrationBundle::sitemgr-form-yelp.html.twig', [
            'yelpAppSecret' => $this->container->get('settings')->getDomainSetting('yelpAppSecret', true),
        ]);
    }

    /**
     * @param null $params
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     * @throws Exception
     */
    public function getGeneralSettingsBeforeRenderForm(&$params = null)
    {
        try {
            echo $this->container->get('twig')->render('YelpIntegrationBundle::js/sitemgr-form-yelp.html.twig');
        } catch (Twig_Error_Loader $e) {
            throw $e;
        } catch (Twig_Error_Runtime $e) {
            throw $e;
        } catch (Twig_Error_Syntax $e) {
            throw $e;
        } catch (Exception $e){
            throw $e;
        }
    }

    /**
     * @param $yelpBusinesses
     * @return array
     */
    private function sanitizeSearchResponse($yelpBusinesses){
        $yelpBusinessessToSaveOnCache = [];
        foreach ($yelpBusinesses as $yelpBusiness){
            if(array_key_exists('distance',$yelpBusiness)){
                $distanceString = $yelpBusiness['distance'];
                if ($distanceString!=="" && is_numeric($distanceString))
                {
                    $distance = $distanceString + 0; //Force conversion to int or float
                    if($distance<100){
                        $yelpBusinessessToSaveOnCache[] = $yelpBusiness;
                    }
                    unset($distance);
                }
                unset($distanceString);
            }
        }
        return $yelpBusinessessToSaveOnCache;
    }

    /**
     * @param null $params
     */
    private function getSearchBeforeAddGlobalVars(&$params = null)
    {
        $manager = $this->container->get('doctrine')->getManager();
        $yelp = $this->container->get('api.yelp');

        $businesses = [];

        if ($yelp->hasPrivateKey()) {

            foreach ($params['pagination'] as $result) {

                if ($result->getType() === 'listing') {

                    $item = $result->getData();

                    if (isset($item['scriptedFieldData'][0])) {
                        $item = $item['scriptedFieldData'][0];
                    }

                    $attributes = $this->container->get('helper.yelp')->retrieveSearchParameters($item);

                    if ($cachedSearch = $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->findOneBy(['searchCriteria' => json_encode($attributes)])) {
                        $businesses[$item['friendlyUrl']] = $cachedSearch->getResponse();
                    } else {

                        try {

                            $yelp->setHttpClientVerify($this->container->get('request_stack')->getCurrentRequest()->isSecure());
                            if ($yelpRequest = $yelp->search($attributes)) {
                                $yelpRequest['businesses'] = $this->sanitizeSearchResponse($yelpRequest['businesses']);
                                $businesses[$item['friendlyUrl']] = $yelpRequest['businesses'];

                                $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->save(json_encode($attributes),
                                    $yelpRequest['businesses'], $manager);
                            }

                        } catch (Exception $e) {
                            $logger = $this->container->get('logger');
                            $logger->critical('Yelp exception: '.$e);
                            $params['_return'] = false;
                        }

                    }
                }
            }
        }

        $this->container->get('modstore.storage.service')->store('businessesMapping', $businesses);
    }

    /**
     * @param null $params
     */
    private function getDailyMaintenanceAfterLoadConfigurations(&$params = null)
    {
        $params['messageLog'] = 'Delete YelpCache table';

        $connection = $this->container->get('doctrine.dbal.domain_connection');
        $paramsSystem = $connection->getParams();

        $paramsSystem['dbname'] = $params['domainDBName'];
        $connection->__construct(
            $paramsSystem,
            $connection->getDriver(),
            $connection->getConfiguration(),
            $connection->getEventManager()
        );

        $connection->connect();

        $statement = $connection->prepare('DELETE FROM YelpCache');
        $statement->execute();
    }

    /**
     * @param null $params
     */
    private function getListingAfterValidateItemDetail(&$params = null)
    {
        try {
            $this->container->get('yelp.service')->storeYelpBusiness($params['item']);
        } catch(Exception $e) {
            $params['_return'] = false;
        }
    }

    /**
     * @param null $params
     */
    private function getListingSummaryBeforeExtractData(&$params = null)
    {
        $data = $params['item']->getData();
        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
        $helper = $this->container->get('helper.yelp');

        isset($data['scriptedFieldData'][0]) and $data = $data['scriptedFieldData'][0];

        foreach ($businessMapping as $friendlyUrl => $businesses) {

            if ($friendlyUrl == $data['friendlyUrl']) {

                foreach ($businesses as $business) {
                    if (Inflector::friendly_title($business['name'], '-',
                            true) === Inflector::friendly_title($helper->titleHandler($data['title'], true), '-',
                            true)) {
                        $data['yelpUrl'] = $business['url'];
                        $data['yelpRating'] = $business['rating'];
                        $data['yelpImageUrl'] = $business['image_url'];
                        $data['yelpLocation'] = $business['location'];
                        $data['yelpPhone'] = $business['display_phone'];
                        unset($businessMapping[$friendlyUrl]);
                        break 2;
                    }
                }
            }
        }

        $this->container->get('modstore.storage.service')->store('yelpData',
            isset($data['scriptedFieldData']) ? reset($data['scriptedFieldData']) : $data);

        $params['_return'] = $params['item'];
    }

    /**
     * @param null $params
     */
    private function getListingSummaryBeforeRenderThumbnail(&$params = null)
    {
        $helper = $this->container->get('helper.yelp');

        if (empty($params['flags'])
            && $viewYelp = $this->container->get('modstore.storage.service')->retrieve('yelpData')
        ) {
            echo $this->container->get('templating')->render('YelpIntegrationBundle::yelp-summary-image.html.twig', [
                'yelpData'      => $viewYelp,
                'itemLevelInfo' => $params['itemLevelInfo'],
                'detailURL'     => $params['detailURL'],
                'title'         => $helper->titleHandler($params['data']['title']),
            ]);
        } else {
            $params['_return'] = false;
        }
    }

    /**
     * @param null $params
     */
    private function getListingSummaryAfterRenderEmail(&$params = null)
    {
        if (!empty($params['item']) && empty($params['item']->getData()['reviewCount']) && $yelpData = $this->container->get('modstore.storage.service')->retrieve('yelpData')) {
            echo $this->container->get('templating')->render('YelpIntegrationBundle::yelp-summary-rating.html.twig', [
                'yelpData'      => $yelpData,
                'itemLevelInfo' => $params['itemLevelInfo'],
                'item'          => $params['item']
            ]);
        } else {
            $params['_return'] = false;
        }
    }

    /**
     * @param null $params
     */
    private function getListingDetailBeforeRenderGallery(&$params = null)
    {
        if (empty($params['gallery'])
            && $params['level']->imageCount > 0
            && $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness')
        ) {
            echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-detail-image.html.twig', [
                'yelpBusiness' => $yelpBusiness,
                'item'         => $params['item'],
            ]);
        }
    }

    /**
     * @param null $params
     */
    private function getDetailEditorPhotoGalleryWillRenderPhotos(&$params = null)
    {
        $willRender = false;
        if ($params['level']->imageCount > 0
            && $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness')
        ) {
            $willRender = !empty($yelpBusiness['photos']);
        }
        $params['_return'] = $willRender;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getDetailExtensionBeforeSetTabHasContent(&$params = null)
    {
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $hasContentRef = &$params['has_content'];
                /**
                 * @var ListingTemplateTab $tab
                 */
                $tab = $params['tab'];
                $listing = $params['listing'];
                $listingLevel = $params['listing_level_features'];
                $tabSectionWidgets = $params['tab_section_widgets'];
                if($hasContentRef!==null && !empty($listing) && !empty($listingLevel) && !empty($tab) && !empty($tabSectionWidgets)) {
                    $tabHasGallery = false;
                    foreach ($tabSectionWidgets as $sectionWidgets) {
                        /** @var ListingTemplateListingWidget $listingTemplateListingWidget */
                        foreach ($sectionWidgets as $listingTemplateListingWidget) {
                            /**
                             * @var ListingWidget $listingWidget
                             */
                            $listingWidget = $listingTemplateListingWidget->getListingWidget();
                            if(!empty($listingWidget) && $listingWidget->getTitle() === ListingWidget::PHOTO_GALLERY){
                                $tabHasGallery = true;
                            }
                        }
                    }
                    if($tabHasGallery) {
                        $willRender = false;
                        if ($listingLevel->imageCount > 0 && $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness')) {
                            $willRender = !empty($yelpBusiness['photos']);
                        }
                        $hasContentRef = ($hasContentRef || $willRender);
                    }
                }
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getDetailExtensionBeforeSetTabHasContent method of YelpIntegrationBundle.php', ['exception' => $e]);
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

    /**
     * @param null $params
     */
    private function getBlocksExtensionBeforeReturnRenderedCardTypeBlock(&$params = null)
    {
        /*
            'twigName' => &$cardTypeBlockTwigName,
            'items'           => &$items,
            'itemsPerRow'     => &$itemsPerRow,
            'banner'          => &$banner,
            'content'         => &$content,
            'widgetLink'      => &$widgetLink,
            'module'          => &$contentModule,
            'cardType'        => &$contentCardType
         */
        $manager = $this->container->get('doctrine')->getManager();
        $yelp = $this->container->get('api.yelp');

        $businesses = [];

        if ($yelp->hasPrivateKey()) {
            /** @var Result[] $elasticaResults */
            $elasticaResults = $params['items'];
            foreach ($elasticaResults as $elasticaResult) {
                if ($elasticaResult->getType() === 'listing') {
                    $item = $elasticaResult->getData();
                    if (isset($item['scriptedFieldData'][0])) {
                        $item = $item['scriptedFieldData'][0];
                    }
                    $attributes = $this->container->get('helper.yelp')->retrieveSearchParameters($item);
                    if ($cachedSearch = $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->findOneBy(['searchCriteria' => json_encode($attributes)])) {
                        $businesses[$item['friendlyUrl']] = $cachedSearch->getResponse();
                    } else {
                        try {
                            $yelp->setHttpClientVerify($this->container->get('request_stack')->getCurrentRequest()->isSecure());
                            if ($yelpRequest = $yelp->search($attributes)) {
                                $yelpRequest['businesses'] = $this->sanitizeSearchResponse($yelpRequest['businesses']);
                                $businesses[$item['friendlyUrl']] = $yelpRequest['businesses'];
                                $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->save(json_encode($attributes), $yelpRequest['businesses'], $manager);
                            }
                        } catch (Exception $e) {
                            $logger = $this->container->get('logger');
                            $logger->critical('Yelp exception: '.$e);
                            $params['_return'] = false;
                        }
                    }
                }
            }
        }
        $this->container->get('modstore.storage.service')->store('businessesMapping', $businesses);
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksVerticalCardsOverwriteCardTagPicture(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (!empty($yelpBusiness) && !empty($yelpBusiness['image_url'])) {
                            $data = [
                                'yelpImagePath' => $yelpBusiness['image_url'],
                                'altText' => $itemRawData['title']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-tagpicture-by-img.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksVerticalCardsOverwriteCardTagPicture method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksVerticalCardsPlusHorizontalOverwriteCardTagPicture(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (!empty($yelpBusiness) && !empty($yelpBusiness['image_url'])) {
                            $data = [
                                'yelpImagePath' => $yelpBusiness['image_url'],
                                'altText' => $itemRawData['title']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-tagpicture-by-img.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksVerticalCardsPlusHorizontalOverwriteCardTagPicture method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksThreeVerticalCardsOverwriteCardTagPicture(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (!empty($yelpBusiness) && !empty($yelpBusiness['image_url'])) {
                            $data = [
                                'yelpImagePath' => $yelpBusiness['image_url'],
                                'altText' => $itemRawData['title']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-tagpicture-by-img.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksThreeVerticalCardsOverwriteCardTagPicture method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksOneHorizontalCardOverwriteCardTagPicture(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (!empty($yelpBusiness) && !empty($yelpBusiness['image_url'])) {
                            $data = [
                                'yelpImagePath' => $yelpBusiness['image_url'],
                                'altText' => $itemRawData['title']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-tagpicture-by-img.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksOneHorizontalCardOverwriteCardTagPicture method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksListOfHorizontalCardsOverwriteCardTagPicture(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (!empty($yelpBusiness) && !empty($yelpBusiness['image_url'])) {
                            $data = [
                                'yelpImagePath' => $yelpBusiness['image_url'],
                                'altText' => $itemRawData['title']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-tagpicture-by-img.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksListOfHorizontalCardsOverwriteCardTagPicture method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksHorizontalCardsOverwriteCardTagPicture(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (!empty($yelpBusiness) && !empty($yelpBusiness['image_url'])) {
                            $data = [
                                'yelpImagePath' => $yelpBusiness['image_url'],
                                'altText' => $itemRawData['title']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-tagpicture-by-img.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksHorizontalCardsOverwriteCardTagPicture method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksCentralizedHighlightedCardOverwriteCardTagPicture(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                $itemIdToApplyBackground = $params['apply_background_on_item_id'];
                if (!empty($item) && !empty($itemData) && !empty($itemIdToApplyBackground)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (!empty($yelpBusiness) && !empty($yelpBusiness['image_url'])) {
                            $data = [
                                'divId' => $itemIdToApplyBackground,
                                'yelpImagePath' => $yelpBusiness['image_url']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-tagpicture-by-div-background.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksCentralizedHighlightedCardOverwriteCardTagPicture method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocks2ColumnsHorizontalCardsOverwriteCardTagPicture(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (!empty($yelpBusiness) && !empty($yelpBusiness['image_url'])) {
                            $data = [
                                'yelpImagePath' => $yelpBusiness['image_url'],
                                'altText' => $itemRawData['title']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-tagpicture-by-img.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocks2ColumnsHorizontalCardsOverwriteCardTagPicture method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksVerticalCardsOverwriteCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }

                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $data = [
                                'yelpUrl' => $yelpBusiness['url'],
                                'yelpRating' => $yelpBusiness['rating'],
                                'yelpReviewCount' => $yelpBusiness['review_count']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-reviews.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksVerticalCardsOverwriteCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksVerticalCardsPlusHorizontalOverwriteCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $data = [
                                'yelpUrl' => $yelpBusiness['url'],
                                'yelpRating' => $yelpBusiness['rating'],
                                'yelpReviewCount' => $yelpBusiness['review_count']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-reviews.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksVerticalCardsPlusHorizontalOverwriteCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksThreeVerticalCardsOverwriteCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $data = [
                                'yelpUrl' => $yelpBusiness['url'],
                                'yelpRating' => $yelpBusiness['rating'],
                                'yelpReviewCount' => $yelpBusiness['review_count']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-reviews.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksThreeVerticalCardsOverwriteCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksOneHorizontalCardOverwriteCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $data = [
                                'yelpUrl' => $yelpBusiness['url'],
                                'yelpRating' => $yelpBusiness['rating'],
                                'yelpReviewCount' => $yelpBusiness['review_count']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-reviews.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksOneHorizontalCardOverwriteCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksListOfHorizontalCardsOverwriteCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $data = [
                                'yelpUrl' => $yelpBusiness['url'],
                                'yelpRating' => $yelpBusiness['rating'],
                                'yelpReviewCount' => $yelpBusiness['review_count']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-reviews.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksListOfHorizontalCardsOverwriteCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksHorizontalCardsOverwriteCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $data = [
                                'yelpUrl' => $yelpBusiness['url'],
                                'yelpRating' => $yelpBusiness['rating'],
                                'yelpReviewCount' => $yelpBusiness['review_count']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-reviews.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksHorizontalCardsOverwriteCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksCentralizedHighlightedCardOverwriteCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $data = [
                                'yelpUrl' => $yelpBusiness['url'],
                                'yelpRating' => $yelpBusiness['rating'],
                                'yelpReviewCount' => $yelpBusiness['review_count']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-reviews.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksCentralizedHighlightedCardOverwriteCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocks2ColumnsHorizontalCardsOverwriteCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $data = [
                                'yelpUrl' => $yelpBusiness['url'],
                                'yelpRating' => $yelpBusiness['rating'],
                                'yelpReviewCount' => $yelpBusiness['review_count']
                            ];
                            try {
                                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-listing-cards-overwrite-reviews.html.twig', ['data' => $data]);
                                $returnValue = true;
                            } catch (Twig_Error_Loader $e) {
                                throw $e;
                            } catch (Twig_Error_Runtime $e) {
                                throw $e;
                            } catch (Twig_Error_Syntax $e) {
                                throw $e;
                            } catch (Exception $e) {
                                throw $e;
                            }
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocks2ColumnsHorizontalCardsOverwriteCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }
    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksVerticalCardsWillRenderCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $returnValue = true;
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksVerticalCardsWillRenderCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksVerticalCardsPlusHorizontalWillRenderCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $returnValue = true;
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksVerticalCardsPlusHorizontalWillRenderCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksThreeVerticalCardsWillRenderCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $returnValue = true;
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksThreeVerticalCardsWillRenderCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksOneHorizontalCardWillRenderCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $returnValue = true;
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksOneHorizontalCardWillRenderCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksListOfHorizontalCardsWillRenderCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $returnValue = true;
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksListOfHorizontalCardsWillRenderCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksHorizontalCardsWillRenderCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $returnValue = true;
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksHorizontalCardsWillRenderCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocksCentralizedHighlightedCardWillRenderCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $returnValue = true;
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocksCentralizedHighlightedCardWillRenderCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsBlocksUtilityDetailWillRenderReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $listingLevelFeatures = $params['level'];
                $paginatedReviews = $params['paginated_reviews'];
                if (!empty($item) && !empty($listingLevelFeatures) && $item instanceof Listing) {
                    $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                    if (!empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                        $returnValue = true;
                    }
                }
                unset($item, $level);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsBlocksUtilityDetailWillRenderReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsBlocksUtilityDetailBeforeReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $listingLevelFeatures = $params['level'];
                $paginatedReviews = $params['paginated_reviews'];
                if (!empty($item) && !empty($listingLevelFeatures) && $item instanceof Listing) {
                    $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                    if (!empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                        $data = [
                            'yelpUrl' => $yelpBusiness['url'],
                            'yelpRating' => $yelpBusiness['rating'],
                            'yelpReviewCount' => $yelpBusiness['review_count']
                        ];
                        try {
                            echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-detail-reviewstarsmacro-before-reviews.html.twig', ['data' => $data]);
                            $returnValue = true;
                        } catch (Twig_Error_Loader $e) {
                            throw $e;
                        } catch (Twig_Error_Runtime $e) {
                            throw $e;
                        } catch (Twig_Error_Syntax $e) {
                            throw $e;
                        } catch (Exception $e) {
                            throw $e;
                        }
                        unset($edirectoryTitleForReviews);
                    }
                    unset($yelpBusiness);
                }
                unset($item, $listingLevelFeatures, $paginatedReviews, $hasReviewButton);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsBlocksUtilityDetailOverwriteReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsBlocksUtilityDetailOverwriteReviewCount(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $listingLevelFeatures = $params['level'];
                $paginatedReviews = $params['paginated_reviews'];
                if (!empty($item) && !empty($listingLevelFeatures) && $item instanceof Listing) {
                    $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                    if (!empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                        $edirectoryTitleForReviews = 'non-yelp';
                        if($this->container->has('multi_domain.information')) {
                            $multiDomainInformationService = $this->container->get('multi_domain.information');
                            if(!empty($multiDomainInformationService)) {
                                $titleFromMultiDomainInformationService = $multiDomainInformationService->getTitle();
                                if(!empty($titleFromMultiDomainInformationService))
                                {
                                    $edirectoryTitleForReviews = $titleFromMultiDomainInformationService;
                                }
                                unset($titleFromMultiDomainInformationService);
                            }
                        }

                        $data = [
                            'reviewsPaginated' => $paginatedReviews,
                            'listingId' => $item->getId(),
                            'edirectoryTitleForReviews' => $edirectoryTitleForReviews,
                            'yelpUrl' => $yelpBusiness['url'],
                            'yelpRating' => $yelpBusiness['rating'],
                            'yelpReviewCount' => $yelpBusiness['review_count']
                        ];
                        try {
                            echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-detail-reviewstarsmacro-overwrite-reviewcount.html.twig', ['data' => $data]);
                            $returnValue = true;
                        } catch (Twig_Error_Loader $e) {
                            throw $e;
                        } catch (Twig_Error_Runtime $e) {
                            throw $e;
                        } catch (Twig_Error_Syntax $e) {
                            throw $e;
                        } catch (Exception $e) {
                            throw $e;
                        }
                        unset($edirectoryTitleForReviews);
                    }
                    unset($yelpBusiness);
                }
                unset($item, $listingLevelFeatures, $paginatedReviews, $hasReviewButton);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsBlocksUtilityDetailOverwriteReviewCount method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsBlocksUtilityDetailDetailsContactMacroBeforeContact(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $listingLevelFeatures = $params['level'];
                if (!empty($item) && !empty($listingLevelFeatures) && $item instanceof Listing) {
                    $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                    if (!empty($yelpBusiness) && !empty($yelpBusiness['url'])) {
                        $itemAddress = $this->container->get('listing.service')->getAddress($item);
                        $yelpAddress = '';
                        if(!empty($yelpBusiness['location']) && !empty($yelpBusiness['location']['display_address']) && is_array($yelpBusiness['location']['display_address'])){
                            $firstDisplayAddressPart = true;
                            foreach($yelpBusiness['location']['display_address'] as $yelpDisplayAddressPart){
                                $yelpAddress .= ((!$firstDisplayAddressPart)?', ':'').$yelpDisplayAddressPart;
                                if($firstDisplayAddressPart){
                                    $firstDisplayAddressPart = false;
                                }
                            }
                            unset($firstDisplayAddressPart);
                        }

                        $data = [
                            'levelHasPhone' => $listingLevelFeatures->hasPhone,
                            'phone' => $item->getPhone(),
                            'address' => $itemAddress,
                            'yelpPhone' => !empty($yelpBusiness['phone'])?$yelpBusiness['phone']:'',
                            'yelpDisplayPhone' => !empty($yelpBusiness['phone'])?(!empty($yelpBusiness['display_phone'])?$yelpBusiness['display_phone']:$yelpBusiness['phone']):'',
                            'yelpAddress' => $yelpAddress,
                            'yelpUrl' => $yelpBusiness['url']
                        ];
                        try {
                            echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-detail-detailscontactmacro-before-contact.html.twig', ['data' => $data]);
                            $returnValue = true;
                        } catch (Twig_Error_Loader $e) {
                            throw $e;
                        } catch (Twig_Error_Runtime $e) {
                            throw $e;
                        } catch (Twig_Error_Syntax $e) {
                            throw $e;
                        } catch (Exception $e) {
                            throw $e;
                        }
                    }
                    unset($yelpBusiness);
                }
                unset($item, $listingLevelFeatures);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsBlocksUtilityDetailDetailsContactMacroBeforeContact method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    /**
     * @param null $params
     * @throws Exception
     */
    private function getViewsListingBlocks2ColumnsHorizontalCardsWillRenderCardReviews(&$params = null)
    {
        $returnValue = false;
        if (!empty($params) && !empty($this->container)) {
            $logger = $this->container->get('logger');
            $notLoggedCriticalException = null;
            try {
                $item = $params['item'];
                $itemData = $params['item_data'];
                if (!empty($item) && !empty($itemData)) {
                    $itemRawData = $item->getData();
                    if(!empty($itemRawData)) {
                        $businessMapping = $this->container->get('modstore.storage.service')->retrieve('businessesMapping');
                        $helper = $this->container->get('helper.yelp');

                        isset($itemRawData['scriptedFieldData'][0]) and $itemRawData = $itemRawData['scriptedFieldData'][0];

                        $yelpBusiness = null;
                        $yelpBusinessFromStorage = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                        if (Inflector::friendly_title($yelpBusinessFromStorage['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                            $yelpBusiness = $yelpBusinessFromStorage;
                        }
                        unset($yelpBusinessFromStorage);
                        if (empty($yelpBusiness)) {
                            foreach ($businessMapping as $friendlyUrl => $businesses) {
                                if ($friendlyUrl == $itemRawData['friendlyUrl']) {
                                    foreach ($businesses as $business) {
                                        if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($helper->titleHandler($itemRawData['title'], true), '-', true)) {
                                            $yelpBusiness = $business;
                                            break;
                                        }
                                    }
                                    if (!empty($yelpBusiness)) {
                                        $this->container->get('modstore.storage.service')->store('yelpBusiness', $yelpBusiness);
                                        break;
                                    }
                                }
                            }
                        }
                        if (empty($itemRawData['reviewCount']) && !empty($yelpBusiness) && !empty($yelpBusiness['review_count'])) {
                            $returnValue = true;
                        }
                    }
                }
                unset($item, $itemData);
            } catch (Exception $e) {
                if (!empty($logger)) {
                    $logger->critical('Unexpected error on getViewsListingBlocks2ColumnsHorizontalCardsWillRenderCardReviews method of YelpIntegrationBundle.php', ['exception' => $e]);
                } else {
                    $notLoggedCriticalException = $e;
                }
            } finally {
                unset($logger);
                if (!empty($notLoggedCriticalException)) {
                    $params['_return'] = $returnValue;
                    throw $notLoggedCriticalException;
                }
            }
        }
        $params['_return'] = $returnValue;
    }

    private function getDetailEditorPhotoGalleryBeforeRenderPhotos(&$params = null)
    {
        if ($params['level']->imageCount > 0
            && $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness')
        ) {
            if(!empty($yelpBusiness['photos'])) {
                echo $this->container->get('twig')->render('YelpIntegrationBundle::yelp-detail-gallery-photos.html.twig', [
                    'yelpBusiness' => $yelpBusiness,
                    'item' => $params['item'],
                ]);
            }
        }
    }

    private function getListingDetailBeforeRenderLocation(&$params = null)
    {
        if (empty($params['item']->getLocations())
            && empty($params['item']->getAddress())
            && $params['level']->hasLocationReference
            && $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness')
        ) {
            echo $this->container->get('templating')->render('YelpIntegrationBundle::yelp-detail-address.html.twig', [
                'yelpBusiness' => $yelpBusiness,
            ]);
        }
    }

    private function getListingSummaryBeforeRenderLocation(&$params = null)
    {
        if (empty($params['data']['address']['street'])
            && empty($params['itemLocations'])
            && $yelpData = $this->container->get('modstore.storage.service')->retrieve('yelpData')
        ) {
            echo $this->container->get('templating')->render('YelpIntegrationBundle::yelp-summary-location.html.twig', [
                'yelpData' => $yelpData,
                'data'     => $params['data'],
            ]);
        } else {
            $params['_return'] = false;
        }
    }

    private function getThemeBoxVerticalOverrideNoImage(&$params)
    {
        $yelp = $this->container->get('api.yelp');
        $helper = $this->container->get('helper.yelp');

        $businesses = [];
        $yelpImage = null;

        if (!$yelp->hasPrivateKey() || $params['item']->getType() !== 'listing') {
            $params['_return'] = false;

            return;
        }

        $item = $params['item']->getData();

        if (isset($item['scriptedFieldData'][0])) {
            $item = $item['scriptedFieldData'][0];
        }

        $attributes = $this->container->get('helper.yelp')->retrieveSearchParameters($item);

        if ($cachedSearch = $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->findOneBy(['searchCriteria' => json_encode($attributes)])) {
            $businesses[$item['friendlyUrl']] = $cachedSearch->getResponse();
        } else {
            try {
                $yelp->setHttpClientVerify($this->container->get('request_stack')->getCurrentRequest()->isSecure());
                if ($yelpRequest = $yelp->search($attributes)) {
                    $yelpRequest['businesses'] = $this->sanitizeSearchResponse($yelpRequest['businesses']);
                    $businesses[$item['friendlyUrl']] = $yelpRequest['businesses'];
                    $manager = $this->container->get('doctrine')->getManager();

                    $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->save(json_encode($attributes),
                        $yelpRequest['businesses'], $manager);
                }

            } catch (Exception $e) {
                $logger = $this->container->get('logger');
                $logger->critical('Yelp exception: '.$e);
                $params['_return'] = false;
            }
        }

        foreach ($businesses[$item['friendlyUrl']] as $business) {
            if (Inflector::friendly_title($business['name'], '-',
                    true) === Inflector::friendly_title($helper->titleHandler($item['title'], true), '-', true)) {
                $yelpImage = $business['image_url'];
                echo $this->container->get('templating')->render('YelpIntegrationBundle::featuredblock-yelpimage.html.twig',
                    [
                        'YelpImage' => $yelpImage,
                        'ItemTitle' => $item['title'],
                    ]);
                break;
            }
        }

        !$yelpImage and $params['_return'] = false;
    }

    private function getThemeBoxVerticalXSOverrideNoImage(&$params = null)
    {
        $yelp = $this->container->get('api.yelp');

        $businesses = [];
        $yelpImage = null;

        if (!$yelp->hasPrivateKey() || $params['item']->getType() !== 'listing') {
            $params['_return'] = false;

            return;
        }

        $item = $params['item']->getData();

        if (isset($item['scriptedFieldData'][0])) {
            $item = $item['scriptedFieldData'][0];
        }

        $attributes = $this->container->get('helper.yelp')->retrieveSearchParameters($item);

        if ($cachedSearch = $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->findOneBy(['searchCriteria' => json_encode($attributes)])) {
            $businesses[$item['friendlyUrl']] = $cachedSearch->getResponse();
        } else {
            try {
                $yelp->setHttpClientVerify($this->container->get('request_stack')->getCurrentRequest()->isSecure());
                if ($yelpRequest = $yelp->search($attributes)) {
                    $yelpRequest['businesses'] = $this->sanitizeSearchResponse($yelpRequest['businesses']);
                    $businesses[$item['friendlyUrl']] = $yelpRequest['businesses'];
                    $manager = $this->container->get('doctrine')->getManager();

                    $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->save(json_encode($attributes),
                        $yelpRequest['businesses'], $manager);
                }

            } catch (Exception $e) {
                $logger = $this->container->get('logger');
                $logger->critical('Yelp exception: '.$e);
                $params['_return'] = false;
            }
        }

        foreach ($businesses[$item['friendlyUrl']] as $business) {
            if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($item['title'],
                    '-', true)) {
                $yelpImage = $business['image_url'];
                echo $this->container->get('templating')->render('YelpIntegrationBundle::featuredblock-yelpimage.html.twig',
                    [
                        'YelpImage' => $yelpImage,
                        'ItemTitle' => $item['title'],
                    ]);
                break;
            }
        }

        !$yelpImage and $params['_return'] = false;
    }

    private function getThemeBoxHorizonalOverrideNoImage(&$params = null)
    {
        $yelp = $this->container->get('api.yelp');

        $businesses = [];
        $yelpImage = null;

        if (!$yelp->hasPrivateKey() || $params['item']->getType() !== 'listing') {
            $params['_return'] = false;

            return;
        }

        $item = $params['item']->getData();

        if (isset($item['scriptedFieldData'][0])) {
            $item = $item['scriptedFieldData'][0];
        }

        $attributes = $this->container->get('helper.yelp')->retrieveSearchParameters($item);

        if ($cachedSearch = $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->findOneBy(['searchCriteria' => json_encode($attributes)])) {
            $businesses[$item['friendlyUrl']] = $cachedSearch->getResponse();
        } else {
            try {
                $yelp->setHttpClientVerify($this->container->get('request_stack')->getCurrentRequest()->isSecure());
                if ($yelpRequest = $yelp->search($attributes)) {
                    $yelpRequest['businesses'] = $this->sanitizeSearchResponse($yelpRequest['businesses']);
                    $businesses[$item['friendlyUrl']] = $yelpRequest['businesses'];
                    $manager = $this->container->get('doctrine')->getManager();

                    $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->save(json_encode($attributes),
                        $yelpRequest['businesses'], $manager);
                }

            } catch (Exception $e) {
                $logger = $this->container->get('logger');
                $logger->critical('Yelp exception: '.$e);
                $params['_return'] = false;
            }
        }

        foreach ($businesses[$item['friendlyUrl']] as $business) {
            if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($item['title'],
                    '-', true)) {
                $yelpImage = $business['image_url'];
                echo $this->container->get('templating')->render('YelpIntegrationBundle::featuredblock-yelpimage.html.twig',
                    [
                        'YelpImage' => $yelpImage,
                        'ItemTitle' => $item['title'],
                    ]);
                break;
            }
        }

        !$yelpImage and $params['_return'] = false;
    }

    private function getThemeBoxHorizonalXSOverrideNoImage(&$params = null)
    {
        $yelp = $this->container->get('api.yelp');

        $businesses = [];
        $yelpImage = null;

        if (!$yelp->hasPrivateKey() || $params['item']->getType() !== 'listing') {
            $params['_return'] = false;

            return;
        }

        $item = $params['item']->getData();

        if (isset($item['scriptedFieldData'][0])) {
            $item = $item['scriptedFieldData'][0];
        }

        $attributes = $this->container->get('helper.yelp')->retrieveSearchParameters($item);

        if ($cachedSearch = $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->findOneBy(['searchCriteria' => json_encode($attributes)])) {
            $businesses[$item['friendlyUrl']] = $cachedSearch->getResponse();
        } else {
            try {
                $yelp->setHttpClientVerify($this->container->get('request_stack')->getCurrentRequest()->isSecure());
                if ($yelpRequest = $yelp->search($attributes)) {
                    $yelpRequest['businesses'] = $this->sanitizeSearchResponse($yelpRequest['businesses']);
                    $businesses[$item['friendlyUrl']] = $yelpRequest['businesses'];
                    $manager = $this->container->get('doctrine')->getManager();

                    $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->save(json_encode($attributes),
                        $yelpRequest['businesses'], $manager);
                }

            } catch (Exception $e) {
                $logger = $this->container->get('logger');
                $logger->critical('Yelp exception: '.$e);
                $params['_return'] = false;
            }
        }

        foreach ($businesses[$item['friendlyUrl']] as $business) {
            if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($item['title'],
                    '-', true)) {
                $yelpImage = $business['image_url'];
                echo $this->container->get('templating')->render('YelpIntegrationBundle::featuredblock-yelpimage.html.twig',
                    [
                        'YelpImage' => $yelpImage,
                        'ItemTitle' => $item['title'],
                    ]);
                break;
            }
        }

        !$yelpImage and $params['_return'] = false;
    }

    private function getThemeBoxFeaturedOverrideNoImage(&$params = null)
    {
        $yelp = $this->container->get('api.yelp');

        $businesses = [];
        $yelpImage = null;

        if (!$yelp->hasPrivateKey() || $params['item']->getType() !== 'listing') {
            $params['_return'] = false;

            return;
        }

        $item = $params['item']->getData();

        if (isset($item['scriptedFieldData'][0])) {
            $item = $item['scriptedFieldData'][0];
        }

        $attributes = $this->container->get('helper.yelp')->retrieveSearchParameters($item);

        if ($cachedSearch = $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->findOneBy(['searchCriteria' => json_encode($attributes)])) {
            $businesses[$item['friendlyUrl']] = $cachedSearch->getResponse();
        } else {
            try {
                $yelp->setHttpClientVerify($this->container->get('request_stack')->getCurrentRequest()->isSecure());
                if ($yelpRequest = $yelp->search($attributes)) {
                    $yelpRequest['businesses'] = $this->sanitizeSearchResponse($yelpRequest['businesses']);
                    $businesses[$item['friendlyUrl']] = $yelpRequest['businesses'];
                    $manager = $this->container->get('doctrine')->getManager();

                    $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->save(json_encode($attributes),
                        $yelpRequest['businesses'], $manager);
                }

            } catch (Exception $e) {
                $logger = $this->container->get('logger');
                $logger->critical('Yelp exception: '.$e);
                $params['_return'] = false;
            }
        }

        foreach ($businesses[$item['friendlyUrl']] as $business) {
            if (Inflector::friendly_title($business['name'], '-', true) === Inflector::friendly_title($item['title'],
                    '-', true)) {
                $yelpImage = $business['image_url'];
                echo $this->container->get('templating')->render('YelpIntegrationBundle::featuredblock-yelpimage.html.twig',
                    [
                        'YelpImage' => $yelpImage,
                        'ItemTitle' => $item['title'],
                    ]);
                break;
            }
        }

        !$yelpImage and $params['_return'] = false;
    }

    private function getThemeBoxFeaturedSmallOverrideNoImage(&$params = null)
    {
        $yelp = $this->container->get('api.yelp');
        $helper = $this->container->get('helper.yelp');

        $businesses = [];
        $yelpImage = null;

        if (!$yelp->hasPrivateKey() || $params['item']->getType() !== 'listing') {
            $params['_return'] = false;

            return;
        }

        $item = $params['item']->getData();

        if (isset($item['scriptedFieldData'][0])) {
            $item = $item['scriptedFieldData'][0];
        }

        $attributes = $this->container->get('helper.yelp')->retrieveSearchParameters($item);

        if ($cachedSearch = $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->findOneBy(['searchCriteria' => json_encode($attributes)])) {
            $businesses[$item['friendlyUrl']] = $cachedSearch->getResponse();
        } else {
            try {
                $yelp->setHttpClientVerify($this->container->get('request_stack')->getCurrentRequest()->isSecure());
                if ($yelpRequest = $yelp->search($attributes)) {
                    $yelpRequest['businesses'] = $this->sanitizeSearchResponse($yelpRequest['businesses']);
                    $businesses[$item['friendlyUrl']] = $yelpRequest['businesses'];
                    $manager = $this->container->get('doctrine')->getManager();

                    $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->save(json_encode($attributes),
                        $yelpRequest['businesses'], $manager);
                }

            } catch (Exception $e) {
                $logger = $this->container->get('logger');
                $logger->critical('Yelp exception: '.$e);
                $params['_return'] = false;
            }
        }

        foreach ($businesses[$item['friendlyUrl']] as $business) {
            if (Inflector::friendly_title($business['name'], '-',
                    true) === Inflector::friendly_title($helper->titleHandler($item['title'], true), '-', true)) {
                $yelpImage = $business['image_url'];
                echo $this->container->get('templating')->render('YelpIntegrationBundle::featuredblock-yelpimage.html.twig',
                    [
                        'YelpImage' => $yelpImage,
                        'ItemTitle' => $item['title'],
                    ]);
                break;
            }
        }

        !$yelpImage and $params['_return'] = false;
    }

    private function getDetailExtensionBeforeIncreaseOverviewCount(&$params = null)
    {
        $yelp = $this->container->get('api.yelp');

        if ($yelp->hasPrivateKey()) {
            $attributes = $this->container->get('helper.yelp')->retrieveSearchParametersGivenObject($params['listing']);

            $cachedSearch = $this->container->get('doctrine')->getRepository('YelpIntegrationBundle:YelpCache')->findOneBy(['searchCriteria' => json_encode($attributes)]);

            if (!empty($cachedSearch)) {
                $params['contentCount']++;
                $params['overviewCount']++;
            }
        }
    }

    private function getDetailExtensionOverwriteHasReview(&$params = null)
    {
        $showYelpReview = $this->container->get('modstore.storage.service')->retrieve('showYelpReview');

        if ((empty($showYelpReview) || $showYelpReview !== 'true') && ($params['listingLevel']->hasReview && $params['reviews_active'] && !empty($params['reviewsPaginated']['reviews']->count()))) {
            $params['contentCount']++;
            $params['activeTab'] = 3;
        }
    }

    private function getListingServiceAfterFormatHoursWork(&$params = null)
    {
        if (empty($params['hours']) && $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness')) {
            $this->container->get('modstore.storage.service')->store('businessFromYelp', true);
            if (array_key_exists('is_open_now',$yelpBusiness)){
                $this->container->get('modstore.storage.service')->store('yelpBusinessIsOpenNow', $yelpBusiness['is_open_now']);
            }
            if (array_key_exists('hours',$yelpBusiness)){
                foreach ($yelpBusiness['hours'] as $day => $yelpHour) {
                    if (is_array($yelpHour)) {
                        $yHourArr = $yelpHour['hours'];
                        if (is_array($yHourArr)){
                            foreach ($yHourArr as $yHour) {
                                $hoursDayEntry = null;
                                if(array_key_exists('hours_start',$yHour) &&
                                    array_key_exists('hours_end',$yHour)) {
                                    $hoursDayEntry = [
                                        'hours_start' => $yHour['hours_start'],
                                        'hours_end' => $yHour['hours_end']
                                    ];
                                }
                                if(!empty($hoursDayEntry)){
                                    $params['hours'][$day][] = $hoursDayEntry;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function getDetailContentValidateReview(&$params = null)
    {
        $showYelpReview = $this->container->get('modstore.storage.service')->retrieve('showYelpReview');

        if (empty($showYelpReview) || $showYelpReview !== 'true') {
            $params['_return'] = false;
        } else {
            $params['_return'] = true;
        }
    }

    /**
     * @param null $params
     */
    private function getDetailContentAfterRenderReview(&$params = null)
    {
        $showYelpReview = $this->container->get('modstore.storage.service')->retrieve('showYelpReview');

        if(empty($showYelpReview) || $showYelpReview !== 'true') {
            $params['_return'] = false;
        } else {
            $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');

            if ($yelpBusiness === null && !empty($params['item'])) {
                try {
                    $this->container->get('yelp.service')->storeYelpBusiness($params['item']);
                    $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                } catch (Exception $e) {
                    $params['_return'] = false;
                }
            }

            if (!empty($yelpBusiness['review_count'])) {
                $yelpReview = $this->container->get('modstore.storage.service')->retrieve('yelpReviews');

                echo $this->container->get('templating')->render('YelpIntegrationBundle::yelp-review.html.twig', [
                    'yelpReview' => $yelpReview,
                    'overview'   => $params['overview']
                ]);
            }
        }
    }

    private function getListingBeforeAddGlobalVars(&$params = null)
    {
        $params['_return'] = false;
        return;
        if(empty($params['reviewsPaginated']['reviews']->count()) && ($yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness')) && !empty($yelpBusiness['review_count'])) {
            $this->container->get('modstore.storage.service')->store('showYelpReview', 'true');

            $params['item']->setAvgReview($yelpBusiness['rating']);
            $params['reviewsPaginated']['total'] = $yelpBusiness['review_count'];
        } else {
            $params['_return'] = false;
        }
    }

    private function getDetailContentOverwriteReviewButton(&$params = null)
    {
        $showYelpReview = $this->container->get('modstore.storage.service')->retrieve('showYelpReview');

        if (empty($showYelpReview) || $showYelpReview !== 'true') {
            $params['_return'] = false;
        } else {
            $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');

            if ($yelpBusiness === null && !empty($params['item'])) {
                try {
                    $this->container->get('yelp.service')->storeYelpBusiness($params['item']);
                    $yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness');
                } catch (Exception $e) {
                    $params['_return'] = false;
                }
            }

            if (!empty($yelpBusiness['review_count'])) {
                $yelpReview = $this->container->get('modstore.storage.service')->retrieve('yelpReviews');
                if(!empty($yelpReview)) {
                    echo $this->container->get('templating')->render('YelpIntegrationBundle::yelp-viewreview.html.twig', [
                        'yelpReview' => $yelpReview
                    ]);
                } else {
                    echo $this->container->get('templating')->render('YelpIntegrationBundle::yelp-noreviewsforlanguage.html.twig', [
                        'yelpReview' => $yelpReview
                    ]);
                }
            }
        }
    }

    private function getSummaryChechNoImage(&$params = null)
    {
        if (empty($this->container->get('modstore.storage.service')->retrieve('yelpData')['yelpImageUrl'])) {
            $params['_return'] = false;
        }
    }

    private function getSummaryOverwritePhone(&$params = null)
    {
        if ($yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpData')
        ) {
            echo $this->container->get('templating')->render('YelpIntegrationBundle::yelp-detail-phone.html.twig', [
                'yelpBusiness' => $yelpBusiness
            ]);
        }
    }

    private function getDetailContentOverwriteAllReviewsLink(&$params = null)
    {
        $showYelpReview = $this->container->get('modstore.storage.service')->retrieve('showYelpReview');

        if (empty($showYelpReview) || $showYelpReview !== 'true') {
            $params['_return'] = false;
        } else if ($yelpBusiness = $this->container->get('modstore.storage.service')->retrieve('yelpBusiness')) {
            echo $this->container->get('templating')->render('YelpIntegrationBundle::yelp-detail-review-link.html.twig',
                [
                    'reviewsPaginated' => $params['reviewsPaginated']
                ]);
        }
    }
}
