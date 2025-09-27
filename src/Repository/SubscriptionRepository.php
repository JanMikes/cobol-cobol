<?php

namespace App\Repository;

use App\Entity\Subscription;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscription>
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    public function findByStripeSubscriptionId(string $stripeSubscriptionId): ?Subscription
    {
        return $this->findOneBy(['stripeSubscriptionId' => $stripeSubscriptionId]);
    }

    public function findActiveSubscriptionForUser(User $user): ?Subscription
    {
        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->andWhere('s.status IN (:activeStatuses)')
            ->setParameter('user', $user)
            ->setParameter('activeStatuses', Subscription::ACTIVE_STATUSES)
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Subscription[]
     */
    public function findActiveSubscriptions(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.status IN (:activeStatuses)')
            ->setParameter('activeStatuses', Subscription::ACTIVE_STATUSES)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Subscription[]
     */
    public function findExpiringSoon(int $days = 7): array
    {
        $expiryDate = new \DateTimeImmutable(sprintf('+%d days', $days));

        return $this->createQueryBuilder('s')
            ->where('s.status IN (:activeStatuses)')
            ->andWhere('s.currentPeriodEnd <= :expiryDate')
            ->setParameter('activeStatuses', Subscription::ACTIVE_STATUSES)
            ->setParameter('expiryDate', $expiryDate)
            ->orderBy('s.currentPeriodEnd', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Subscription[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countActiveSubscriptions(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.status IN (:activeStatuses)')
            ->setParameter('activeStatuses', Subscription::ACTIVE_STATUSES)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Subscription[]
     */
    public function findRecentSubscriptions(int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}