<?php

namespace ArcaSolutions\ImportBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * ImportLogRepository
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @since 11.3.00
 * @package ArcaSolutions\ImportBundle\Repository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ImportLogRepository extends EntityRepository
{
    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $importId
     * @return mixed
     */
    public function getImportLogStatus($importId)
    {
        try {
            return $this->createQueryBuilder('il')
                ->select('il.status')
                ->where('il.id = :importId')
                ->setParameter('importId', $importId)
                ->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            return null;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
