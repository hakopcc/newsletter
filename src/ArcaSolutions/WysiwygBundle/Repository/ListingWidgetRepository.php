<?php

namespace ArcaSolutions\WysiwygBundle\Repository;

use ArcaSolutions\WysiwygBundle\Entity\ListingWidget;
use Doctrine\ORM\EntityRepository;

/**
 * Class ListingWidgetRepository
 * @package ArcaSolutions\WysiwygBundle\Repository
 */
class ListingWidgetRepository extends EntityRepository
{
    /**
     * @param $type
     * @param $section
     * @return array
     */
    public function findAllGrouped($type, $section)
    {
        return $this->createQueryBuilder('lw')
            ->select('lw')
            ->where('lw.type = :type')
            ->andWhere('lw.section = :section')
            ->setParameter('type', $type)
            ->setParameter('section', $section)
            ->getQuery()
            ->getArrayResult();
    }
}
