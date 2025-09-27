<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription
{
    public const STATUS_INCOMPLETE = 'incomplete';
    public const STATUS_INCOMPLETE_EXPIRED = 'incomplete_expired';
    public const STATUS_TRIALING = 'trialing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PAUSED = 'paused';

    public const ACTIVE_STATUSES = [
        self::STATUS_TRIALING,
        self::STATUS_ACTIVE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private Plan $plan;

    #[ORM\Column(length: 255, nullable: false)]
    private string $stripeSubscriptionId;

    #[ORM\Column(length: 255, nullable: false)]
    private string $status;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $currentPeriodStart = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $currentPeriodEnd = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $trialStart = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $trialEnd = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $canceledAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $cancelAtPeriodEnd = null;

    #[ORM\Column(nullable: false)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: false)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getPlan(): Plan
    {
        return $this->plan;
    }

    public function setPlan(Plan $plan): static
    {
        $this->plan = $plan;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getStripeSubscriptionId(): string
    {
        return $this->stripeSubscriptionId;
    }

    public function setStripeSubscriptionId(string $stripeSubscriptionId): static
    {
        $this->stripeSubscriptionId = $stripeSubscriptionId;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    public function isPastDue(): bool
    {
        return $this->status === self::STATUS_PAST_DUE;
    }

    public function isTrialing(): bool
    {
        return $this->status === self::STATUS_TRIALING;
    }

    public function getCurrentPeriodStart(): ?\DateTimeImmutable
    {
        return $this->currentPeriodStart;
    }

    public function setCurrentPeriodStart(?\DateTimeImmutable $currentPeriodStart): static
    {
        $this->currentPeriodStart = $currentPeriodStart;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCurrentPeriodEnd(): ?\DateTimeImmutable
    {
        return $this->currentPeriodEnd;
    }

    public function setCurrentPeriodEnd(?\DateTimeImmutable $currentPeriodEnd): static
    {
        $this->currentPeriodEnd = $currentPeriodEnd;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getTrialStart(): ?\DateTimeImmutable
    {
        return $this->trialStart;
    }

    public function setTrialStart(?\DateTimeImmutable $trialStart): static
    {
        $this->trialStart = $trialStart;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getTrialEnd(): ?\DateTimeImmutable
    {
        return $this->trialEnd;
    }

    public function setTrialEnd(?\DateTimeImmutable $trialEnd): static
    {
        $this->trialEnd = $trialEnd;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCanceledAt(): ?\DateTimeImmutable
    {
        return $this->canceledAt;
    }

    public function setCanceledAt(?\DateTimeImmutable $canceledAt): static
    {
        $this->canceledAt = $canceledAt;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getCancelAtPeriodEnd(): ?\DateTimeImmutable
    {
        return $this->cancelAtPeriodEnd;
    }

    public function setCancelAtPeriodEnd(?\DateTimeImmutable $cancelAtPeriodEnd): static
    {
        $this->cancelAtPeriodEnd = $cancelAtPeriodEnd;
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

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_INCOMPLETE => 'Incomplete',
            self::STATUS_INCOMPLETE_EXPIRED => 'Incomplete (Expired)',
            self::STATUS_TRIALING => 'Trial',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PAST_DUE => 'Past Due',
            self::STATUS_CANCELED => 'Canceled',
            self::STATUS_UNPAID => 'Unpaid',
            self::STATUS_PAUSED => 'Paused',
            default => 'Unknown',
        };
    }

    public function getDaysUntilPeriodEnd(): ?int
    {
        if ($this->currentPeriodEnd === null) {
            return null;
        }

        $now = new \DateTimeImmutable();
        $interval = $now->diff($this->currentPeriodEnd);

        return $interval->invert === 0 ? (int) $interval->days : 0;
    }
}