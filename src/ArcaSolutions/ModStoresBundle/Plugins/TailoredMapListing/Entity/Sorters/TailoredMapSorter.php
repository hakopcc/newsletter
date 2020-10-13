<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\TailoredMapListing\Entity\Sorters;

use ArcaSolutions\SearchBundle\Entity\Sorters\BaseSorter;
use Elastica\Query;

class TailoredMapSorter extends BaseSorter
{
    protected static $name = 'tailoredmap';

    /**
     * Sets events to listening
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'search.tailoredplacement.map' => 'register',
        ];
    }

    /**
     * Sets sort elastic query for date
     *
     * @param Query $query
     */
    public function sort(Query $query)
    {
        $query->setParam('track_scores', true);
        $query->setSort([
            '_score' => ['order' => 'desc'],
            'level'  => [
                'order'         => 'asc',
                'unmapped_type' => 'integer',
            ],
        ]);
    }
}
