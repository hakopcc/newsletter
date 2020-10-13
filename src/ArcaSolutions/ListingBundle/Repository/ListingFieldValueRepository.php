<?php

namespace ArcaSolutions\ListingBundle\Repository;

use ArcaSolutions\CoreBundle\Doctrine\ORM\LevelRepository;

/**
 * Class ListingLevelRepository
 * @package ArcaSolutions\ListingBundle\Repository
 */
class ListingFieldValueRepository extends LevelRepository
{
    /**
     * @param $level
     * @return array|int|string
     */
    public function getCheckBoxFieldValuesByListingId($listingId) {
        $qb = $this->createQueryBuilder('lfv')
            ->select('lfv')
            ->innerJoin('ListingBundle:ListingTField', 'ltf', 'WITH', 'ltf.id = lfv.listingTFieldId')
            ->where('ltf.fieldType = :checkbox')
            ->andWhere('lfv.listingId = :listingId')
            ->setParameter('listingId', $listingId)
            ->setParameter('checkbox', 'checkbox');

        return $qb->getQuery()->getResult();
    }
}
