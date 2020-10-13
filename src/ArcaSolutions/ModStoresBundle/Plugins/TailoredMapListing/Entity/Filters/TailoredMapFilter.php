<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\TailoredMapListing\Entity\Filters;

use ArcaSolutions\SearchBundle\Entity\Filters\BaseFilter;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\SearchEngine;

class TailoredMapFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    protected static $name = 'BoundingFilter';

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'search.tailoredplacement.map' => 'registerBoundMapItem',
        ];
    }

    /**
     * Provides the necessary elasticsearch queries and filters for a geo bounding box search
     *
     * @param SearchEvent $searchEvent
     * @param $eventName
     */
    public function registerBoundMapItem(SearchEvent $searchEvent, $eventName)
    {
        $coordinates = $searchEvent->getOptions();

        if (isset($coordinates['top_left']) && isset($coordinates['bottom_right'])) {

            $this->register($searchEvent, $eventName);
            $qb = SearchEngine::getElasticaQueryBuilder();

            $this->addElasticaFilter(
                $qb->filter()->geo_bounding_box(
                    'geoLocation',
                    [
                        [
                            'lat' => $coordinates['top_left'][0],
                            'lon' => $coordinates['top_left'][1],
                        ],
                        [
                            'lat' => $coordinates['bottom_right'][0],
                            'lon' => $coordinates['bottom_right'][1],
                        ],
                    ]
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterView()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function processAggregationResults($aggregationResults)
    {
        /* This filter has no aggregations */
    }

    /**
     * {@inheritdoc}
     */
    protected function processAggregationBuckets($filterAggregationBuckets)
    {
        /* This filter has no buckets. */
    }
}