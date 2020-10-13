<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\TailoredMapListing\Search;

use ArcaSolutions\CoreBundle\Search\BaseConfiguration;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\SearchEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TailoredMapListingConfiguration extends BaseConfiguration
{
    /**
     * @var string|null
     */
    public static $elasticType = 'listing';

    /**
     * @var string
     */
    protected $moduleUrlName;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->moduleUrlName = $container->getParameter('alias_listing_module');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'search.tailoredplacement.map' => 'registerMapItem',
        ];
    }

    public function registerMapItem(SearchEvent $searchEvent)
    {
        $this->register($searchEvent);
        $qB = SearchEngine::getElasticaQueryBuilder();

        $options = $searchEvent->getOptions();

        $avoid_ids = isset($options['avoid_ids']) ? $options['avoid_ids'] : [];

        $this->setElasticaQuery(
            $qB->query()->filtered(
                $this->createDefaultSearchQuery(),
                $qB->filter()->bool()
                    ->addMust($qB->filter()->type(self::$elasticType))
                    ->addMust($qB->filter()->term()->setTerm('status', true))
                    ->addMustNot($qB->filter()->terms()->setTerms('_id', $avoid_ids))
            )
        );
    }
}