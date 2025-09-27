<?php

namespace App\Service;

use App\Entity\Plan;
use App\Entity\Subscription;
use App\Entity\User;
use App\Repository\PlanRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class SubscriptionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly PlanRepository $planRepository,
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function createSubscription(
        User $user,
        Plan $plan,
        string $stripeSubscriptionId,
        string $status,
        ?\DateTimeImmutable $currentPeriodStart = null,
        ?\DateTimeImmutable $currentPeriodEnd = null,
        ?\DateTimeImmutable $trialStart = null,
        ?\DateTimeImmutable $trialEnd = null
    ): Subscription {
        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->setPlan($plan);
        $subscription->setStripeSubscriptionId($stripeSubscriptionId);
        $subscription->setStatus($status);
        $subscription->setCurrentPeriodStart($currentPeriodStart);
        $subscription->setCurrentPeriodEnd($currentPeriodEnd);
        $subscription->setTrialStart($trialStart);
        $subscription->setTrialEnd($trialEnd);

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        $this->logger->info('Subscription created', [
            'user_id' => $user->getId(),
            'subscription_id' => $subscription->getId(),
            'stripe_subscription_id' => $stripeSubscriptionId,
            'status' => $status,
        ]);

        return $subscription;
    }

    public function updateSubscriptionFromStripe(\Stripe\Subscription $stripeSubscription): ?Subscription
    {
        $subscription = $this->subscriptionRepository->findByStripeSubscriptionId($stripeSubscription->id);

        if (!$subscription) {
            $this->logger->warning('Subscription not found for Stripe subscription', [
                'stripe_subscription_id' => $stripeSubscription->id,
            ]);
            return null;
        }

        $subscription->setStatus($stripeSubscription->status);
        $subscription->setCurrentPeriodStart(
            isset($stripeSubscription->current_period_start)
                ? new \DateTimeImmutable('@' . $stripeSubscription->current_period_start)
                : null
        );
        $subscription->setCurrentPeriodEnd(
            isset($stripeSubscription->current_period_end)
                ? new \DateTimeImmutable('@' . $stripeSubscription->current_period_end)
                : null
        );
        $subscription->setTrialStart(
            $stripeSubscription->trial_start
                ? new \DateTimeImmutable('@' . $stripeSubscription->trial_start)
                : null
        );
        $subscription->setTrialEnd(
            $stripeSubscription->trial_end
                ? new \DateTimeImmutable('@' . $stripeSubscription->trial_end)
                : null
        );
        $subscription->setCanceledAt(
            $stripeSubscription->canceled_at
                ? new \DateTimeImmutable('@' . $stripeSubscription->canceled_at)
                : null
        );
        $subscription->setCancelAtPeriodEnd(
            $stripeSubscription->cancel_at_period_end && isset($stripeSubscription->current_period_end)
                ? new \DateTimeImmutable('@' . $stripeSubscription->current_period_end)
                : null
        );

        $this->entityManager->flush();

        $this->logger->info('Subscription updated from Stripe', [
            'subscription_id' => $subscription->getId(),
            'stripe_subscription_id' => $stripeSubscription->id,
            'status' => $stripeSubscription->status,
        ]);

        return $subscription;
    }

    public function createSubscriptionFromStripeData(
        string $stripeCustomerId,
        \Stripe\Subscription $stripeSubscription
    ): ?Subscription {
        $user = $this->userRepository->findByStripeCustomerId($stripeCustomerId);
        if (!$user) {
            $this->logger->error('User not found for Stripe customer', [
                'stripe_customer_id' => $stripeCustomerId,
                'stripe_subscription_id' => $stripeSubscription->id,
            ]);
            return null;
        }

        // Get the price from the subscription to determine the plan
        $subscriptionItem = $stripeSubscription->items->data[0] ?? null;
        if (!$subscriptionItem) {
            $this->logger->error('No subscription items found', [
                'stripe_subscription_id' => $stripeSubscription->id,
            ]);
            return null;
        }

        $plan = $this->planRepository->findByStripePriceId($subscriptionItem->price->id);
        if (!$plan) {
            $this->logger->error('Plan not found for Stripe price', [
                'stripe_price_id' => $subscriptionItem->price->id,
                'stripe_subscription_id' => $stripeSubscription->id,
            ]);
            return null;
        }

        return $this->createSubscription(
            $user,
            $plan,
            $stripeSubscription->id,
            $stripeSubscription->status,
            isset($stripeSubscription->current_period_start)
                ? new \DateTimeImmutable('@' . $stripeSubscription->current_period_start)
                : null,
            isset($stripeSubscription->current_period_end)
                ? new \DateTimeImmutable('@' . $stripeSubscription->current_period_end)
                : null,
            $stripeSubscription->trial_start
                ? new \DateTimeImmutable('@' . $stripeSubscription->trial_start)
                : null,
            $stripeSubscription->trial_end
                ? new \DateTimeImmutable('@' . $stripeSubscription->trial_end)
                : null
        );
    }

    public function cancelSubscription(Subscription $subscription): void
    {
        $subscription->setStatus(Subscription::STATUS_CANCELED);
        $subscription->setCanceledAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        $this->logger->info('Subscription canceled', [
            'subscription_id' => $subscription->getId(),
            'stripe_subscription_id' => $subscription->getStripeSubscriptionId(),
        ]);
    }

    public function getUserActiveSubscription(User $user): ?Subscription
    {
        return $this->subscriptionRepository->findActiveSubscriptionForUser($user);
    }

    public function userHasActiveSubscription(User $user): bool
    {
        return $this->getUserActiveSubscription($user) !== null;
    }

    /**
     * @return array{active_count: int, recent_subscriptions: Subscription[]}
     */
    public function getSubscriptionStats(): array
    {
        $activeCount = $this->subscriptionRepository->countActiveSubscriptions();
        $recentSubscriptions = $this->subscriptionRepository->findRecentSubscriptions(5);

        return [
            'active_count' => $activeCount,
            'recent_subscriptions' => $recentSubscriptions,
        ];
    }
}