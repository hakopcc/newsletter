<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\AdvancedReviewListing\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class ReviewRatingTypeRepository extends EntityRepository
{
    /**
     * @param $id
     * @param $typeId
     * @return mixed
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getListingRatingByTypeId($id, $typeId)
    {
        $qb = $this->createQueryBuilder('rr');

        /*
         * Conditions
         */
        $qb->select('AVG(rr.value) as rating')
            ->innerJoin('WebBundle:Review', 'r', 'WITH', 'r.id = rr.reviewId')
            ->andWhere('rr.ratingId = :type_id')
            ->andWhere('r.itemId = :item_id')
            ->andWhere('r.itemType = :item_type')
            ->andWhere('r.approved = :approved');

        /*
         * Parameters
         */
        $qb->setParameter('item_id', $id);
        $qb->setParameter('item_type', 'listing');
        $qb->setParameter('type_id', $typeId);
        $qb->setParameter('approved', '1');

        return $qb->getQuery()->getSingleScalarResult();
    }
}
