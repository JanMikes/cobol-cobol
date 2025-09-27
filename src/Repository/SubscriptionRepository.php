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

    public function findOneByStripeSubscriptionId(string $stripeSubscriptionId): ?Subscription
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.stripeSubscriptionId = :stripeSubscriptionId')
            ->setParameter('stripeSubscriptionId', $stripeSubscriptionId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByStripeCustomerId(string $stripeCustomerId): ?Subscription
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.stripeCustomerId = :stripeCustomerId')
            ->setParameter('stripeCustomerId', $stripeCustomerId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveByUser(User $user): ?Subscription
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.user = :user')
            ->andWhere('s.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', ['active', 'trialing'])
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
            ->andWhere('s.status IN (:statuses)')
            ->setParameter('statuses', ['active', 'trialing'])
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Subscription[]
     */
    public function findExpiredSubscriptions(): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('s')
            ->andWhere('s.currentPeriodEnd < :now')
            ->andWhere('s.status = :status')
            ->setParameter('now', $now)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getResult();
    }
}