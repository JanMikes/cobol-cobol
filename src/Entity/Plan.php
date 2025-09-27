<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: false)]
    private int $priceMonthly;

    #[ORM\Column(nullable: true)]
    private ?int $priceYearly = null;

    #[ORM\Column(length: 255, nullable: false)]
    private string $stripeProductId;

    #[ORM\Column(length: 255, nullable: false)]
    private string $stripePriceMonthlyId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePriceYearlyId = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private int $sortOrder = 0;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'json')]
    private array $features = [];

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, Subscription>
     */
    #[ORM\OneToMany(targetEntity: Subscription::class, mappedBy: 'plan')]
    private Collection $subscriptions;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getPriceMonthly(): int
    {
        return $this->priceMonthly;
    }

    public function setPriceMonthly(int $priceMonthly): static
    {
        $this->priceMonthly = $priceMonthly;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getPriceYearly(): ?int
    {
        return $this->priceYearly;
    }

    public function setPriceYearly(?int $priceYearly): static
    {
        $this->priceYearly = $priceYearly;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getFormattedPriceMonthly(): string
    {
        return '$' . number_format($this->priceMonthly / 100, 2);
    }

    public function getFormattedPriceYearly(): string
    {
        if ($this->priceYearly === null) {
            return '';
        }

        return '$' . number_format($this->priceYearly / 100, 2);
    }

    public function getYearlyDiscount(): ?int
    {
        if ($this->priceYearly === null) {
            return null;
        }

        $monthlyYearlyPrice = $this->priceMonthly * 12;
        return (int) round((($monthlyYearlyPrice - $this->priceYearly) / $monthlyYearlyPrice) * 100);
    }

    public function getStripeProductId(): string
    {
        return $this->stripeProductId;
    }

    public function setStripeProductId(string $stripeProductId): static
    {
        $this->stripeProductId = $stripeProductId;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getStripePriceMonthlyId(): string
    {
        return $this->stripePriceMonthlyId;
    }

    public function setStripePriceMonthlyId(string $stripePriceMonthlyId): static
    {
        $this->stripePriceMonthlyId = $stripePriceMonthlyId;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getStripePriceYearlyId(): ?string
    {
        return $this->stripePriceYearlyId;
    }

    public function setStripePriceYearlyId(?string $stripePriceYearlyId): static
    {
        $this->stripePriceYearlyId = $stripePriceYearlyId;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getFeatures(): array
    {
        return $this->features;
    }

    /**
     * @param array<string> $features
     */
    public function setFeatures(array $features): static
    {
        $this->features = $features;
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

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setPlan($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): static
    {
        if ($this->subscriptions->removeElement($subscription)) {
            if ($subscription->getPlan() === $this) {
                /** @phpstan-ignore-next-line */
                $subscription->setPlan(null);
            }
        }

        return $this;
    }
}