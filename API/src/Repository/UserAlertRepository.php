<?php

namespace App\Repository;

use App\Entity\Alert;
use App\Entity\UserAlert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserAlert|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAlert|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAlert[]    findAll()
 * @method UserAlert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAlert::class);
    }

    public function findRaisedUserAlerts()
    {
        $queryBuilder = $this->createQueryBuilder('ua');
        $lastAlertSent = new DateTime();
        $lastAlertSent->modify('+10 minutes');

        return $queryBuilder
            ->join(Alert::class, 'a', Join::WITH, $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('a', 'ua.alert'),
                $queryBuilder->expr()->eq('a.status', ':alertStatus')
            ))
            ->where($queryBuilder->expr()->isNull('ua.voiceSentAt'))
            ->andWhere($queryBuilder->expr()->gte('ua.smsSentAt', ':smsSentAt'))
            ->setParameter('alertStatus', 'raised')
            ->setParameter('smsSentAt', $lastAlertSent->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }
}
