<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * @return Article[]
     */
    public function findPublishedOrderedByDate(bool $premiumOnly = false): array
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :isPublished')
            ->setParameter('isPublished', true)
            ->orderBy('a.publishedAt', 'DESC');

        if ($premiumOnly) {
            $qb->andWhere('a.isPremium = :isPremium')
               ->setParameter('isPremium', true);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Article[]
     */
    public function findPublicArticles(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :isPublished')
            ->andWhere('a.isPremium = :isPremium')
            ->setParameter('isPublished', true)
            ->setParameter('isPremium', false)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[]
     */
    public function findPremiumArticles(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :isPublished')
            ->andWhere('a.isPremium = :isPremium')
            ->setParameter('isPublished', true)
            ->setParameter('isPremium', true)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.slug = :slug')
            ->andWhere('a.isPublished = :isPublished')
            ->setParameter('slug', $slug)
            ->setParameter('isPublished', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Article[]
     */
    public function findByAuthor(User $author): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.author = :author')
            ->setParameter('author', $author)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[]
     */
    public function findRecentPublic(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :isPublished')
            ->andWhere('a.isPremium = :isPremium')
            ->setParameter('isPublished', true)
            ->setParameter('isPremium', false)
            ->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}