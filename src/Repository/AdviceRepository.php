<?php

namespace App\Repository;

use App\Entity\Advice;
use App\Enum\Month;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advice>
 */
class AdviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advice::class);
    }

    public function findByMonthWithPagination(Month $month, int $page, int $limit): Paginator
    {
        $query = $this->getQueryBuilderToFindByMonth($month)
            ->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new \Doctrine\ORM\Tools\Pagination\Paginator($query);
    }

        /**
         * @return Advice[] Returns an array of Advice objects
         */
        protected function getQueryBuilderToFindByMonth(Month $month): QueryBuilder
        {
            return $this->createQueryBuilder('a')
                ->innerJoin('a.months', 'm')
                ->andWhere('m.numberInYear = :val')
                ->setParameter('val', $month->value)
                ->orderBy('a.id', 'ASC')
            ;
        }

    //    /**
    //     * @return Advice[] Returns an array of Advice objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Advice
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
