<?php

namespace ArcaSolutions\WebBundle\Repository;

use ArcaSolutions\WebBundle\Entity\Accountprofilecontact;
use ArcaSolutions\WebBundle\Entity\Review;
use ArcaSolutions\WebBundle\Entity\Setting;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Form\Form;

/**
 * FaqRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReviewRepository extends EntityRepository
{
    const REVIEWS_PER_PAGE = 10;

    /**
     * @param int $id
     * @param string $module
     * @return array
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTotalByItemId($id, $module)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->where('r.itemType = :module')
            ->andWhere('r.approved = :approved')
            ->andWhere('r.itemId = :id')
            ->setParameter('module', $module)
            ->setParameter('approved', 1)
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $id
     * @param string $module
     *
     * @return Review|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOneGoodReview($id, $module)
    {
        return $this->createQueryBuilder('r')
            ->where('r.itemType = :module')
            ->andWhere('r.approved = :approved')
            ->andWhere('r.itemId = :id')
            ->andWhere('r.review <> :empty')
            ->setParameter('module', $module)
            ->setParameter('approved', 1)
            ->setParameter('empty', '')
            ->setParameter('id', $id)
            ->orderBy('r.rating', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $id Item's ID
     * @param string $module Module's name
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAvgReviewByItemId($id, $module)
    {
        return $this->createQueryBuilder('r')
            ->select('avg(r.rating)')
            ->where('r.itemId = :id')
            ->andWhere('r.approved = :approved')
            ->andWhere('r.itemType = :module')
            ->setParameter('id', $id)
            ->setParameter('approved', 1)
            ->setParameter('module', $module)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param $itemId
     * @param $page
     * @return array
     */
    public function getReviewsPaginated($itemId, $page)
    {
        $query = $this->createQueryBuilder('r')
            ->select('r')
            ->where('r.itemId = :id')
            ->andWhere('r.approved = :approved')
            ->andWhere('r.itemType = :module')
            ->setParameter('id', $itemId)
            ->setParameter('approved', 1)
            ->setParameter('module', 'listing')
            ->orderBy('r.rating', 'DESC')
            ->orderBy('r.added', 'DESC')
            ->setFirstResult(($page - 1) * self::REVIEWS_PER_PAGE)
            ->setMaxResults(self::REVIEWS_PER_PAGE);

        $paginator = new Paginator($query, $fetchJoinCollection = true);

        return [
            'reviews' => $paginator,
            'total' => $paginator->count(),
            'pageCount' => ceil($paginator->count() / self::REVIEWS_PER_PAGE)
        ];
    }
}
