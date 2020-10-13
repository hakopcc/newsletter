<?php

namespace ArcaSolutions\ListingBundle\Controller;

use ArcaSolutions\CoreBundle\Exception\ItemNotFoundException;
use ArcaSolutions\CoreBundle\Form\Type\CaptchaType;
use ArcaSolutions\CoreBundle\Services\ValidationDetail;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingCategory;
use ArcaSolutions\ListingBundle\Entity\ListingChoice;
use ArcaSolutions\ListingBundle\Entity\ListingLevelField;
use ArcaSolutions\ListingBundle\Entity\ListingTField;
use ArcaSolutions\ListingBundle\Entity\ListingTFieldGroup;
use ArcaSolutions\ListingBundle\ListingItemDetail;
use ArcaSolutions\ListingBundle\Sample\ListingSample;
use ArcaSolutions\ReportsBundle\Services\ReportHandler;
use ArcaSolutions\SearchBundle\Entity\Elasticsearch\Category;
use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use ArcaSolutions\WebBundle\Form\Type\ReviewsType;
use ArcaSolutions\WebBundle\Form\Type\SendMailType;
use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\MapHelperBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        $this->get('widget.service')->setModule(ParameterHandler::MODULE_LISTING);

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::LISTING_HOME_PAGE);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * @param $friendlyUrl
     *
     * @return Response
     * @throws Exception
     */
    public function detailAction($friendlyUrl)
    {
        /*
         * Validation
         */
        /* @var $item Listing For phpstorm get properties of entity Listing */
        $item = $this->get('search.engine')->itemFriendlyURL($friendlyUrl, 'listing', 'ListingBundle:Listing');
        /* listing not found by friendlyURL */
        if ($item === null) {
            throw new ItemNotFoundException();
        }

        /* normalizes item to validate detail */
        $listingItemDetail = new ListingItemDetail($this->container, $item);
        $level = $listingItemDetail->getLevel();

        /* validating if listing is enabled, if listing's level is active and if level allows detail */
        if (!ValidationDetail::isDetailAllowed($listingItemDetail)) {
            $parameterHandler = new ParameterHandler($this->container, false);
            $parameterHandler->addModule(ParameterHandler::MODULE_LISTING);
            $parameterHandler->addKeyword($friendlyUrl);

            $this->get('request_stack')->getCurrentRequest()->cookies->set('edirectory_results_viewmode', 'item');

            return $this->redirect($parameterHandler->buildUrl());
        }

        /* ModStores Hooks */
        HookFire('listing_after_validate_itemdetail', [
            'item' => &$item,
            'that' => &$this,
        ]);

        /*
         * Report
         */
        if (false === ValidationDetail::isSponsorsOrSitemgr($listingItemDetail)) {
            /* Counts the view towards the statistics */
            $this->container->get('reporthandler')->addListingReport($item->getId(), ReportHandler::LISTING_DETAIL);
        }

        /* gets item's gallery */
        $gallery = null;
        if ($listingItemDetail->getLevel()->imageCount > 0) {
            $gallery = $this->get('doctrine')->getRepository('ListingBundle:Listing')
                ->getGallery($item, $listingItemDetail->getLevel()->imageCount);
        }

        $map = null;
        $iconPath = null;
        /* checks if item has latitude and longitude to show the map */
        if ($item->getLatitude() && $item->getLongitude() && $this->container->get('settings')->getDomainSetting('google_map_status') == 'on'
            and $googleMapsKey = $this->container->get('settings')->getDomainSetting('google_api_key')) {
            /* sets map */
            $map = $this->container->get('listing.service')->getDetailMap($item);

            /* ModStores Hooks */
            HookFire('listing_before_buildmapJSHelper', [
                'map'      => &$map,
                'iconPath' => $iconPath
            ]);

            $mapJSHelper = MapHelperBuilder::create()->build()->renderJavascript($map);
            $apiHelper = ApiHelperBuilder::create()->setKey($googleMapsKey)->build()->render([$map]);

            $jsHandler = $this->container->get('javascripthandler');
            $jsHandler->addJSBlock('::js/summary/map.html.twig');
            $jsHandler->addTwigParameter('mapJSHelper', $mapJSHelper);
            $jsHandler->addTwigParameter('apiHelper', $apiHelper);
        }

        /* gets item reviews */
        $reviewsPaginated = $this->get('doctrine')->getRepository('WebBundle:Review')->getReviewsPaginated($item->getId(), 1);

        /* Validates if listing has the review active */
        $reviews_active = $this->getDoctrine()->getRepository('WebBundle:Setting')
            ->getSetting('review_listing_enabled');

        $categoryIds = [];
        foreach ($item->getCategories() as $category) {
            /* @var $category ListingCategory */
            $categoryIds[] = Category::create()
                ->setId($category->getId())
                ->setModule(ParameterHandler::MODULE_LISTING);
        }

        /* gets listing's deals */
        $deals = [];
        foreach ($item->getDeals() as $deal) {
            if ($this->get('deal.handler')->isValid($deal)) {
                $deals[] = $deal;
            }
        }
        /* limit deals by listing level */
        $deals = array_slice($deals, 0, $listingItemDetail->getLevel()->dealCount);

        $badges = array_map(function ($item) {
            /* @var $item ListingChoice */
            return $item->getEditorChoice();
        }, $item->getChoices()->toArray());

        /* Gets listing classifieds */
        $classifieds = [];
        foreach ($item->getClassifieds() as $classified) {
            if ($this->get('classified.handler')->isValid($classified)) {
                $classifieds[] = $classified;
            }
        }

        /* Limit classified by listing level */
        $classifieds = array_slice($classifieds, 0, $level->classifiedCount);

        $this->get('widget.service')->setModule(ParameterHandler::MODULE_LISTING);

        $userId = $this->container->get('request')->getSession()->get('SESS_ACCOUNT_ID');
        $memberAccount = null;

        if($userId) {
            $memberAccount = $this->container->get('doctrine')->getRepository('WebBundle:Accountprofilecontact')->find($userId);
        }
        $consentSettings = $this->container->get("settingMain.service")->getSettingsConsent();
        $consentSettings = $consentSettings->getValue();

        $arrayObj = [
            'member' => $memberAccount,
            'review' => $consentSettings
        ];
        $formSendMail = $this->createForm(new SendMailType(), null, $arrayObj);

        $arrayObj = [
            'member' => $memberAccount ? true : false,
            'review' => $consentSettings
        ];
        $formReview = $this->createForm(ReviewsType::class, null, $arrayObj);

        if (!$userId) {

            if ($this->container->get('settings')->getDomainSetting('google_recaptcha_status') === 'on') {
                $options = [];
            } else {
                $options = [
                    'reload' => true,
                    'as_url' => true,
                ];
            }

            $formSendMail->add('sendEmailCaptcha', CaptchaType::class, $options);
            $formReview->add('reviewCaptcha', CaptchaType::class, $options);
        }

        $hours = $this->container->get('listing.service')->formatHoursWork($item->getHoursWork());

        if(!empty($item->getFeatures())) {
            $features = json_decode($item->getFeatures(), true);
        }

        $listingWidgets = $this->container->get('listingtemplate.service')->getAllListingWidgetsByListingTemplate($item->getListingTemplate()->getId());

        $coverImage = $this->container->get('listing.service')->getCoverImage($item);

        $logoImage = $this->container->get('listing.service')->getLogoImage($item);

        $address = $this->container->get('listing.service')->getAddress($item);

        $twig = $this->container->get('twig');

        /* ModStores Hooks */
        HookFire('listing_before_add_globalvars', [
            'item'             => &$item,
            'that'             => &$this,
            'reviewsPaginated' => &$reviewsPaginated
        ]);

        $twig->addGlobal('item', $item);
        $twig->addGlobal('friendlyUrl', $friendlyUrl);
        $twig->addGlobal('address', $address);
        $twig->addGlobal('listingWidgets', $listingWidgets);
        $twig->addGlobal('coverImage', $coverImage);
        $twig->addGlobal('logoImage', $logoImage);
        $twig->addGlobal('deals', $deals);
        $twig->addGlobal('classifieds', $classifieds);
        $twig->addGlobal('level', $level);
        $twig->addGlobal('badges', $badges);
        $twig->addGlobal('gallery', $gallery);
        $twig->addGlobal('bannerCategories', $categoryIds);
        $twig->addGlobal('reviews_active', $reviews_active);
        $twig->addGlobal('reviewsPaginated', $reviewsPaginated);
        $twig->addGlobal('map', $map);
        !empty($features) and $twig->addGlobal('features', $features);
        !empty($hours) and $twig->addGlobal('hoursWork', $hours);
        $formSendMail and $twig->addGlobal('formSendMail', $formSendMail->createView());
        $formReview and $twig->addGlobal('formReview', $formReview->createView());

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::LISTING_DETAIL_PAGE);

        /* ModStores Hooks */
        HookFire('listing_before_render', [
            'that'     => &$this,
            'item'     => &$item,
            'page'     => &$page,
            'map'      => &$map,
            'iconPath' => &$iconPath,
        ]);

        return $this->render('::modules/listing/detail.html.twig', [
            'pageId'          => $page->getId(),
            'customTag' => $page->getCustomTag(),
        ]);
    }

    /**
     * @param int $level
     *
     * @param int $template
     * @return Response
     * @throws Exception
     */
    public function sampleDetailAction($level = 0, $template = 0)
    {
        $item = new ListingSample($level, $this->get('translator'), $this->get('doctrine'), $template);
        $listingItemDetail = new ListingItemDetail($this->container, $item);

        $map = null;
        /* checks if item has latitude and longitude to show the map */
        if ($item->getLatitude() && $item->getLongitude() && $this->container->get('settings')->getDomainSetting('google_map_status') == 'on'
            and $googleMapsKey = $this->container->get('settings')->getDomainSetting('google_api_key')) {
            /* sets map */

            $map = $this->container->get('listing.service')->getDetailMap($item);

            $mapJSHelper = MapHelperBuilder::create()->build()->renderJavascript($map);
            $apiHelper = ApiHelperBuilder::create()->setKey($googleMapsKey)->build()->render([$map]);

            $jsHandler = $this->container->get('javascripthandler');
            $jsHandler->addJSBlock('::js/summary/map.html.twig');
            $jsHandler->addTwigParameter('mapJSHelper', $mapJSHelper);
            $jsHandler->addTwigParameter('apiHelper', $apiHelper);
        }

        /* Validates if listing has the review active */
        $reviews_active = $this->getDoctrine()->getRepository('WebBundle:Setting')
            ->getSetting('review_listing_enabled');

        $editorChoice = $this->getDoctrine()->getRepository('ListingBundle:EditorChoice')->findby([
            'available' => 1,
        ]);

        $coverImage = $this->container->get('templating.helper.assets')->getUrl('assets/images/placeholders/1024x768.jpg');

        $logoImage = [
            '80x80' => $this->container->get('templating.helper.assets')->getUrl('assets/images/placeholders/80x80.jpg'),
            '96x96' => $this->container->get('templating.helper.assets')->getUrl('assets/images/placeholders/96x96.jpg')
        ];

        $listingWidgets = $this->container->get('listingtemplate.service')->getAllListingWidgetsByListingTemplate($item->getListingTemplate()->getId());

        /* gets item reviews */
        $reviewsPaginated = [
            'reviews'   => new ArrayCollection($item->getReviews()),
            'total'     => $item->getReviewCount(),
            'pageCount' => 1
        ];

        $hours = $this->container->get('listing.service')->formatHoursWork($item->getHoursWork());

        if(!empty($item->getFeatures())) {
            $features = json_decode($item->getFeatures(), true);
        }

        $address = $this->container->get('translator')->trans('Street Name, Number, City, State Zipcode, Country');

        $item->setDeals($listingItemDetail->getLevel()->dealCount);
        $item->setClassifieds($listingItemDetail->getLevel()->classifiedCount);
        $item->setListingTemplateId($template);
        $twig = $this->container->get('twig');

        /* ModStores Hooks */
        HookFire('listingsample_before_add_globalvars', [
            'item' => &$item,
            'that' => &$this,
        ]);

        $twig->addGlobal('item', $item);
        $twig->addGlobal('address', $address);
        $twig->addGlobal('listingWidgets', $listingWidgets);
        $twig->addGlobal('reviewsPaginated', $reviewsPaginated);
        $twig->addGlobal('coverImage', $coverImage);
        $twig->addGlobal('logoImage', $logoImage);
        $twig->addGlobal('level', $listingItemDetail->getLevel());
        $twig->addGlobal('map', $map);
        $twig->addGlobal('gallery', $item->getGallery(--$listingItemDetail->getLevel()->imageCount));
        $twig->addGlobal('reviews_active', $reviews_active);
        $twig->addGlobal('reviews', $item->getReviews());
        $twig->addGlobal('reviews_total', $item->getReviewCount());
        $twig->addGlobal('categories', $item->getCategories());
        $twig->addGlobal('deals', $item->getDeals());
        $twig->addGlobal('classifieds', $item->getClassifieds());
        $twig->addGlobal('locationsIDs', $item->getFakeLocationsIds());
        $twig->addGlobal('locationsObjs', $item->getLocationObjects());
        $twig->addGlobal('badges', $editorChoice);
        $twig->addGlobal('isSample', true);
        $twig->addGlobal('type', 'listing');
        !empty($features) and $twig->addGlobal('features', $features);
        !empty($hours) and $twig->addGlobal('hoursWork', $hours);

        $this->get('widget.service')->setModule(ParameterHandler::MODULE_LISTING);
        /* @var Page $page*/
        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::LISTING_DETAIL_PAGE);

        /* ModStores Hooks */
        HookFire('listingsample_before_render', [
            'page' => &$page,
            'that' => &$this,
        ]);

        return $this->render('::modules/listing/detail.html.twig', [
            'pageId'    => $page->getId(),
            'customTag' => $page->getCustomTag(),
        ]);
    }

    /**
     * @return Response
     */
    public function allcategoriesAction()
    {
        /* Loading and setting wysiwyg */
        $this->get('widget.service')->setModule(ParameterHandler::MODULE_LISTING);

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::LISTING_CATEGORIES_PAGE);

        $result = $this->get('search.repository.category')
            ->findCategoriesWithItens('listing');

        $twig = $this->get('twig');

        $twig->addGlobal('categories', $result);
        $twig->addGlobal('routing', ParameterHandler::MODULE_LISTING);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function viewContactAction()
    {
        $return = [
            'status' => false,
        ];

        $session = $this->container->get('session');
        $request = $this->container->get('request');

        $listingId = $request->request->get('item');
        $type = null;
        $reportType = null;

        switch ($request->request->get('type')) {
            case 'phone':
                $type = 'Phone';
                $reportType = ReportHandler::LISTING_PHONE;
                break;
            case 'additional_phone':
                $type = 'Additional Phone';
                $reportType = ReportHandler::LISTING_ADDITIONAL_PHONE;
                break;
            case 'url':
                $type = 'Url';
                $reportType = ReportHandler::LISTING_CLICK;
                break;
        }

        if ($type) {
            $recentlyViewed = $session->get("listing{$type}Viewed", []);

            if (empty($recentlyViewed[$listingId])) {
                /* Counts the view towards the statistics */
                $this->container->get('reporthandler')->addListingReport($listingId, $reportType);

                $listing = $this->get('doctrine')->getRepository('ListingBundle:Listing')->find($listingId);

                $recentlyViewed[$listingId] = call_user_func([$listing, "get{$type}"]);
                $session->set("listing{$type}Viewed", $recentlyViewed);
            }

            $return['status'] = true;
            $return['data'] = $recentlyViewed[$listingId];
        }

        return new JsonResponse($return);
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function alllocationsAction()
    {
        $locations_enable = $this->get('doctrine')->getRepository('WebBundle:SettingLocation')->getLocationsEnabledID();
        $locations = $this->get('helper.location')->getAllLocations($locations_enable, ParameterHandler::MODULE_LISTING);

        $this->get('widget.service')->setModule(ParameterHandler::MODULE_LISTING);

        $twig = $this->container->get('twig');

        $twig->addGlobal('locations', $locations);
        $twig->addGlobal('routing', ParameterHandler::MODULE_LISTING);

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::LISTING_ALL_LOCATIONS);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * Returns locations on ajax call
     *
     * @return Response JsonResponse
     */
    public function locationsAction(Request $request)
    {
        return $this->container->get('location.service')->getChildrenLocations($request);
    }

    /**
     * @param String $friendlyUrl
     * @param Integer $page
     *
     * @return Response
     * @throws Exception
     */
    public function reviewAction($friendlyUrl, $page)
    {
        $page = $this->get('search.engine')->convertFromPaginationFormat($page);

        /* Gets listing and validation if exist */
        /* @var $listing Listing For phpstorm get properties of entity Listing */
        $listing = $this->get('search.engine')->itemFriendlyURL($friendlyUrl, 'listing', 'ListingBundle:Listing');
        if ($listing === null) {
            throw $this->createNotFoundException('This Listing does not exist');
        }

        /* Validates if level has the review active */
        $listingDetail = new ListingItemDetail($this->container, $listing);
        if (!$listingDetail->getLevel()->hasReview) {
            throw $this->createNotFoundException('This listing has not activated reviews');
        }

        /* Gets reviews of listing */
        $reviews = $this->getDoctrine()
            ->getRepository('WebBundle:Review')
            ->findBy([
                'itemType' => 'listing',
                'approved' => 1,
                'itemId'   => $listing->getId(),
            ], ['added' => 'DESC']);

        // Creates the pagination to reviews
        $pagination = $this->get('knp_paginator')->paginate($reviews, $page);

        /* Gets total of reviews */
        $reviews_total = $this->get('doctrine')->getRepository('WebBundle:Review')
            ->getTotalByItemId($listing->getId(), 'listing');

        /* Gets reviews of listing */
        $reviewsPaginated = $this->get('doctrine')->getRepository('WebBundle:Review')->getReviewsPaginated($listing->getId(), $page);

        /* normalizes item to validate detail */
        $listingItemDetail = new ListingItemDetail($this->container, $listing);
        $level = $listingItemDetail->getLevel();

        /* Validates if listing has the review active */
        $reviews_active = $this->getDoctrine()->getRepository('WebBundle:Setting')
            ->getSetting('review_listing_enabled');

        $userId = $this->container->get('request')->getSession()->get('SESS_ACCOUNT_ID');

        $memberAccount = null;

        if($userId) {
            $memberAccount = $this->container->get('doctrine')->getRepository('WebBundle:Accountprofilecontact')->find($userId);
        }
        $consentSettings = $this->container->get("settingMain.service")->getSettingsConsent();
        $consentSettings = $consentSettings->getValue();

        $arrayObj = [
            'member' => $memberAccount ? true : false,
            'review' => $consentSettings
        ];

        $formReview = $this->createForm(ReviewsType::class, null, $arrayObj);

        if (!$userId) {
            if ($this->container->get('settings')->getDomainSetting('google_recaptcha_status') === 'on') {
                $options = [];
            } else {
                $options = [
                    'reload' => true,
                    'as_url' => true,
                ];
            }
            $formReview->add('reviewCaptcha', CaptchaType::class, $options);
        }

        $this->get('widget.service')->setModule(ParameterHandler::MODULE_LISTING);

        $twig = $this->container->get('twig');

        $twig->addGlobal('review', $listing);
        $twig->addGlobal('reviewsPaginated', $reviewsPaginated);
        $twig->addGlobal('reviews_total', $reviews_total);
        $twig->addGlobal('pagination', $pagination);
        $twig->addGlobal('level', $level);
        $twig->addGlobal('reviews_active', $reviews_active);
        $twig->addGlobal('formReview', $formReview->createView());

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::LISTING_REVIEWS);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * @param String $friendlyUrl
     * @param Integer $page
     *
     * @return Response
     * @throws Exception
     */
    public function paginatedReviewAction($friendlyUrl, $page)
    {
        /* Validates if listing has the review active */
        $active = $this->getDoctrine()->getRepository('WebBundle:Setting')->getSetting('review_listing_enabled');
        if (empty($active)) {
            throw $this->createNotFoundException('Listing has not reviews activated');
        }

        /* Gets listing and validation if exist */
        /* @var $listing Listing For phpstorm get properties of entity Listing */
        $listing = $this->get('search.engine')->itemFriendlyURL($friendlyUrl, 'listing', 'ListingBundle:Listing');
        if ($listing === null) {
            throw $this->createNotFoundException('This Listing does not exist');
        }

        /* Gets reviews of listing */
        $reviewsPaginated = $this->get('doctrine')->getRepository('WebBundle:Review')->getReviewsPaginated($listing->getId(), $page);

        $reviewBlock = $this->renderView('@Listing/reviews-paginated.html.twig',[
            'reviewsPaginated' => $reviewsPaginated,
            'friendlyUrl'      => $friendlyUrl,
            'page'             => $page,
            'item'             => $listing
        ]);

        return JsonResponse::create([
            'reviewBlock' => $reviewBlock
        ]);
    }

    /**
     * Save report clicking(visit website)
     *
     * @param Request $request
     * @return Response nothing
     */
    public function reportClickAction(Request $request)
    {
        $friendlyUrl = json_decode($this->get('url_encryption')->decrypt($request->get('info')));
        $friendlyUrl = current($friendlyUrl);

        /*
         * Validation
         */
        /* @var $item Listing For phpstorm get properties of entity Listing */
        $item = $this->get('search.engine')->itemFriendlyURL($friendlyUrl, 'listing', 'ListingBundle:Listing');
        /* listing not found by friendlyURL */
        if (null === $item) {
            throw new ItemNotFoundException();
        }

        /*
        * Report
        */
        /* Counts click */
        $this->container->get('reporthandler')->addListingReport($item->getId(), ReportHandler::LISTING_CLICK);

        /* Return nothing */

        return new Response();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getListingLevelFieldsAction(Request $request)
    {
        /*
         * This controller is not being used for now.
         * It's code was moved to web/includes/code/loadLevelFieldsActionAjax.php to avoid an error with AJAX requests when managing multiples domains.
         */
        $level = $request->get('level');
        $templateId = $request->get('template');

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

        $levelFields = $this->container->get('listinglevelfield.service')->getListingLevelFieldsNameByLevel($level);

        /* ModStores Hooks */
        HookFire("listingbundle-controller-getlistinglevelfieldsaction_after_getlistinglevelfieldsnamebylevel", [
            'fields_array' => &$fields,
            'level_fields_array' => &$levelFields,
            'level_value_from_get' => $level
        ], true);

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
            $template = $this->container->get('doctrine')->getRepository('ListingBundle:ListingTemplate')->find($templateId);

            $customFields = $this->container->get('listingtemplatefield.service')->getCustomFieldsByTemplate($template);

            foreach($customFields as $customField) {
                $customLevelFields = $this->container->get('listinglevelfield.service')->getListingLevelFieldsByTemplateAndLevel($templateId, $level);

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

        return JsonResponse::create([
            'displayFields'     => $displayFields,
            'blockFields'       => $blockFields
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getListingTemplateFieldsAction(Request $request)
    {
        /*
         * This controller is not being used for now.
         * It's code was moved to web/includes/code/loadCustomFieldsActionAjax.php to avoid an error with AJAX requests when managing multiples domains.
         */
        $templateId = $request->get('template');
        $id = $request->get('listingId');
        $level = $request->get('level');

        $fieldValues = $this->container->get('listingfieldvalue.service')->getFieldValues($id);

        if(empty($level)) {
            $levelFields = $this->container->get('listinglevelfield.service')->getListingLevelFieldsByTemplate($templateId);
        } else {
            $levelFields = $this->container->get('listinglevelfield.service')->getListingLevelFieldsByTemplateAndLevel($templateId, $level);
        }

        $templateFieldsBlock = $this->container->get('listingtemplatefield.service')->renderListingTemplateFields($templateId, $levelFields, $fieldValues);

        return JsonResponse::create([
            'block' => $templateFieldsBlock
        ]);
    }
}
