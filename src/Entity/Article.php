<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    private string $title;

    #[ORM\Column(length: 255, nullable: false)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $excerpt = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $content;

    #[ORM\Column]
    private bool $isPremium = false;

    #[ORM\Column]
    private bool $isPublished = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $author;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'json')]
    private array $tags = [];

    #[ORM\Column(nullable: true)]
    private ?string $metaDescription = null;

    #[ORM\Column(nullable: true)]
    private ?string $featuredImage = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): static
    {
        $this->excerpt = $excerpt;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isPremium(): bool
    {
        return $this->isPremium;
    }

    public function setPremium(bool $isPremium): static
    {
        $this->isPremium = $isPremium;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;
        $this->updatedAt = new \DateTimeImmutable();

        if ($isPublished && $this->publishedAt === null) {
            $this->publishedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): static
    {
        $this->author = $author;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array<string> $tags
     */
    public function setTags(array $tags): static
    {
        $this->tags = $tags;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): static
    {
        $this->metaDescription = $metaDescription;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getFeaturedImage(): ?string
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(?string $featuredImage): static
    {
        $this->featuredImage = $featuredImage;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        return max(1, (int) ceil($wordCount / 200));
    }

    public function getPreview(int $length = 150): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        $text = strip_tags($this->content ?? '');
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }
}