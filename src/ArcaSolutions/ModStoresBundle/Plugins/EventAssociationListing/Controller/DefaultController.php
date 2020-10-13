<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\EventAssociationListing\Controller;

use ArcaSolutions\EventBundle\Entity\Internal\EventLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Repository\ListingRepository;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DefaultController extends Controller
{
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
     * @param DoctrineRegistry $doctrine
     * @param QueryBuilder $queryBuilder
     * @param array $eventLevels
     * @param Comparison|Func|Orx|Composite|string $extraWhereCondition
     * @param array $extraParameters
     * @param string $eventEntityAlias
     */
    private function injectFromJoinAndWhereInEventQueryBuilder($accountId, $listingId, DoctrineRegistry $doctrine, QueryBuilder &$queryBuilder, &$eventLevels, $extraWhereCondition=null, $extraParameters=array(), $eventEntityAlias = 'event'): void
    {
        $eventLevelEntities = (EventLevelFeatures::getAllLevelsAndNormalize($doctrine));
        $eventLevels = array();
        /** @var EventLevelFeatures $eventLevelEntity */
        foreach ($eventLevelEntities as $level => $eventLevelEntity) {
            $eventLevels[] = $level;
        }
        if(!empty($eventEntityAlias) && $queryBuilder !== null) {
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
                    if(empty($eventLevels)){
                        $whereCondition = $queryBuilder->expr()->eq($eventEntityAlias . '.accountId', ':accountId');
                    } else {
                        $whereCondition = $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->eq($eventEntityAlias . '.accountId', ':accountId'),
                            $queryBuilder->expr()->in($eventEntityAlias . '.level',$eventLevels)
                        );
                    }
                    $queryBuilder = $queryBuilder->leftJoin('EventAssociationListingBundle:EventAssociated', 'event_associated', Join::WITH, $leftJoinCondition);
                    if($whereCondition!==null) {
                        if($extraWhereCondition!==null){
                            $this->injectExtraWhereCondition($queryBuilder, $whereCondition,$extraWhereCondition);
                        }
                        $queryBuilder = $queryBuilder->where($whereCondition);
                    }
                    if(!empty($extraParameters)){
                        array_merge($queryBuilderParameters, $extraParameters);
                    }
                    $queryBuilder = $queryBuilder->setParameters($queryBuilderParameters);
                }
                unset($leftJoinCondition, $queryBuilderParameters);
            } else {
                if(empty($eventLevels)){
                    $whereCondition = $queryBuilder->expr()->isNull($eventEntityAlias . '.accountId');
                } else {
                    $whereCondition = $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->isNull($eventEntityAlias . '.accountId'),
                        $queryBuilder->expr()->in($eventEntityAlias . '.level',$eventLevels)
                    );
                }
                if($whereCondition!==null) {
                    if($extraWhereCondition!==null){
                        $this->injectExtraWhereCondition($queryBuilder, $whereCondition,$extraWhereCondition);
                    }
                    $queryBuilder = $queryBuilder->where($whereCondition);
                    if(!empty($extraParameters)){
                        $queryBuilder = $queryBuilder->setParameters($extraParameters);
                    }
                }
            }
        }
    }

    /**
     * @param $request
     * @param $listingId
     * @param $accountId
     * @param ListingRepository $listingRepository
     * @return bool
     */
    private function tryToGetAccountIdFromListingAndCheckAuthorization($request, $listingId, &$accountId, ListingRepository $listingRepository, $isLoggedInAsSponsor = false)
    {
        if ($listingId !== null) {
            /** @var Listing $listing */
            $listing = $listingRepository->find($listingId);
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
        return $isLoggedIn && ($accountId !== null || $isLoggedInAsSiteManager);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function listEventAction(Request $request)
    {
        $returnValue = new JsonResponse(['error' => 'Unexpected empty content'], 500);
        /** @var Logger $logger */
        $logger = $this->container->get('logger');
        try {
            if($request!==null && $request->query!==null) {
                $accountId = null;
                $listingId = null;
                if(!$this->getAccountIdAndListinIdFromSessionAndRequest($request,$accountId,$listingId)) {
                    $returnValue = new JsonResponse(['error' => 'Invalid request'], 400);
                }else {
                    /** @var DoctrineRegistry $doctrine */
                    $doctrine = $this->container->get('doctrine');
                    if ($doctrine !== null) {
                        /** @var ListingRepository $listingRepository */
                        $listingRepository = $doctrine->getRepository('ListingBundle:Listing');
                        if ($listingRepository !== null) {
                            if (!$this->tryToGetAccountIdFromListingAndCheckAuthorization($request, $listingId, $accountId, $listingRepository)) {
                                $returnValue = new JsonResponse(['error' => 'Unauthorized request'], 301);
                            } else {
                                /** @var ObjectManager $em */
                                $em = $doctrine->getManager();
                                if ($em !== null) {
                                    /** @var QueryBuilder $queryBuilder */
                                    $queryBuilder = $em->createQueryBuilder();
                                    $queryBuilder = $queryBuilder->select('event.id AS id, event.title AS title');
                                    $extraWhereCondition = null;
                                    $extraWhereParameters = array();
                                    if ($request->query !== null) {
                                        if ($request->query->has('query')) {
                                            $queryFromRequest = $request->query->get('query');
                                            if(!empty($queryFromRequest)) {
                                                $extraWhereCondition = $queryBuilder->expr()->like('event.title', ':query');
                                                $extraWhereParameters['query'] = '%' . $queryFromRequest . '%';
                                            }
                                        }
                                    }
                                    $this->injectFromJoinAndWhereInEventQueryBuilder($accountId, $listingId, $doctrine, $queryBuilder, $eventLevels, $extraWhereCondition, $extraWhereParameters);
                                    if (empty($eventLevels)) {
                                        $returnValue = new JsonResponse(['data' => []]);
                                    } else {
                                        $queryBuilder = $queryBuilder->orderBy('title');
                                        $query = $queryBuilder->getQuery();
                                        $query->setMaxResults(1000);
                                        $queryResult = $query->getResult();

                                        if (empty($queryResult)) {
                                            $returnValue = new JsonResponse(['data' => []]);
                                        }
                                        $response = ['data' => []];
                                        foreach ($queryResult as $event) {
                                            $response['data'][] = [
                                                'label' => $event['title'],
                                                'id' => $event['id'],
                                            ];
                                        }
                                        $returnValue = new JsonResponse($response);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $logger->critical('Unexpected error in listEventAction of EventAssociationListing DefaultController', ['exception' => $e]);
            $returnValue = new JsonResponse(['error' => 'Unexpected error'], 500);
        } finally {
            unset($logger);
        }
        return $returnValue;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function listListingAction(Request $request)
    {
        $em = $this->container->get('doctrine')->getManager();
        $connection = $em->getConnection();

        $response = ['error' => 'Not Found'];

        if (!$this->container->get('request_stack')->getCurrentRequest()->getSession()->get('SM_LOGGEDIN') &&
            !$this->container->get('request_stack')->getCurrentRequest()->getSession()->get('SESS_ACCOUNT_ID')) {
            return new JsonResponse($response);
        }

        $queryParams = [];

        $accountId = (int)$request->query->get('accountId', 0);

        $listingLevels = ListingLevelFeatures::getAllLevelsAndNormalize($this->container->get('doctrine'));

        foreach ($listingLevels as $listingLevel)
        {
            if ($listingLevel->eventsCount > 0)
            {
                $eventLevels[] = $listingLevel->level;
            }
        }

        if(empty($eventLevels)){
            echo json_encode([]);
            exit;
        }

        $eventLevels = implode(',', $eventLevels);

        $where = sprintf(' level IN (%s) ', $eventLevels);

        if ((int)$accountId > 0) {
            // with account
            $where .= ' AND account_id = '.$accountId;
        } else {
            $where .= ' AND (account_id = 0 OR account_id IS NULL) ';
        }

        if(!empty($_GET['query'])){
            $where .= " AND Listing.`title` LIKE '%".$_GET['query']."%' ";
        }

        $query = "
            SELECT id, title
            FROM Listing
            WHERE {$where}
            ORDER BY title
            LIMIT 1000;
        ";

        $statement = $connection->prepare($query);

        foreach ($queryParams as $key => $value) {
            $statement->bindValue($key, $value);
        }

        $statement->execute();
        $result = $statement->fetchAll();

        if (empty($result)) {
            return new JsonResponse($response);
        }

        $response = [];
        foreach ($result as $listing) {
            $response[] = [
                'title' => $listing['title'],
                'id'    => $listing['id'],
            ];
        }

        return new JsonResponse($response);
    }

}
