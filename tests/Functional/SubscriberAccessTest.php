<?php

namespace App\Tests\Functional;

use App\Entity\Subscription;

class SubscriberAccessTest extends BaseTestCase
{
    private function createActiveSubscription(): void
    {
        // Get the subscriber user
        $user = $this->userRepository->findOneBy(['email' => 'subscriber@cobolcobol.com']);
        $this->assertNotNull($user, 'Subscriber user not found');

        // Create an active subscription if one doesn't exist
        if (!$user->hasActiveSubscription()) {
            $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
            $planRepository = static::getContainer()->get('App\Repository\PlanRepository');

            // Get a plan for the subscription
            $plan = $planRepository->findOneBy([]);
            $this->assertNotNull($plan, 'No plan found in fixtures');

            $subscription = new Subscription();
            $subscription->setUser($user)
                ->setPlan($plan)
                ->setStripeSubscriptionId('sub_test_123')
                ->setStatus(Subscription::STATUS_ACTIVE)
                ->setCurrentPeriodStart(new \DateTimeImmutable('-1 month'))
                ->setCurrentPeriodEnd(new \DateTimeImmutable('+1 month'));

            $entityManager->persist($subscription);
            $entityManager->flush();

            // Refresh the user entity to ensure the subscription is loaded
            $entityManager->refresh($user);
        }
    }

    /**
     * Test that subscribers can access all pages
     */
    public function testSubscriberCanAccessAllPages(): void
    {
        $this->createActiveSubscription();
        $user = $this->loginUser('subscriber@cobolcobol.com');

        $allRoutes = [
            '/',                    // Home page
            '/pricing',            // Pricing page
            '/articles',          // Articles list
            '/dashboard',         // Dashboard
            '/premium',           // Premium articles page
        ];

        foreach ($allRoutes as $route) {
            $this->assertPageAccessible($route);
        }
    }

    /**
     * Test that subscribers can access free articles
     */
    public function testSubscriberCanAccessFreeArticles(): void
    {
        $this->createActiveSubscription();
        $user = $this->loginUser('subscriber@cobolcobol.com');

        $freeArticleRoutes = [
            '/articles/getting-started-with-cobolcobol',
            '/articles/best-practices-for-user-authentication',
            '/articles/database-design-patterns',
        ];

        foreach ($freeArticleRoutes as $route) {
            $this->assertPageAccessible($route);
        }
    }

    /**
     * Test that subscribers can access premium articles
     */
    public function testSubscriberCanAccessPremiumArticles(): void
    {
        $this->createActiveSubscription();
        $user = $this->loginUser('subscriber@cobolcobol.com');

        $premiumArticleRoutes = [
            '/articles/advanced-features-for-power-users',
            '/articles/building-scalable-saas-applications',
            '/articles/stripe-integration-guide',
        ];

        foreach ($premiumArticleRoutes as $route) {
            $this->assertPageAccessible($route);
        }
    }

    /**
     * Test that subscriber has the correct roles
     */
    public function testSubscriberHasCorrectRoles(): void
    {
        $this->createActiveSubscription();
        $user = $this->loginUser('subscriber@cobolcobol.com');

        $this->assertTrue($user->hasActiveSubscription());
        $this->assertContains('ROLE_SUBSCRIBER', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }
}