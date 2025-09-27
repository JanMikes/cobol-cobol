<?php

namespace App\Tests\Functional;

class AuthenticatedAccessTest extends BaseTestCase
{
    /**
     * Test that authenticated users can access their allowed pages
     */
    public function testAuthenticatedUserCanAccessAllowedPages(): void
    {
        $this->loginUser('user@cobolcobol.com');

        $allowedRoutes = [
            '/',                    // Home page
            '/pricing',            // Pricing page
            '/articles',          // Articles list
            '/dashboard',         // User dashboard
        ];

        foreach ($allowedRoutes as $route) {
            $this->assertPageAccessible($route);
        }
    }

    /**
     * Test that authenticated users can access free articles
     */
    public function testAuthenticatedUserCanAccessFreeArticles(): void
    {
        $this->loginUser('user@cobolcobol.com');

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
     * Test that non-subscriber users cannot access premium content
     */
    public function testNonSubscriberCannotAccessPremiumContent(): void
    {
        $this->loginUser('user@cobolcobol.com');

        $premiumRoutes = [
            '/premium',                                      // Premium articles page
            '/articles/advanced-features-for-power-users',  // Premium article
            '/articles/building-scalable-saas-applications', // Premium article
            '/articles/stripe-integration-guide',           // Premium article
        ];

        foreach ($premiumRoutes as $route) {
            $this->assertPageForbidden($route);
        }
    }

    /**
     * Test that admin users can access basic pages but not premium without subscription
     */
    public function testAdminUserBasicAccess(): void
    {
        $this->loginUser('admin@cobolcobol.com');

        $allowedRoutes = [
            '/',                    // Home page
            '/pricing',            // Pricing page
            '/articles',          // Articles list
            '/dashboard',         // Dashboard
        ];

        foreach ($allowedRoutes as $route) {
            $this->assertPageAccessible($route);
        }

        // Admin still needs subscription for premium content (business rule)
        $premiumRoutes = [
            '/premium',
            '/articles/advanced-features-for-power-users',
        ];

        foreach ($premiumRoutes as $route) {
            $this->assertPageForbidden($route);
        }
    }
}