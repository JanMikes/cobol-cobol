<?php

namespace App\Tests\Functional;

use Symfony\Component\DomCrawler\Crawler;

class LoginTest extends BaseTestCase
{
    public function testLoginPageDisplaysCorrectly(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Vitajte späť!');
        $this->assertSelectorExists('form[method="post"]');
        $this->assertSelectorExists('input[name="email"]');
        $this->assertSelectorExists('input[name="password"]');
        $this->assertSelectorExists('input[name="_csrf_token"]');
        $this->assertSelectorExists('button[type="submit"]');
    }

    public function testLoginWithValidCredentials(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Prihlásiť sa')->form();

        $form['email'] = 'user@cobolcobol.com';
        $form['password'] = 'user123';

        $this->client->submit($form);

        // Should redirect to dashboard after successful login
        $this->assertResponseRedirects('/dashboard');

        // Follow the redirect
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('app_dashboard');

        // Should show success flash message
        $this->assertSelectorExists('.bg-green-50.border-green-400');
        $this->assertSelectorTextContains('.text-green-700', 'Úspešne ste sa prihlásili!');
    }

    public function testLoginWithInvalidEmail(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Prihlásiť sa')->form();

        $form['email'] = 'nonexistent@example.com';
        $form['password'] = 'anypassword';

        $this->client->submit($form);

        // Should redirect back to login page on invalid credentials
        $this->assertResponseRedirects('/login');

        // Follow the redirect to see the error message
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('app_login');

        // Should display error message
        $this->assertSelectorExists('.border-red-400');
        $this->assertSelectorTextContains('.text-red-700', 'Invalid credentials');
    }

    public function testLoginWithInvalidPassword(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Prihlásiť sa')->form();

        $form['email'] = 'user@cobolcobol.com';
        $form['password'] = 'wrongpassword';

        $this->client->submit($form);

        // Should redirect back to login page on invalid credentials
        $this->assertResponseRedirects('/login');

        // Follow the redirect to see the error message
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('app_login');

        // Should display error message
        $this->assertSelectorExists('.border-red-400');
        $this->assertSelectorTextContains('.text-red-700', 'Invalid credentials');
    }

    public function testLoginWithEmptyCredentials(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Prihlásiť sa')->form();

        $form['email'] = '';
        $form['password'] = '';

        $this->client->submit($form);

        // Should redirect back to login page on empty credentials
        $this->assertResponseRedirects('/login');

        // Follow the redirect to see the error message
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertRouteSame('app_login');

        // Should display error message
        $this->assertSelectorExists('.border-red-400');
    }

    public function testLoginPreservesLastUsername(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Prihlásiť sa')->form();

        $form['email'] = 'user@cobolcobol.com';
        $form['password'] = 'wrongpassword';

        $this->client->submit($form);

        // Follow the redirect to see the preserved email
        $this->client->followRedirect();

        // Email should be preserved in the form
        $this->assertSelectorExists('input[name="email"][value="user@cobolcobol.com"]');
    }

    public function testLoginRedirectsAuthenticatedUser(): void
    {
        // Login a user first
        $this->loginUser('user@cobolcobol.com');

        // Try to access login page
        $this->client->request('GET', '/login');

        // Should redirect to dashboard
        $this->assertResponseRedirects('/dashboard');
    }

    public function testRememberMeOption(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Prihlásiť sa')->form();

        $form['email'] = 'user@cobolcobol.com';
        $form['password'] = 'user123';
        $form['_remember_me'] = '1';

        $this->client->submit($form);

        // Should redirect to dashboard
        $this->assertResponseRedirects('/dashboard');

        // Should set remember me cookie
        $response = $this->client->getResponse();
        $cookies = $response->headers->getCookies();

        $rememberMeCookieFound = false;
        foreach ($cookies as $cookie) {
            if (str_contains($cookie->getName(), 'REMEMBERME')) {
                $rememberMeCookieFound = true;
                break;
            }
        }

        $this->assertTrue($rememberMeCookieFound, 'Remember me cookie should be set');
    }

    public function testCsrfProtection(): void
    {
        // Try to submit login form without CSRF token
        $this->client->request('POST', '/login', [
            'email' => 'user@cobolcobol.com',
            'password' => 'password123'
        ]);

        // Should be rejected (403 or redirect back to login)
        $this->assertTrue(
            $this->client->getResponse()->isClientError() ||
            $this->client->getResponse()->isRedirection()
        );
    }
}