<?php

namespace App\Repository;

use App\Entity\OnCall;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OnCall|null find($id, $lockMode = null, $lockVersion = null)
 * @method OnCall|null findOneBy(array $criteria, array $orderBy = null)
 * @method OnCall[]    findAll()
 * @method OnCall[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OnCallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OnCall::class);
    }

    public function findOnCallByWeek(Carbon $date)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.startDate <= :date')
            ->andWhere('o.endDate >= :date')
            ->setParameter('date', $date->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    // /**
    //  * @return OnCall[] Returns an array of OnCall objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OnCall
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
