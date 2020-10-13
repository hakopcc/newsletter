<?php

namespace ArcaSolutions\CoreBundle\Repository;

use ArcaSolutions\CoreBundle\Interfaces\EntityLocationRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * BaseModule
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EntityLocationRepository extends EntityRepository implements EntityLocationRepositoryInterface
{
    /**
     * @var int 20
     */
    protected $max_items = 20;

    /**
     * {@inheritdoc}
     */
    public function getFeaturedLocationsByLevel($location_level = null)
    {
        if (is_null($location_level)) {
            throw new \Exception('You must pass location level');
        }

        return $this->createQueryBuilder('q')
            ->select(array(
                'q.title, q.fullFriendlyUrl as friendly_url'
            ))
            ->where('q.locationLevel = :level')
            ->andWhere('q.count > :count')
            ->setParameter('count', 0)
            ->setParameter('level', $location_level)
            ->orderBy('q.count', 'DESC')
            ->setMaxResults($this->max_items)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function getMaxItems()
    {
        return $this->max_items;
    }

    /**
     * @param mixed $max_items
     *
     * @return $this
     */
    public function setMaxItems($max_items)
    {
        if (is_int($max_items)) {
            $this->max_items = $max_items;
        }

        return $this;
    }
}