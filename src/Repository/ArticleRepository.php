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

    public function findBySlug(string $slug): ?Article
    {
        return $this->findOneBy(['slug' => $slug, 'isPublished' => true]);
    }

    /**
     * @return Article[]
     */
    public function findPublishedArticles(bool $premiumOnly = false): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.isPublished = :isPublished')
            ->setParameter('isPublished', true);

        if ($premiumOnly) {
            $qb->andWhere('a.isPremium = :isPremium')
               ->setParameter('isPremium', true);
        }

        return $qb->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[]
     */
    public function findFreeArticles(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublished = :isPublished')
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
            ->where('a.isPublished = :isPublished')
            ->andWhere('a.isPremium = :isPremium')
            ->setParameter('isPublished', true)
            ->setParameter('isPremium', true)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[]
     */
    public function findRecentArticles(int $limit = 5, bool $premiumOnly = false): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.isPublished = :isPublished')
            ->setParameter('isPublished', true);

        if ($premiumOnly) {
            $qb->andWhere('a.isPremium = :isPremium')
               ->setParameter('isPremium', true);
        }

        return $qb->orderBy('a.publishedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[]
     */
    public function findByAuthor(User $author): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.author = :author')
            ->andWhere('a.isPublished = :isPublished')
            ->setParameter('author', $author)
            ->setParameter('isPublished', true)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[]
     */
    public function findByTag(string $tag): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublished = :isPublished')
            ->andWhere('JSON_CONTAINS(a.tags, :tag) = 1')
            ->setParameter('isPublished', true)
            ->setParameter('tag', json_encode($tag))
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Article[]
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isPublished = :isPublished')
            ->andWhere('a.title LIKE :query OR a.content LIKE :query OR a.excerpt LIKE :query')
            ->setParameter('isPublished', true)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countPublishedArticles(bool $premiumOnly = false): int
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.isPublished = :isPublished')
            ->setParameter('isPublished', true);

        if ($premiumOnly) {
            $qb->andWhere('a.isPremium = :isPremium')
               ->setParameter('isPremium', true);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return string[]
     */
    public function findAllTags(): array
    {
        $result = $this->createQueryBuilder('a')
            ->select('a.tags')
            ->where('a.isPublished = :isPublished')
            ->setParameter('isPublished', true)
            ->getQuery()
            ->getArrayResult();

        $tags = [];
        foreach ($result as $row) {
            if (is_array($row['tags'])) {
                $tags = array_merge($tags, $row['tags']);
            }
        }

        return array_unique($tags);
    }
}