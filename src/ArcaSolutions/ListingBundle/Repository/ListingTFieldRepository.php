<?php

namespace ArcaSolutions\ListingBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class ListingTFieldRepository
 * @package ArcaSolutions\ListingBundle\Repository
 */
class ListingTFieldRepository extends EntityRepository
{
    /**
     * @param $templateId
     * @param string $returnType
     * @return array|mixed
     */
    public function getTemplateCustomFields($templateId, $returnType = 'array')
    {
        $qb = $this->createQueryBuilder('ltf')
            ->select('ltf')
            ->where('ltf.listingTemplateId = :templateId')
            ->andWhere('ltf.fieldType != :fieldType')
            ->setParameter('templateId', $templateId)
            ->setParameter('fieldType', 'default');

        if ($returnType === 'array') {
            return $qb->getQuery()->getArrayResult();
        }

        if ($returnType === 'object') {
            return $qb->getQuery()->getResult();
        }

        return null;
    }

    /**
     * @param $templateId
     * @return array|int|string
     */
    public function getTemplateCustomFieldGroups($templateId)
    {
        $qb = $this->createQueryBuilder('ltf')
            ->select('ltfg')
            ->where('ltf.listingTemplateId = :templateId')
            ->innerJoin('ListingBundle:ListingTFieldGroup', 'ltfg', 'WITH', 'ltfg.id = ltf.groupId' )
            ->setParameter('templateId', $templateId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return int|mixed|string
     */
    public function getAllCustomFields()
    {
        $qb = $this->createQueryBuilder('ltf')
            ->select('ltf')
            ->where('ltf.fieldType != :fieldType')
            ->setParameter('fieldType', 'default');

        return $qb->getQuery()->getResult();
    }
}
