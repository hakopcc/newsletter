<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\CommunityForum\Search;

use ArcaSolutions\CoreBundle\Search\BaseConfiguration;
use ArcaSolutions\CoreBundle\Services\Utility;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\SearchBlock;
use ArcaSolutions\SearchBundle\Services\SearchEngine;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuestionConfiguration extends BaseConfiguration
{
    /**
     * @var string|null
     */
    public static $elasticType = 'question';
    /**
     * @var string
     */
    protected $moduleUrlName;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->moduleUrlName = $container->getParameter('alias_forum_module');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $events = [
            'search.global' => 'registerItem',
            'popular.question'  => 'registerPopular',
            'recent.question'   => 'registerRecent',
            'question.card'     => 'registerCard',
        ];

        /* ModStores Hooks */
        HookFire("forumconfiguration_before_return_subscribers", [
            'events' => &$events,
        ]);

        return $events;
    }

    public function registerItem(SearchEvent $event)
    {
        if (array_key_exists(self::$elasticType, $this->searchEngine->getActiveModules())) {
            $this->register($event);
            $qB = SearchEngine::getElasticaQueryBuilder();

            if ($keyword = Utility::convertArrayToString($this->searchEvent->getKeyword())) {

                $query = $qB->query()->multi_match()
                    ->setQuery($keyword)
                    ->setTieBreaker(0.3)
                    ->setOperator('and')
                    ->setFields([
                        'friendlyUrl^200',
                        'title.raw^200',
                        'title.analyzed^10',
                        'description^5',
                        'searchInfo.keyword^1',
                    ]);

            }

            $filter = $qB->filter()->bool()
                ->addMust($qB->filter()->type(self::$elasticType))
                ->addMust($qB->filter()->term()->setTerm('status', true));


            /* ModStores Hooks */
            HookFire("forumconfiguration_before_setup_itemquery", [
                "that"        => &$this,
                "filter"      => &$filter,
                "query"       => &$query,
                "searchEvent" => &$event,
            ]);

            $finalQuery = $qB->query()->filtered($query, $filter);

            /* ModStores Hooks */
            HookFire("forumconfiguration_before_return_itemquery", [
                "that"        => &$this,
                "searchEvent" => &$event,
                "finalQuery"  => &$finalQuery,
            ]);

            $this->setElasticaQuery($finalQuery);
        }
    }

    /**
     * @param SearchEvent $searchEvent
     * @param bool $repeatableItem
     * @throws Exception
     */
    public function registerPopular(SearchEvent $searchEvent, $repeatableItem = false)
    {
        /* registers this event */
        $this->register($searchEvent);

        $qb = SearchEngine::getElasticaQueryBuilder();

        $searchEvent->setDefaultSorter($this->container->get('sorter.view'));

        $query = $qb->query()->match_all();

        $filter = $qb->filter()->bool()
            ->addMust($qb->filter()->term()->setTerm('status', true));

        if(!$repeatableItem) {
            $filter->addMustNot($qb->filter()->terms()->setTerms('_id', SearchBlock::$previousItems[self::$elasticType]));
        }
        /* ModStores Hooks */
        HookFire("forumconfiguration_before_setup_popularquery", [
            "that"        => &$this,
            "filter"      => &$filter,
            "query"       => &$query,
            "searchEvent" => &$searchEvent,
        ]);

        $finalQuery = $qb->query()->filtered($query, $filter);

        /* ModStores Hooks */
        HookFire("forumconfiguration_before_return_popularquery", [
            "that"        => &$this,
            "searchEvent" => &$searchEvent,
            "finalQuery"  => &$finalQuery,
        ]);

        $this->setElasticaQuery($finalQuery);
    }

    /**
     * @param SearchEvent $searchEvent
     *
     * @throws Exception
     */
    public function registerCard(SearchEvent $searchEvent)
    {
        /* registers this event */
        $this->register($searchEvent);

        $qb = SearchEngine::getElasticaQueryBuilder();

        $options = $searchEvent->getOptions();

        $mainQuery = $qb->query()->match_all();

        $filter =  $qb->filter()->bool();

        $filter->addMust($qb->filter()->term()->setTerm('status', true));

        if(empty($options['items'])) {
            if (!empty($options['custom']->level)) {
                $filter->addMust($qb->filter()->terms()->setTerms('level', (array)$options['custom']->level));
            }

            if(!array_key_exists(self::$elasticType,SearchBlock::$previousItems)){
                SearchBlock::$previousItems[self::$elasticType] = [];
            }

            $filter->addMustNot($qb->filter()->terms()->setTerms('_id', SearchBlock::$previousItems[self::$elasticType]));

            $searchEvent->setCardSorter([$options['custom']->order1, $options['custom']->order2]);

        } else {
            $filter->addMust($qb->filter()->terms()->setTerms('_id',$options['items']));
        }

        $mainQuery = $qb->query()->filtered($mainQuery, $filter);

        $this->setElasticaQuery($mainQuery);
    }

    /**
     * @param SearchEvent $searchEvent
     * @throws Exception
     */
    public function registerRecent(SearchEvent $searchEvent)
    {
        /* registers this event */
        $this->register($searchEvent);

        $qb = SearchEngine::getElasticaQueryBuilder();

        $searchEvent->setDefaultSorter($this->container->get('sorter.publicationdate'));

        $query = $qb->query()->match_all();

        $filter = $qb->filter()->bool()
            ->addMust($qb->filter()->term()->setTerm('status', true))
            ->addMustNot($qb->filter()->terms()->setTerms('_id', SearchBlock::$previousItems[self::$elasticType]));

        /* ModStores Hooks */
        HookFire("forumconfiguration_before_setup_recentquery", [
            "that"        => &$this,
            "filter"      => &$filter,
            "query"       => &$query,
            "searchEvent" => &$searchEvent,
        ]);

        $finalQuery = $qb->query()->filtered($query, $filter);

        /* ModStores Hooks */
        HookFire("forumconfiguration_before_return_recentquery", [
            "that"        => &$this,
            "searchEvent" => &$searchEvent,
            "finalQuery"  => &$finalQuery,
        ]);

        $this->setElasticaQuery($finalQuery);
    }
}
