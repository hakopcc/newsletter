<?php

namespace ArcaSolutions\ListingBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ListingLevelFieldRepository
 * @package ArcaSolutions\ListingBundle\Repository
 */
class ListingLevelFieldRepository extends EntityRepository
{
    /**
     * @param $level
     * @return mixed
     */
    public function getDealsCount($level) {
        $qb = $this->createQueryBuilder('llf')
            ->select('llf')
            ->where('llf.level = :level')
            ->setParameter('level', $level)
            ->andWhere('llf.quantity > 0')
            ->andWhere('llf.field = :deals')
            ->setParameter('deals', 'deals');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $level
     * @return mixed
     */
    public function getClassifiedsCount($level) {
        $qb = $this->createQueryBuilder('llf')
            ->select('llf')
            ->where('llf.level = :level')
            ->setParameter('level', $level)
            ->andWhere('llf.quantity > 0')
            ->andWhere('llf.field = :classifieds')
            ->setParameter('classifieds', 'classifieds');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $fieldName
     * @return array
     */
    public function getListingLevelsByFieldName($fieldName) {
        $qb = $this->createQueryBuilder('llf')
            ->select('l.name')
            ->innerJoin('ListingBundle:ListingLevel', 'l', 'WITH', 'l.value = llf.level' )
            ->where('llf.field = :field')
            ->andWhere("llf.quantity is NULL OR llf.quantity > 0")
            ->setParameter('field', $fieldName);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param $fieldName
     * @return array
     */
    public function getListingLevelsByFieldId($fieldId) {
        $qb = $this->createQueryBuilder('llf')
            ->select('l.name')
            ->innerJoin('ListingBundle:ListingLevel', 'l', 'WITH', 'l.value = llf.level')
            ->where('llf.listingTFieldId = :fieldId')
            ->setParameter('fieldId', $fieldId);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param $groupId
     * @return array
     */
    public function getListingLevelsByGroupId($groupId) {
        $qb = $this->createQueryBuilder('llf')
            ->select('l.name')
            ->innerJoin('ListingBundle:ListingLevel', 'l', 'WITH', 'l.value = llf.level')
            ->where('llf.listingTFieldGroupId = :groupId')
            ->setParameter('groupId', $groupId);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param $level
     * @return array
     */
    public function getListingLevelTemplateFieldIdsByLevel($level) {
        $qb = $this->createQueryBuilder('llf')
            ->select('llf.listingTFieldId')
            ->where('llf.level = :level')
            ->andWhere('llf.field = :custom')
            ->andWhere('llf.listingTFieldGroupId IS NULL')
            ->setParameter('level', $level)
            ->setParameter('custom', 'custom');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param $level
     * @return array
     */
    public function getListingLevelTemplateFieldGroupIdsByLevel($level) {
        $qb = $this->createQueryBuilder('llf')
            ->select('llf.listingTFieldGroupId')
            ->where('llf.level = :level')
            ->andWhere('llf.field = :custom')
            ->andWhere('llf.listingTFieldId IS NULL')
            ->setParameter('level', $level)
            ->setParameter('custom', 'custom');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param $level
     * @return array
     */
    public function getListingLevelFieldsNameByLevel($level) {
        $qb = $this->createQueryBuilder('llf')
            ->select('llf.field')
            ->where('llf.level = :level')
            ->andWhere("llf.quantity is NULL OR llf.quantity > 0")
            ->andWhere('llf.field != :custom')
            ->setParameter('level', $level)
            ->setParameter('custom', 'custom');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param $templateId
     * @return array|int|string
     */
    public function getListingLevelFieldIdsByTemplate($templateId) {
        $qb = $this->createQueryBuilder('llf')
            ->select('llf.id')
            ->innerJoin('ListingBundle:ListingTField', 'ltf', 'WITH', 'ltf.id = llf.listingTFieldId')
            ->where('ltf.listingTemplateId = :templateId')
            ->andWhere('llf.listingTFieldGroupId IS NULL')
            ->setParameter('templateId', $templateId);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param $templateId
     * @return array|int|string
     */
    public function getListingLevelFieldGroupIdsByTemplate($templateId) {
        $qb = $this->createQueryBuilder('llf')
            ->select('llf.id')
            ->innerJoin('ListingBundle:ListingTField', 'ltf', 'WITH', 'ltf.id = llf.listingTFieldId')
            ->where('ltf.listingTemplateId = :templateId')
            ->andWhere('llf.listingTFieldId IS NULL')
            ->setParameter('templateId', $templateId);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param $templateId
     * @param $level
     * @return array|int|string
     */
    public function getListingLevelFieldIdsByTemplateAndLevel($templateId, $level) {
        $qb = $this->createQueryBuilder('llf')
            ->select('llf.listingTFieldId')
            ->innerJoin('ListingBundle:ListingTField', 'ltf', 'WITH', 'ltf.id = llf.listingTFieldId')
            ->where('ltf.listingTemplateId = :templateId')
            ->andWhere('llf.listingTFieldGroupId IS NULL')
            ->andWhere('llf.level = :level')
            ->setParameter('templateId', $templateId)
            ->setParameter('level', $level);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param $templateId
     * @param $level
     * @return array|int|string
     */
    public function getListingLevelFieldGroupIdsByTemplateAndLevel($templateId, $level) {
        $qb = $this->createQueryBuilder('llf')
            ->select('llf.listingTFieldGroupId')
            ->innerJoin('ListingBundle:ListingTFieldGroup', 'ltfg', 'WITH', 'ltfg.id = llf.listingTFieldGroupId')
            ->innerJoin('ListingBundle:ListingTField', 'ltf', 'WITH', 'ltf.groupId = ltfg.id')
            ->where('ltf.listingTemplateId = :templateId')
            ->andWhere('llf.listingTFieldId IS NULL')
            ->andWhere('llf.level = :level')
            ->setParameter('templateId', $templateId)
            ->setParameter('level', $level);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param $templateFieldId
     * @param $level
     * @return int|mixed|string
     */
    public function getQuantityByTemplateFieldIdAndLevel($templateFieldId, $level) {
        $qb = $this->createQueryBuilder('llf')
            ->select('llf.quantity')
            ->where('llf.listingTFieldId = :templateFieldId')
            ->andWhere('llf.level = :level')
            ->setParameter('templateFieldId', $templateFieldId)
            ->setParameter('level', $level);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param $level
     * @return array|int|string
     */
    public function getListingLevelLinkedListingQuantityByLevel($level) {
        $qb = $this->createQueryBuilder('llf')
            ->select('llf.listingTFieldId as fieldId, llf.quantity as quantity')
            ->where('llf.level = :level')
            ->andWhere('llf.field = :custom')
            ->andWhere('llf.quantity IS NOT NULL')
            ->setParameter('level', $level)
            ->setParameter('custom', 'custom');

        return $qb->getQuery()->getArrayResult();
    }
}
