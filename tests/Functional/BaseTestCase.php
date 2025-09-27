<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    protected function loginUser(string $email): User
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $this->assertNotNull($user, "User with email {$email} not found");

        $this->client->loginUser($user);
        return $user;
    }

    protected function assertPageAccessible(string $path): void
    {
        $this->client->request('GET', $path);
        $this->assertResponseIsSuccessful("Page {$path} should be accessible");
    }

    protected function assertPageRedirectsToLogin(string $path): void
    {
        $this->client->request('GET', $path);
        $this->assertResponseRedirects(null, 302, "Page {$path} should redirect");
        $location = $this->client->getResponse()->headers->get('Location');
        $this->assertNotNull($location, "Location header should be present");
        $this->assertStringContainsString('/login', $location, "Page {$path} should redirect to login");
    }

    protected function assertPageForbidden(string $path): void
    {
        $this->client->request('GET', $path);
        $this->assertResponseStatusCodeSame(403, "Page {$path} should return 403 Forbidden");
    }
}