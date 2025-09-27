<?php

namespace App\Tests\Functional;

class PublicAccessTest extends BaseTestCase
{
    /**
     * Test that public pages are accessible to guests
     */
    public function testPublicPagesAccessible(): void
    {
        $publicRoutes = [
            '/',                    // Home page
            '/pricing',            // Pricing page
            '/articles',          // Articles list (shows free articles only)
            '/login',             // Login page
            '/register',          // Registration page
        ];

        foreach ($publicRoutes as $route) {
            $this->assertPageAccessible($route);
        }
    }

    /**
     * Test that free articles are accessible to guests
     */
    public function testFreeArticlesAccessible(): void
    {
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
     * Test that premium content redirects guests to login
     */
    public function testPremiumContentRedirectsToLogin(): void
    {
        $premiumRoutes = [
            '/premium',                                      // Premium articles page
            '/articles/advanced-features-for-power-users',  // Premium article
            '/articles/building-scalable-saas-applications', // Premium article
            '/articles/stripe-integration-guide',           // Premium article
        ];

        foreach ($premiumRoutes as $route) {
            $this->assertPageRedirectsToLogin($route);
        }
    }

    /**
     * Test that protected pages redirect guests to login
     */
    public function testProtectedPagesRedirectToLogin(): void
    {
        $protectedRoutes = [
            '/dashboard',
            '/account/manage-subscription',
        ];

        foreach ($protectedRoutes as $route) {
            $this->assertPageRedirectsToLogin($route);
        }
    }
}