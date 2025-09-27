<?php

namespace App\Service;

use App\Entity\Plan;
use App\Entity\User;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private StripeClient $stripe;

    public function __construct(
        #[Autowire('%env(STRIPE_SECRET_KEY)%')] private readonly string $secretKey,
        #[Autowire('%env(STRIPE_PUBLIC_KEY)%')] private readonly string $publicKey,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
        Stripe::setApiKey($this->secretKey);
        $this->stripe = new StripeClient($this->secretKey);
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function createCustomer(User $user): Customer
    {
        try {
            return $this->stripe->customers->create([
                'email' => $user->getEmail(),
                'name' => $user->getFullName(),
                'metadata' => [
                    'user_id' => (string) $user->getId(),
                ],
            ]);
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Failed to create Stripe customer: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getOrCreateCustomer(User $user): Customer
    {
        if ($user->getStripeCustomerId()) {
            try {
                return $this->stripe->customers->retrieve($user->getStripeCustomerId());
            } catch (ApiErrorException $e) {
                // Customer not found, create a new one
            }
        }

        return $this->createCustomer($user);
    }

    public function createCheckoutSession(
        User $user,
        Plan $plan,
        bool $isYearly = false,
        ?string $successUrl = null,
        ?string $cancelUrl = null
    ): Session {
        $customer = $this->getOrCreateCustomer($user);

        $priceId = $isYearly && $plan->getStripePriceYearlyId()
            ? $plan->getStripePriceYearlyId()
            : $plan->getStripePriceMonthlyId();

        if (!$priceId) {
            throw new \InvalidArgumentException('Price ID not found for plan');
        }

        $successUrl = $successUrl ?? $this->urlGenerator->generate('app_subscription_success', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $cancelUrl ?? $this->urlGenerator->generate('app_pricing', [], UrlGeneratorInterface::ABSOLUTE_URL);

        try {
            return $this->stripe->checkout->sessions->create([
                'customer' => $customer->id,
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price' => $priceId,
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'subscription',
                'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
                'allow_promotion_codes' => true,
                'billing_address_collection' => 'auto',
                'metadata' => [
                    'user_id' => (string) $user->getId(),
                    'plan_id' => (string) $plan->getId(),
                ],
            ]);
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Failed to create checkout session: ' . $e->getMessage(), 0, $e);
        }
    }

    public function createCustomerPortalSession(User $user, ?string $returnUrl = null): \Stripe\BillingPortal\Session
    {
        if (!$user->getStripeCustomerId()) {
            throw new \InvalidArgumentException('User does not have a Stripe customer ID');
        }

        $returnUrl = $returnUrl ?? $this->urlGenerator->generate('app_dashboard', [], UrlGeneratorInterface::ABSOLUTE_URL);

        try {
            return $this->stripe->billingPortal->sessions->create([
                'customer' => $user->getStripeCustomerId(),
                'return_url' => $returnUrl,
            ]);
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Failed to create customer portal session: ' . $e->getMessage(), 0, $e);
        }
    }

    public function retrieveSubscription(string $subscriptionId): \Stripe\Subscription
    {
        try {
            return $this->stripe->subscriptions->retrieve($subscriptionId);
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Failed to retrieve subscription: ' . $e->getMessage(), 0, $e);
        }
    }

    public function cancelSubscription(string $subscriptionId, bool $atPeriodEnd = true): \Stripe\Subscription
    {
        try {
            if ($atPeriodEnd) {
                return $this->stripe->subscriptions->update($subscriptionId, [
                    'cancel_at_period_end' => true,
                ]);
            } else {
                return $this->stripe->subscriptions->cancel($subscriptionId);
            }
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Failed to cancel subscription: ' . $e->getMessage(), 0, $e);
        }
    }

    public function retrieveCheckoutSession(string $sessionId): Session
    {
        try {
            return $this->stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['subscription', 'customer'],
            ]);
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Failed to retrieve checkout session: ' . $e->getMessage(), 0, $e);
        }
    }

    public function retrievePrice(string $priceId): Price
    {
        try {
            return $this->stripe->prices->retrieve($priceId);
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Failed to retrieve price: ' . $e->getMessage(), 0, $e);
        }
    }

    public function constructWebhookEvent(string $payload, string $signature, string $webhookSecret): \Stripe\Event
    {
        try {
            return \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            throw new \RuntimeException('Invalid payload: ' . $e->getMessage(), 0, $e);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            throw new \RuntimeException('Invalid signature: ' . $e->getMessage(), 0, $e);
        }
    }
}