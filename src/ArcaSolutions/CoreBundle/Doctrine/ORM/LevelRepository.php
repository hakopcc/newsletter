<?php

namespace ArcaSolutions\CoreBundle\Doctrine\ORM;


use Doctrine\ORM\EntityRepository;

/**
 * Class LevelRepository
 * @package ArcaSolutions\CoreBundle\Doctrine\ORM
 */
class LevelRepository extends EntityRepository
{
    /**
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findLowerLevel()
    {
        $query =  $this->createQueryBuilder('lv')
            ->select('lv.value')
            ->addSelect('CASE WHEN lv.price > 0 THEN lv.price ELSE lv.priceYearly END AS ORD')
            ->where('lv.popular = :popular')
            ->orderBy('ORD', 'ASC')
            ->setParameter('popular', 'n')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }
}
