<?php

namespace App\Repository;

use App\Entity\Plan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plan>
 */
class PlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plan::class);
    }

    /**
     * @return Plan[]
     */
    public function findActiveOrderedBySortOrder(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByStripePriceId(string $stripePriceId): ?Plan
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stripePriceId = :stripePriceId')
            ->setParameter('stripePriceId', $stripePriceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByStripeProductId(string $stripeProductId): ?Plan
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stripeProductId = :stripeProductId')
            ->setParameter('stripeProductId', $stripeProductId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Plan[]
     */
    public function findByInterval(string $interval): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.interval = :interval')
            ->andWhere('p.isActive = :isActive')
            ->setParameter('interval', $interval)
            ->setParameter('isActive', true)
            ->orderBy('p.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}