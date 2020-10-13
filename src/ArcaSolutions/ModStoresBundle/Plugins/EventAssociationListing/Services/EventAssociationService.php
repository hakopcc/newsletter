<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\Services;

use ArcaSolutions\EventBundle\Entity\Event;
use ArcaSolutions\EventBundle\Entity\Internal\EventLevelFeatures;
use ArcaSolutions\CoreBundle\Services\LanguageHandler;
use ArcaSolutions\CoreBundle\Services\Settings as MainSettings;
use ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\Entity\EventAssociated;
use ArcaSolutions\MultiDomainBundle\Services\Settings as DomainSettings;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Repository\ListingRepository;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

class EventAssociationService
{
    /** @var Registry $_doctrine */
    private $_doctrine;

    /** @var Logger $_logger */
    private $_logger;

    /** @var Twig_Environment $_twig */
    private $_twig;

    /** @var ContainerInterface $_container */
    private $_container;

    /** @var RequestStack $_requestStack */
    private $_requestStack;

    /** @var TranslatorInterface $_translator */
    private $_translator;

    /** @var LanguageHandler $_languageHandler */
    private $_languageHandler;

    /** @var DomainSettings */
    private $_domainSettings;

    /** @var MainSettings */
    private $_mainSettings;

    /** @var ListingRepository $_listingRepository */
    private $_listingRepository;

    /** @var ObjectManager $_em */
    private $_em;

    /** @var string $_aliasSiteManager */
    private $_aliasSiteManager;

    public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;
    }

    /**
     * @return string|null
     */
    private function &getAliasSiteMgr()
    {
        if($this->_container !== null) {
            if(empty($this->_aliasSiteManager)) {
                $aliasSiteMgrParameterFromContainer = $this->_container->getParameter('alias_sitemgr_module');
                if(!empty($aliasSiteMgrParameterFromContainer) && is_string($aliasSiteMgrParameterFromContainer)){
                    $this->_aliasSiteManager = $aliasSiteMgrParameterFromContainer;
                }
                unset($aliasSiteMgrParameterFromContainer);
            }
        } else {
            $this->_aliasSiteManager = null;
        }
        return $this->_aliasSiteManager;
    }

    /**
     * @return Logger|null
     */
    private function &getLogger()
    {
        if($this->_container !== null) {
            if($this->_logger === null) {
                $loggerFromContainer = $this->_container->get('logger');
                if($loggerFromContainer!==null && is_a($loggerFromContainer, Logger::class)){
                    $this->_logger = $loggerFromContainer;
                }
                unset($loggerFromContainer);
            }
        } else {
            $this->_logger = null;
        }
        return $this->_logger;
    }

    /**
     * @return TranslatorInterface
     */
    private function &getTranslator()
    {
        if($this->_container !== null) {
            if($this->_translator === null) {
                $translatorFromContainer = $this->_container->get('translator');
                if($translatorFromContainer!==null && is_a($translatorFromContainer, TranslatorInterface::class)){
                    $this->_translator = $translatorFromContainer;
                }
                unset($translatorFromContainer);
            }
        } else {
            $this->_translator = null;
        }
        return $this->_translator;
    }

    /**
     * @return DomainSettings
     */
    private function &getDomainSettings()
    {
        if($this->_container !== null) {
            if($this->_domainSettings === null) {
                $domainSettingsFromContainer = $this->_container->get('multi_domain.information');
                if($domainSettingsFromContainer!==null && is_a($domainSettingsFromContainer, DomainSettings::class)){
                    $this->_domainSettings = $domainSettingsFromContainer;
                }
                unset($domainSettingsFromContainer);
            }
        } else {
            $this->_domainSettings = null;
        }
        return $this->_domainSettings;
    }

    /**
     * @return MainSettings
     */
    private function &getMainSettings()
    {
        if($this->_container !== null) {
            if($this->_mainSettings === null) {
                $mainSettingsFromContainer = $this->_container->get('settings');
                if($mainSettingsFromContainer!==null && is_a($mainSettingsFromContainer, MainSettings::class)){
                    $this->_mainSettings = $mainSettingsFromContainer;
                }
                unset($mainSettingsFromContainer);
            }
        } else {
            $this->_mainSettings = null;
        }
        return $this->_mainSettings;
    }

    /**
     * @return LanguageHandler
     */
    private function &getLanguageHandler()
    {
        if($this->_container !== null) {
            if($this->_languageHandler === null) {
                $languageHandlerFromContainer = $this->_container->get('languagehandler');
                if($languageHandlerFromContainer!==null && is_a($languageHandlerFromContainer, LanguageHandler::class)){
                    $this->_languageHandler = $languageHandlerFromContainer;
                }
                unset($languageHandlerFromContainer);
            }
        } else {
            $this->_languageHandler = null;
        }
        return $this->_languageHandler;
    }

    /**
     * @return Twig_Environment
     */
    private function &getTwigEnvironment()
    {
        if($this->_container !== null) {
            if($this->_twig === null) {
                $twigEnvironmentFromContainer = $this->_container->get('twig');
                if($twigEnvironmentFromContainer!==null && is_a($twigEnvironmentFromContainer, Twig_Environment::class)){
                    $this->_twig = $twigEnvironmentFromContainer;
                }
                unset($twigEnvironmentFromContainer);
            }
        } else {
            $this->_twig = null;
        }
        return $this->_twig;
    }

    /**
     * @return RequestStack
     */
    private function &getRequestStack()
    {
        if($this->_container !== null) {
            if($this->_requestStack === null) {
                $requestStackFromContainer = $this->_container->get('request_stack');
                if($requestStackFromContainer!==null && is_a($requestStackFromContainer, RequestStack::class)){
                    $this->_requestStack = $requestStackFromContainer;
                }
                unset($requestStackFromContainer);
            }
        } else {
            $this->_requestStack = null;
        }
        return $this->_requestStack;
    }

    /**
     * @return Registry
     */
    private function &getDoctrine()
    {
        if($this->_container !== null) {
            if($this->_doctrine === null) {
                $doctrineFromContainer = $this->_container->get('doctrine');
                if($doctrineFromContainer!==null && is_a($doctrineFromContainer, Registry::class)){
                    $this->_doctrine = $doctrineFromContainer;
                }
                unset($doctrineFromContainer);
            }
        } else {
            $this->_doctrine = null;
        }
        return $this->_doctrine;
    }

    /**
     * @return ListingRepository
     */
    private function &getListingRepository()
    {
        /** @var Registry $doctrineRef */
        $doctrineRef = &$this->getDoctrine();
        if($doctrineRef!==null) {
            if($this->_listingRepository === null) {
                $repositoryFromDoctrineRegistry = $doctrineRef->getRepository('ListingBundle:Listing');
                if($repositoryFromDoctrineRegistry!==null && is_a($repositoryFromDoctrineRegistry, ListingRepository::class)){
                    $this->_listingRepository = $repositoryFromDoctrineRegistry;
                }
                unset($repositoryFromDoctrineRegistry);
            }
        } else {
            $this->_listingRepository = null;
        }
        return $this->_listingRepository;
    }

    /**
     * @return ObjectManager
     */
    private function &getObjectManager()
    {
        /** @var Registry $doctrineRef */
        $doctrineRef = &$this->getDoctrine();
        if($doctrineRef!==null) {
            if($this->_em === null) {
                $objectManagerFromDoctrineRegistry = $doctrineRef->getManager();
                if($objectManagerFromDoctrineRegistry!==null && is_a($objectManagerFromDoctrineRegistry, ObjectManager::class)){
                    $this->_em = $objectManagerFromDoctrineRegistry;
                }
                unset($objectManagerFromDoctrineRegistry);
            }
        } else {
            $this->_em = null;
        }
        return $this->_em;
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isSiteMgr(Request $request)
    {
        $returnValue = false;
        $aliasSiteMgr = $this->getAliasSiteMgr();
        if(empty($aliasSiteMgr)){
            $aliasSiteMgr = 'sitemgr';
        }
        if ($request !== null) {
            // verify if sitemgr alias from real URL as well as http referer in case of ajax request
            $returnValue = (
                (strpos($request->getUri(), $aliasSiteMgr) !== false) ||
                ($request->isXmlHttpRequest() === true && strpos($request->server->get('HTTP_REFERER'), $aliasSiteMgr) !== false)
            );
        }
        return $returnValue;
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function getCurrentISOLang(Request $request)
    {
        $isSiteMgr = $this->isSiteMgr($request);
        $returnValue = 'en';
        /** @var LanguageHandler $languageHandler */
        $languageHandlerRef = &$this->getLanguageHandler();
        if ($languageHandlerRef!==null) {
            $locale = 'en';
            if (!$isSiteMgr) {
                $domainSettingsRef = &$this->getDomainSettings();
                if($domainSettingsRef!==null) {
                    $locale = $domainSettingsRef->getLocale();
                }
            } else {
                $mainSettingsRef = &$this->getMainSettings();
                if($mainSettingsRef!==null) {
                    $locale = $mainSettingsRef->getSetting('sitemgr_language');
                }
            }
            $returnValue = $languageHandlerRef->getISOLang($locale);
            unset($locale);
        }
        return $returnValue;
    }

    /**
     * @param Request $request
     * @param null $accountId
     * @return bool
     */
    private function checkIfIsLoggedInAsSponsorAndGetAccountId(Request $request, &$accountId=null)
    {
        $returnValue = false;
        if($request!==null) {
            /** @var SessionInterface $requestSession */
            $requestSession = $request->getSession();
            if ($requestSession !== null) {
                if ($requestSession->has('SESS_ACCOUNT_ID')) {
                    $accountIdFromSession = $requestSession->get('SESS_ACCOUNT_ID');
                    if (!empty($accountIdFromSession)) {
                        if (is_numeric($accountIdFromSession)) {
                            $accountId = $accountIdFromSession;
                            $returnValue = true;
                        }
                    }
                    unset($accountIdFromSession);
                }
            }
            unset($requestSession);
        }
        return $returnValue;
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isLoggedInAsSiteManager(Request $request)
    {
        $returnValue = false;
        if($request!==null) {
            /** @var SessionInterface $requestSession */
            $requestSession = $request->getSession();
            if ($requestSession !== null) {
                if ($requestSession->has('SM_LOGGEDIN')) {
                    $smLoggedInFromSession = $requestSession->get('SM_LOGGEDIN');
                    $returnValue = !empty($smLoggedInFromSession);
                    unset($smLoggedInFromSession);
                }
            }
            unset($requestSession);
        }
        return $returnValue;
    }

    /**
     * @param Request $request
     * @param $accountId
     * @param $listingId
     * @param bool $isLoggedInAsSponsor
     * @return bool
     */
    private function getAccountIdAndListinIdFromSessionAndRequest(Request $request, &$accountId, &$listingId, &$isLoggedInAsSponsor = false){
        $returnValue = false;
        $isLoggedInAsSponsor = false;
        $originalAccountId = $accountId;
        $originalListingId = $listingId;
        if ($request !== null) {
            $accountId = null;
            $listingId = null;
            $isSiteMgrLoggedIn = $this->isLoggedInAsSiteManager($request);
            $isLoggedInAsSponsor = $this->checkIfIsLoggedInAsSponsorAndGetAccountId($request, $accountId);
            if($isLoggedInAsSponsor && !empty($accountId)){
                $returnValue = true;
            }
            if ($request->query !== null) {
                if ($request->query->has('listingId')) {
                    $listingIdFromRequest = $request->query->get('listingId');
                    if (!empty($listingIdFromRequest)){
                        if(is_numeric($listingIdFromRequest)) {
                            $listingId = $listingIdFromRequest;
                            $returnValue = true;
                        }
                    } else {
                        $returnValue = true;
                    }
                    unset($listingIdFromRequest);
                } elseif ($request->query->has('id')){
                    $idFromRequest = $request->query->get('id');
                    if (!empty($idFromRequest)){
                        if(is_numeric($idFromRequest)) {
                            $listingId = $idFromRequest;
                            $returnValue = true;
                        }
                    } else {
                        $returnValue = true;
                    }
                    unset($idFromRequest);
                }
                unset($listingIdFromRequest);
                if ($request->query->has('accountId')) {
                    $accountIdFromRequest = $request->query->get('accountId');
                    if (!empty($accountIdFromRequest)){
                        if(is_numeric($accountIdFromRequest)) {
                            if ($isSiteMgrLoggedIn && $accountId === null) {
                                $accountId = $accountIdFromRequest;
                                $returnValue = true;
                            }
                        }
                    } elseif ($isSiteMgrLoggedIn) {
                        $returnValue = true;
                    }
                    unset($accountIdFromRequest);
                }
            }
            unset($isSiteMgrLoggedIn);
        }
        if(!$returnValue){
            $accountId = $originalAccountId;
            $listingId = $originalListingId;
        }
        return $returnValue;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Comparison|Func|Orx|Composite|string $whereCondition
     * @param Comparison|Func|Orx|Composite|string $extraWhereCondition
     */
    private function injectExtraWhereCondition(QueryBuilder $queryBuilder, &$whereCondition, $extraWhereCondition) : void
    {
        if ($whereCondition !== null && $extraWhereCondition !== null) {
            $originalWhereCondition = $whereCondition;
            $whereCondition = $queryBuilder->expr()->andX(
                $extraWhereCondition,
                $originalWhereCondition);
            unset($originalWhereCondition);
        }
    }

    /**
     * @param $accountId
     * @param $listingId
     * @param QueryBuilder $queryBuilder
     * @param array $eventLevels
     * @param Comparison|Func|Orx|Composite|string $extraWhereCondition
     * @param array $extraParameters
     * @param string $eventEntityAlias
     * @return bool
     */
    private function injectFromJoinAndWhereInEventQueryBuilder($accountId, $listingId, QueryBuilder &$queryBuilder, &$eventLevels, $extraWhereCondition=null, $extraParameters=array(), $eventEntityAlias = 'event')
    {
        $returnValue = false;
        $doctrineRef = &$this->getDoctrine();
        if($doctrineRef!==null) {
            $returnValue = true;
            $eventLevelEntities = (EventLevelFeatures::getAllLevelsAndNormalize($doctrineRef));
            $eventLevels = array();
            /** @var EventLevelFeatures $eventLevelEntity */
            foreach ($eventLevelEntities as $level => $eventLevelEntity) {
                $eventLevels[] = (string) $level;
            }
            if (!empty($eventEntityAlias) && $queryBuilder !== null) {
                $queryBuilder = $queryBuilder->from('EventBundle:Event', $eventEntityAlias);
                $whereCondition = null;
                if ($accountId !== null) {
                    $leftJoinCondition = null;
                    $queryBuilderParameters = array('accountId' => $accountId);
                    if ($listingId !== null) {
                        $leftJoinCondition = $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->eq('event_associated.event', $eventEntityAlias),
                            $queryBuilder->expr()->neq('event_associated.listingId', ':listingId')
                        );
                        $queryBuilderParameters['listingId'] = $listingId;
                    } else {
                        $leftJoinCondition = $queryBuilder->expr()->eq('event_associated.event', $eventEntityAlias);
                    }
                    if ($leftJoinCondition !== null) {
                        if (empty($eventLevels)) {
                            $whereCondition = $queryBuilder->expr()->eq($eventEntityAlias . '.accountId', ':accountId');
                        } else {
                            $whereCondition = $queryBuilder->expr()->andX(
                                $queryBuilder->expr()->eq($eventEntityAlias . '.accountId', ':accountId'),
                                $queryBuilder->expr()->in($eventEntityAlias . '.level', $eventLevels)
                            );
                        }
                        $queryBuilder = $queryBuilder->leftJoin('EventAssociationListingBundle:EventAssociated', 'event_associated', Join::WITH, $leftJoinCondition);
                        if ($whereCondition !== null) {
                            if ($extraWhereCondition !== null) {
                                $this->injectExtraWhereCondition($queryBuilder, $whereCondition, $extraWhereCondition);
                            }
                            $queryBuilder = $queryBuilder->where($whereCondition);
                        }
                        if (!empty($extraParameters)) {
                            array_merge($queryBuilderParameters, $extraParameters);
                        }
                        $queryBuilder = $queryBuilder->setParameters($queryBuilderParameters);
                    }
                    unset($leftJoinCondition, $queryBuilderParameters);
                } else {
                    if (empty($eventLevels)) {
                        $whereCondition = $queryBuilder->expr()->orX(//Needed because Event table does not have a FK that avoids set ID with value less then 1 (inexistent ones)
                            $queryBuilder->expr()->isNull($eventEntityAlias . '.accountId'),
                            $queryBuilder->expr()->lt($eventEntityAlias . '.accountId', 1)//Needed because Event table does not have a FK that avoids set ID with value less then 1 (inexistent ones)
                        );
                    } else {
                        $whereCondition = $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->orX(//Needed because Event table does not have a FK that avoids set ID with value less then 1 (inexistent one)
                                $queryBuilder->expr()->isNull($eventEntityAlias . '.accountId'),
                                $queryBuilder->expr()->lt($eventEntityAlias . '.accountId', 1)//Needed because Event table does not have a FK that avoids set ID with value less then 1 (inexistent one)
                            ),
                            $queryBuilder->expr()->in($eventEntityAlias . '.level', $eventLevels)
                        );
                    }
                    if ($whereCondition !== null) {
                        if ($extraWhereCondition !== null) {
                            $this->injectExtraWhereCondition($queryBuilder, $whereCondition, $extraWhereCondition);
                        }
                        $queryBuilder = $queryBuilder->where($whereCondition);
                        if (!empty($extraParameters)) {
                            $queryBuilder = $queryBuilder->setParameters($extraParameters);
                        }
                    }
                }
            }
        }
        return $returnValue;
    }

    /**
     * @param $request
     * @param $listingId
     * @param $accountId
     * @param bool $authorized
     * @param bool $isLoggedInAsSponsor
     * @return bool
     */
    private function tryToGetAccountIdFromListingAndCheckAuthorization($request, $listingId, &$accountId, &$authorized, $isLoggedInAsSponsor = false)
    {
        $returnValue = false;
        $listingRepositoryRef = &$this->getListingRepository();
        if($listingRepositoryRef!==null) {
            $returnValue = true;
            $authorized = false;
            if ($listingId !== null) {
                /** @var Listing $listing */
                $listing = $listingRepositoryRef->find($listingId);
                $accountIdFromListing = null;
                if ($listing !== null) {
                    $accountIdFromListing = $listing->getAccountId();
                }
                if ($accountId === null && $accountIdFromListing !== null) {
                    $accountId = $accountIdFromListing;
                }
                unset($listing, $accountIdFromListing);
            }
            $isLoggedInAsSiteManager = $this->isLoggedInAsSiteManager($request);
            $isLoggedIn = $isLoggedInAsSponsor || $isLoggedInAsSiteManager;
            $authorized = $isLoggedIn && ($accountId !== null || $isLoggedInAsSiteManager);
        }
        return $returnValue;
    }

    /**
     * @param $httpPostArray
     * @param $httpGetArray
     * @return string
     */
    public function getFormListingEvent()
    {
        $returnValue = 'Unexpected content';
        $allEventsAvailable = false;
        $error = null;
        $lang = 'en';
        $listingId = null;
        $accountId = null;
        try {
            $requestStackRef = &$this->getRequestStack();
            if($requestStackRef!==null) {
                $request = $requestStackRef->getCurrentRequest();
                if ($request === null || $request->query === null) {
                    $returnValue = 'Invalid content request';
                } else {
                    $lang = $this->getCurrentISOLang($request);
                    $availableEventCount = 0;
                    $translatorRef = &$this->getTranslator();
                    if ($translatorRef!==null) {
                        $error = $translatorRef->trans('Unable to acquire available events for association with this Listing due to an internal unexpected behavior.', array(), 'messages', $lang);
                    } else {
                        $error = 'Unable to acquire available events for association with this Listing due to an internal unexpected behavior.';
                    }
                    $isLoggedInAsSponsor = false;
                    if ($this->getAccountIdAndListinIdFromSessionAndRequest($request, $accountId, $listingId, $isLoggedInAsSponsor)) {
                        $authorized = false;
                        if ($this->tryToGetAccountIdFromListingAndCheckAuthorization($request, $listingId, $accountId, $authorized, $isLoggedInAsSponsor)) {
                            if ($authorized) {
                                /** @var ObjectManager $objectManagerRef */
                                $objectManagerRef = &$this->getObjectManager();
                                if ($objectManagerRef!==null) {
                                    $eventLevels = null;
                                    /** @var QueryBuilder $queryBuilder */
                                    $queryBuilder = $objectManagerRef->createQueryBuilder();
                                    $queryBuilder = $queryBuilder->select('count(event.id) AS availableCount');
                                    if ($this->injectFromJoinAndWhereInEventQueryBuilder($accountId, $listingId, $queryBuilder, $eventLevels)) {
                                        if (empty($eventLevels)) {
                                            $allEventsAvailable = false;
                                            $error = null;
                                        } else {
                                            $query = $queryBuilder->getQuery();
                                            $queryResult = $query->getSingleResult();
                                            $availableEventCount = $queryResult['availableCount'];
                                            $allEventsAvailable = $availableEventCount > 0;
                                            $error = null;
                                        }
                                        unset($eventLevels, $queryBuilder, $query, $queryResult, $availableEventCount);
                                    }
                                }
                            }
                        }
                        unset($authorized);
                    }
                    unset($availableEventCount);
                }
            }
        } catch (Exception $e) {
            $loggerRef = &$this->getLogger();
            if($loggerRef!==null) {
                $loggerRef->critical('Unexpected error in getFormListingEvent of EventAssociationService.php', ['exception' => $e]);
            }
        } finally {
            try {
                $twigEnvironmentRef = &$this->getTwigEnvironment();
                if($twigEnvironmentRef!==null) {
                    $returnValue = $twigEnvironmentRef->render('EventAssociationListingBundle::form-sitemgr-listing.html.twig', [
                        'lang' => $lang,
                        'error' => $error,
                        'listingId' => $listingId,
                        'accountId' => $accountId,
                        'allEventsAvailable' => $allEventsAvailable
                    ]);
                }
            } catch (Exception $e){
                $loggerRef = &$this->getLogger();
                if($loggerRef!==null) {
                    $loggerRef->critical('Unexpected error on try to render EventAssociationListingBundle::form-sitemgr-listing.html.twig in getFormListingEvent of EventAssociationService.php', ['exception' => $e]);
                }
            }
            unset($listing, $allEventsAvailable, $error, $lang,
                $availableEventCount, $accountId, $listingId,
                $queryBuilder, $query,$queryResult, $availableEventCount);
        }
        return $returnValue;
    }

    /**
     * @return string
     */
    public function saveListingEventTab()
    {
        $returnValue = '';
        $notifyErrorString = null;
        $notifySuccessString = null;
        $unexpectedErrorMessage = 'Unexpected error.';
        try {
            $requestStackRef = &$this->getRequestStack();
            if ($requestStackRef!==null) {
                $request = $requestStackRef->getCurrentRequest();

                if ($request !== null) {

                    $lang = $this->getCurrentISOLang($request);

                    /** @var TranslatorInterface $translatorRef */
                    $translatorRef = &$this->getTranslator($translatorRef);
                    if($translatorRef!==null) {
                        $unexpectedErrorMessage = $translatorRef->trans('Unexpected error.', array(), 'messages', $lang);
                    }

                    if (!empty($request->request) &&
                        ($request->request->has('listing_id') ||
                            $request->request->has('account_id'))) {

                        $isSiteMgrLoggedIn = $this->isLoggedInAsSiteManager($request);
                        $isLoggedInAsSponsor = $this->checkIfIsLoggedInAsSponsorAndGetAccountId($request);

                        if ($isSiteMgrLoggedIn || $isLoggedInAsSponsor) {
                            $events = $request->request->get('event_attached', array());
                            $listingId = null;
                            $listingIdFromGet = $request->request->get('listing_id', null);
                            $accountId = null;
                            $accountIdFromGet = $request->request->get('account_id', null);
                            if(!empty($accountIdFromGet) && is_numeric($accountIdFromGet)) {
                                $accountId = (int)$accountIdFromGet;
                            }
                            if(!empty($listingIdFromGet) && is_numeric($listingIdFromGet)) {
                                $listingId = (int)$listingIdFromGet;
                            }
                            if (!empty($listingId)) {
                                /** @var DoctrineRegistry $doctrineRef */
                                $doctrineRef = &$this->getDoctrine();
                                if ($doctrineRef!==null) {
                                    /** @var ObjectManager $objectManagerRef */
                                    $objectManagerRef = &$this->getObjectManager();
                                    if ($objectManagerRef!==null) {
                                        /** @var EntityRepository $eventAssociatedRepository */
                                        $eventAssociatedRepository = $doctrineRef->getRepository('EventAssociationListingBundle:EventAssociated');
                                        $eventRepository = $doctrineRef->getRepository('EventBundle:Event');
                                        if ($eventAssociatedRepository !== null && $eventRepository !== null) {
                                            /** @var EventAssociated[] $associations */
                                            $associations = $eventAssociatedRepository->findBy([
                                                'listingId' => $listingId,
                                            ]);
                                            $needFlush = false;
                                            foreach ($associations as $association) {
                                                /** @var Event $associatedEvent */
                                                $associatedEvent = $association->getEvent();
                                                if ($associatedEvent !== null) {
                                                    $associatedEventId = $associatedEvent->getId();
                                                    if (!empty($associatedEventId)) {
                                                        if (($key = array_search($associatedEventId, $events, true)) !== false) {
                                                            unset($events[$key]);//if exists on database, remove from event collection that will be used to made new associations
                                                        } else {
                                                            $objectManagerRef->remove($association);//if exists on database but do not exists on event collection, remove from database (the association has been deleted)
                                                            $needFlush = true;
                                                        }
                                                    }
                                                    unset($associatedEventId);
                                                } else {
                                                    $objectManagerRef->remove($association);
                                                    $needFlush = true;
                                                }
                                                unset($associatedEvent);
                                            }

                                            $eventAssociationErrors = array();
                                            foreach ($events as $eventId) {
                                                /** @var Event $event */
                                                $event = $eventRepository->find($eventId);
                                                if ($event !== null) {
                                                    $eventAccountId = $event->getAccountId();
                                                    if(empty($eventAccountId)){
                                                        $eventAccountId = null;
                                                    }
                                                    if ($eventAccountId === $accountId) {
                                                        $existentEventAssociationCount = $eventAssociatedRepository->count([
                                                            'event' => $event,
                                                        ]);
                                                        if ($existentEventAssociationCount === 0) {
                                                            $eventAssociation = new EventAssociated();
                                                            $eventAssociation->setEvent($event);
                                                            $eventAssociation->setListing($listingId);
                                                            $objectManagerRef->persist($eventAssociation);
                                                            unset($eventAssociation);
                                                            $needFlush = true;
                                                        }
                                                        unset($existentEventAssociationCount);
                                                    } else {
                                                        $eventTitle = $event->getTitle();
                                                        $eventAssociationError = 'Event "' . $eventTitle . '" need to have the same owner of the listing.';
                                                        if ($translatorRef !== null) {
                                                            $notifySuccessStringFormat = $translatorRef->trans('Event {0} need to have the same owner of the listing.', array(), 'messages', $lang);
                                                            $notifySuccessString = sprintf($notifySuccessStringFormat, $eventTitle);
                                                        }
                                                        $eventAssociationErrors[] = $eventAssociationError;
                                                        unset($eventTitle, $eventAssociationError);
                                                    }
                                                    unset($eventAccountId);
                                                }
                                                unset($event);
                                            }
                                            if ($needFlush) {
                                                $objectManagerRef->flush();
                                                $notifySuccessString = 'Listing event association updated successfully.';
                                                if ($translatorRef !== null) {
                                                    $notifySuccessString = $translatorRef->trans('Listing event association updated successfully.', array(), 'messages', $lang);
                                                }
                                            }
                                            if (!empty($eventAssociationErrors)) {
                                                $notifyErrorString = '<ul><li>' . implode('</li><li>', $eventAssociationErrors) . '</li></ul>';
                                            }
                                            unset($needFlush, $associations, $eventAssociationErrors);
                                        }
                                        unset($eventRepository, $eventAssociatedRepository);
                                    }
                                }
                            }
                            unset($events, $listingId, $accountId);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            if (!empty($notifyErrorString)) {
                if (string_substr_count($notifyErrorString, '<ul><li>') === 0) {
                    $notifyErrorString = '<ul><li>' . $notifyErrorString . '</li><li>' . $unexpectedErrorMessage . '</li></ul>';
                } else {
                    $notifyErrorString .= '<ul><li>' . $unexpectedErrorMessage . '</li></ul>';
                }
            } else {
                $notifyErrorString = $unexpectedErrorMessage;
            }
            $loggerRef = &$this->getLogger();
            if($loggerRef!==null) {
                $loggerRef->critical('Unexpected error in saveListingEventTab of EventAssociationService.php', ['exception' => $e]);
            }
        } finally {
            try {
                $twigRef = &$this->getTwigEnvironment();
                if($twigRef!==null) {
                    $returnValue = $twigRef->render('EventAssociationListingBundle:js:legacy-listing-event-js.html.twig', ['error' => $notifyErrorString, 'success' => $notifySuccessString]);
                }
            } catch (Exception $e) {
                $returnValue = '<script type="text/javascript">document.addEventListener(\'DOMContentLoaded\', function() {$(document).ready(function () {notify.error(\'' . $unexpectedErrorMessage . '\', \'\', {fadeOut: 0});});});</script>';
                $loggerRef = &$this->getLogger();
                if($loggerRef!==null) {
                    $loggerRef->critical('Unexpected error on try to render EventAssociationListingBundle:js:legacy-listing-event-js.html.twig in saveListingEventTab of EventAssociationService.php', ['exception' => $e]);
                }
            }
        }
        return $returnValue;
    }

    public function updateEventAssociations($listingId)
    {
        try {
            if (!empty($listingId)) {
                /** @var DoctrineRegistry $doctrineRef */
                $doctrineRef = &$this->getDoctrine();
                if ($doctrineRef!==null) {
                    /** @var ObjectManager $objectManagerRef */
                    $objectManagerRef = &$this->getObjectManager();
                    if ($objectManagerRef!==null) {
                        /** @var ListingRepository $listingRepositoryRef */
                        $listingRepositoryRef = &$this->getListingRepository();
                        if ($listingRepositoryRef!==null) {
                            /** @var Listing $listing */
                            $listing = $listingRepositoryRef->find($listingId);
                            if($listing!==null) {
                                $listingAccountId = $listing->getAccountId();
                                /** @var EntityRepository $eventAssociatedRepository */
                                $eventAssociatedRepository = $doctrineRef->getRepository('EventAssociationListingBundle:EventAssociated');
                                $eventRepository = $doctrineRef->getRepository('EventBundle:Event');
                                if ($eventAssociatedRepository !== null && $eventRepository !== null) {
                                    /** @var EventAssociated[] $associations */
                                    $associations = $eventAssociatedRepository->findBy([
                                        'listingId' => $listingId,
                                    ]);
                                    $needFlush = false;
                                    foreach ($associations as $association) {
                                        /** @var Event $associatedEvent */
                                        $associatedEvent = $association->getEvent();
                                        if ($associatedEvent !== null) {
                                            $associatedEventAccountId = $associatedEvent->getAccountId();
                                            if($listingAccountId!==$associatedEventAccountId){
                                                $objectManagerRef->remove($association);
                                                $needFlush = true;
                                            }
                                        } else {
                                            $objectManagerRef->remove($association);
                                            $needFlush = true;
                                        }
                                        unset($associatedEvent);
                                    }
                                    if ($needFlush) {
                                        $objectManagerRef->flush();
                                    }
                                }
                                unset($eventRepository, $eventAssociatedRepository, $listingAccountId);
                            }
                            unset($listing);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $loggerRef = &$this->getLogger();
            if($loggerRef!==null) {
                $loggerRef->critical('Unexpected error in updateEventAssociations of EventAssociationListing DefaultController', ['exception' => $e]);
            }
        }
    }
}
