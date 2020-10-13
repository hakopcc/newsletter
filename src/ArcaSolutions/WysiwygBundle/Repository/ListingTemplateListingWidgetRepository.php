<?php

namespace ArcaSolutions\WysiwygBundle\Repository;

use ArcaSolutions\WysiwygBundle\Entity\PageWidget;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class ListingTemplateListingWidgetRepository
 * @package ArcaSolutions\WysiwygBundle\Repository
 */
class ListingTemplateListingWidgetRepository extends EntityRepository
{
    /**
     * @param $listingTemplateId
     * @return mixed
     * @throws NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function findLastOrder($listingTemplateId)
    {
        $lastOrder = $this->createQueryBuilder('ltlw')
            ->select('Max(ltlw.order)')
            ->where('ltlw.listingTemplateId = :listingTemplateId')
            ->setParameter('listingTemplateId', $listingTemplateId)
            ->getQuery()
            ->getSingleScalarResult();

        return $lastOrder ? ++$lastOrder : 1;
    }

    /**
     * @param $listingTemplateId
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findByListingTemplateIdOrderedByOrder($listingTemplateId)
    {
        return $this->createQueryBuilder('ltlw')
            ->select('ltlw')
            ->where('ltlw.listingTemplateId = :listingTemplateId')
            ->setParameter('listingTemplateId', $listingTemplateId)
            ->orderBy('ltlw.order')
            ->getQuery()
            ->getResult();
    }
}
