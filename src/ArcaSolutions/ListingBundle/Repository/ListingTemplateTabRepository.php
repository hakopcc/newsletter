<?php

namespace ArcaSolutions\ListingBundle\Repository;


use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class ListingTemplateTabRepository
 * @package ArcaSolutions\ListingBundle\Repository
 */
class ListingTemplateTabRepository extends EntityRepository
{
    /**
     * @param $listingTemplate
     * @return array
     * @throws NonUniqueResultException
     */
    public function getLastOrder(ListingTemplate $listingTemplate)
    {
        $qb = $this->createQueryBuilder('tab')
            ->select('tab.order')
            ->where('tab.listingTemplateId = :listingTemplateId')
            ->setParameter('listingTemplateId', $listingTemplate->getId())
            ->orderBy('tab.order','DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return array
     * @throws NonUniqueResultException
     */
    public function getLastId()
    {
        $qb = $this->createQueryBuilder('tab')
            ->select('tab.id')
            ->orderBy('tab.id','DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
