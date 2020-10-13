<?php


namespace ArcaSolutions\ModStoresBundle\Plugins\ArticleAssociationListing\Services;

use ArcaSolutions\ArticleBundle\Entity\Article;
use ArcaSolutions\ArticleBundle\Entity\Internal\ArticleLevelFeatures;
use ArcaSolutions\CoreBundle\Services\LanguageHandler;
use ArcaSolutions\CoreBundle\Services\Settings as MainSettings;
use ArcaSolutions\ModStoresBundle\Plugins\ArticleAssociationListing\Entity\ArticleAssociated;
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
use Symfony\Component\ExpressionLanguage\Tests\Node\Obj;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

class ArticleAssociationService
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
     * @param array $articleLevels
     * @param Comparison|Func|Orx|Composite|string $extraWhereCondition
     * @param array $extraParameters
     * @param string $articleEntityAlias
     * @return bool
     */
    private function injectFromJoinAndWhereInArticleQueryBuilder($accountId, $listingId, QueryBuilder &$queryBuilder, &$articleLevels, $extraWhereCondition=null, $extraParameters=array(), $articleEntityAlias = 'article')
    {
        $returnValue = false;
        $doctrineRef = &$this->getDoctrine();
        if($doctrineRef!==null) {
            $returnValue = true;
            $articleLevelEntities = (ArticleLevelFeatures::getAllLevelsAndNormalize($doctrineRef));
            $articleLevels = array();
            /** @var ArticleLevelFeatures $articleLevelEntity */
            foreach ($articleLevelEntities as $level => $articleLevelEntity) {
                $articleLevels[] = (string) $level;
            }
            if (!empty($articleEntityAlias) && $queryBuilder !== null) {
                $queryBuilder = $queryBuilder->from('ArticleBundle:Article', $articleEntityAlias);
                $whereCondition = null;
                if ($accountId !== null) {
                    $leftJoinCondition = null;
                    $queryBuilderParameters = array('accountId' => $accountId);
                    if ($listingId !== null) {
                        $leftJoinCondition = $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->eq('article_associated.article', $articleEntityAlias),
                            $queryBuilder->expr()->neq('article_associated.listingId', ':listingId')
                        );
                        $queryBuilderParameters['listingId'] = $listingId;
                    } else {
                        $leftJoinCondition = $queryBuilder->expr()->eq('article_associated.article', $articleEntityAlias);
                    }
                    if ($leftJoinCondition !== null) {
                        if (empty($articleLevels)) {
                            $whereCondition = $queryBuilder->expr()->eq($articleEntityAlias . '.accountId', ':accountId');
                        } else {
                            $whereCondition = $queryBuilder->expr()->andX(
                                $queryBuilder->expr()->eq($articleEntityAlias . '.accountId', ':accountId'),
                                $queryBuilder->expr()->in($articleEntityAlias . '.level', $articleLevels)
                            );
                        }
                        $queryBuilder = $queryBuilder->leftJoin('ArticleAssociationListingBundle:ArticleAssociated', 'article_associated', Join::WITH, $leftJoinCondition);
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
                    if (empty($articleLevels)) {
                        $whereCondition = $queryBuilder->expr()->isNull($articleEntityAlias . '.accountId');
                    } else {
                        $whereCondition = $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->isNull($articleEntityAlias . '.accountId'),
                            $queryBuilder->expr()->in($articleEntityAlias . '.level', $articleLevels)
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
    public function getFormListingArticle()
    {
        $returnValue = 'Unexpected content';
        $allArticlesAvailable = false;
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
                    $availableArticleCount = 0;
                    $translatorRef = &$this->getTranslator();
                    if ($translatorRef!==null) {
                        $error = $translatorRef->trans('Unable to acquire available articles for association with this Listing due to an internal unexpected behavior.', array(), 'messages', $lang);
                    } else {
                        $error = 'Unable to acquire available articles for association with this Listing due to an internal unexpected behavior.';
                    }
                    $isLoggedInAsSponsor = false;
                    if ($this->getAccountIdAndListinIdFromSessionAndRequest($request, $accountId, $listingId, $isLoggedInAsSponsor)) {
                        $authorized = false;
                        if ($this->tryToGetAccountIdFromListingAndCheckAuthorization($request, $listingId, $accountId, $authorized, $isLoggedInAsSponsor)) {
                            if ($authorized) {
                                /** @var ObjectManager $objectManagerRef */
                                $objectManagerRef = &$this->getObjectManager();
                                if ($objectManagerRef!==null) {
                                    $articleLevels = null;
                                    /** @var QueryBuilder $queryBuilder */
                                    $queryBuilder = $objectManagerRef->createQueryBuilder();
                                    $queryBuilder = $queryBuilder->select('count(article.id) AS availableCount');
                                    if ($this->injectFromJoinAndWhereInArticleQueryBuilder($accountId, $listingId, $queryBuilder, $articleLevels)) {
                                        if (empty($articleLevels)) {
                                            $allArticlesAvailable = false;
                                            $error = null;
                                        } else {
                                            $query = $queryBuilder->getQuery();
                                            $queryResult = $query->getSingleResult();
                                            $availableArticleCount = $queryResult['availableCount'];
                                            $allArticlesAvailable = $availableArticleCount > 0;
                                            $error = null;
                                        }
                                        unset($articleLevels, $queryBuilder, $query, $queryResult, $availableArticleCount);
                                    }
                                }
                            }
                        }
                        unset($authorized);
                    }
                    unset($availableArticleCount);
                }
            }
        } catch (Exception $e) {
            $loggerRef = &$this->getLogger();
            if($loggerRef!==null) {
                $loggerRef->critical('Unexpected error in getFormListingArticle of ArticleAssociationService.php', ['exception' => $e]);
            }
        } finally {
            try {
                $twigEnvironmentRef = &$this->getTwigEnvironment();
                if($twigEnvironmentRef!==null) {
                    $returnValue = $twigEnvironmentRef->render('ArticleAssociationListingBundle::form-sitemgr-listing.html.twig', [
                        'lang' => $lang,
                        'error' => $error,
                        'listingId' => $listingId,
                        'accountId' => $accountId,
                        'allArticlesAvailable' => $allArticlesAvailable
                    ]);
                }
            } catch (Exception $e){
                $loggerRef = &$this->getLogger();
                if($loggerRef!==null) {
                    $loggerRef->critical('Unexpected error on try to render ArticleAssociationListingBundle::form-sitemgr-listing.html.twig in getFormListingArticle of ArticleAssociationService.php', ['exception' => $e]);
                }
            }
            unset($listing, $allArticlesAvailable, $error, $lang,
                $availableArticleCount, $accountId, $listingId,
                $queryBuilder, $query,$queryResult, $availableArticleCount);
        }
        return $returnValue;
    }

    /**
     * @return string
     */
    public function saveListingArticleTab()
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
                            $articles = $request->request->get('article_attached', array());
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
                                        /** @var EntityRepository $articleAssociatedRepository */
                                        $articleAssociatedRepository = $doctrineRef->getRepository('ArticleAssociationListingBundle:ArticleAssociated');
                                        $articleRepository = $doctrineRef->getRepository('ArticleBundle:Article');
                                        if ($articleAssociatedRepository !== null && $articleRepository !== null) {
                                            /** @var ArticleAssociated[] $associations */
                                            $associations = $articleAssociatedRepository->findBy([
                                                'listingId' => $listingId,
                                            ]);
                                            $needFlush = false;
                                            foreach ($associations as $association) {
                                                /** @var Article $associatedArticle */
                                                $associatedArticle = $association->getArticle();
                                                if ($associatedArticle !== null) {
                                                    $associatedArticleId = $associatedArticle->getId();
                                                    if (!empty($associatedArticleId)) {
                                                        if (($key = array_search($associatedArticleId, $articles, true)) !== false) {
                                                            unset($articles[$key]);//if exists on database, remove from article collection that will be used to made new associations
                                                        } else {
                                                            $objectManagerRef->remove($association);//if exists on database but do not exists on article collection, remove from database (the association has been deleted)
                                                            $needFlush = true;
                                                        }
                                                    }
                                                    unset($associatedArticleId);
                                                } else {
                                                    $objectManagerRef->remove($association);
                                                    $needFlush = true;
                                                }
                                                unset($associatedArticle);
                                            }

                                            $articleAssociationErrors = array();
                                            foreach ($articles as $articleId) {
                                                /** @var Article $article */
                                                $article = $articleRepository->find($articleId);
                                                if ($article !== null) {
                                                    $articleAccountId = $article->getAccountId();
                                                    if(empty($articleAccountId)){
                                                        $articleAccountId = null;
                                                    }
                                                    if ($articleAccountId === $accountId) {
                                                        $existentArticleAssociationCount = $articleAssociatedRepository->count([
                                                            'article' => $article,
                                                        ]);
                                                        if ($existentArticleAssociationCount === 0) {
                                                            $articleAssociation = new ArticleAssociated();
                                                            $articleAssociation->setArticle($article);
                                                            $articleAssociation->setListing($listingId);
                                                            $objectManagerRef->persist($articleAssociation);
                                                            unset($articleAssociation);
                                                            $needFlush = true;
                                                        }
                                                        unset($existentArticleAssociationCount);
                                                    } else {
                                                        $articleTitle = $article->getTitle();
                                                        $articleAssociationError = 'Article "' . $articleTitle . '" need to have the same owner of the listing.';
                                                        if ($translatorRef !== null) {
                                                            $notifySuccessStringFormat = $translatorRef->trans('Article {0} need to have the same owner of the listing.', array(), 'messages', $lang);
                                                            $notifySuccessString = sprintf($notifySuccessStringFormat, $articleTitle);
                                                        }
                                                        $articleAssociationErrors[] = $articleAssociationError;
                                                        unset($articleTitle, $articleAssociationError);
                                                    }
                                                    unset($articleAccountId);
                                                }
                                                unset($article);
                                            }
                                            if ($needFlush) {
                                                $objectManagerRef->flush();
                                                $notifySuccessString = 'Listing article association updated successfully.';
                                                if ($translatorRef !== null) {
                                                    $notifySuccessString = $translatorRef->trans('Listing article association updated successfully.', array(), 'messages', $lang);
                                                }
                                            }
                                            if (!empty($articleAssociationErrors)) {
                                                $notifyErrorString = '<ul><li>' . implode('</li><li>', $articleAssociationErrors) . '</li></ul>';
                                            }
                                            unset($needFlush, $associations, $articleAssociationErrors);
                                        }
                                        unset($articleRepository, $articleAssociatedRepository);
                                    }
                                }
                            }
                            unset($articles, $listingId, $accountId);
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
                $loggerRef->critical('Unexpected error in saveListingArticleTab of ArticleAssociationService.php', ['exception' => $e]);
            }
        } finally {
            try {
                $twigRef = &$this->getTwigEnvironment();
                if($twigRef!==null) {
                    $returnValue = $twigRef->render('ArticleAssociationListingBundle:js:legacy-listing-article-js.html.twig', ['error' => $notifyErrorString, 'success' => $notifySuccessString]);
                }
            } catch (Exception $e) {
                $returnValue = '<script type="text/javascript">document.addEventListener(\'DOMContentLoaded\', function() {$(document).ready(function () {notify.error(\'' . $unexpectedErrorMessage . '\', \'\', {fadeOut: 0});});});</script>';
                $loggerRef = &$this->getLogger();
                if($loggerRef!==null) {
                    $loggerRef->critical('Unexpected error on try to render ArticleAssociationListingBundle:js:legacy-listing-article-js.html.twig in saveListingArticleTab of ArticleAssociationService.php', ['exception' => $e]);
                }
            }
        }
        return $returnValue;
    }

    public function updateArticleAssociations($listingId)
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
                                /** @var EntityRepository $articleAssociatedRepository */
                                $articleAssociatedRepository = $doctrineRef->getRepository('ArticleAssociationListingBundle:ArticleAssociated');
                                $articleRepository = $doctrineRef->getRepository('ArticleBundle:Article');
                                if ($articleAssociatedRepository !== null && $articleRepository !== null) {
                                    /** @var ArticleAssociated[] $associations */
                                    $associations = $articleAssociatedRepository->findBy([
                                        'listingId' => $listingId,
                                    ]);
                                    $needFlush = false;
                                    foreach ($associations as $association) {
                                        /** @var Article $associatedArticle */
                                        $associatedArticle = $association->getArticle();
                                        if ($associatedArticle !== null) {
                                            $associatedArticleAccountId = $associatedArticle->getAccountId();
                                            if($listingAccountId!==$associatedArticleAccountId){
                                                $objectManagerRef->remove($association);
                                                $needFlush = true;
                                            }
                                        } else {
                                            $objectManagerRef->remove($association);
                                            $needFlush = true;
                                        }
                                        unset($associatedArticle);
                                    }
                                    if ($needFlush) {
                                        $objectManagerRef->flush();
                                    }
                                }
                                unset($articleRepository, $articleAssociatedRepository, $listingAccountId);
                            }
                            unset($listing);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $loggerRef = &$this->getLogger();
            if($loggerRef!==null) {
                $loggerRef->critical('Unexpected error in updateArticleAssociations of ArticleAssociationListing DefaultController', ['exception' => $e]);
            }
        }
    }
}
