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
    public function findActivePlans(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.priceMonthly', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByStripeProductId(string $stripeProductId): ?Plan
    {
        return $this->findOneBy(['stripeProductId' => $stripeProductId]);
    }

    public function findByStripePriceId(string $stripePriceId): ?Plan
    {
        return $this->createQueryBuilder('p')
            ->where('p.stripePriceMonthlyId = :priceId OR p.stripePriceYearlyId = :priceId')
            ->setParameter('priceId', $stripePriceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Plan[]
     */
    public function findForPricingPage(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.priceMonthly', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCheapestPlan(): ?Plan
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('p.priceMonthly', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}