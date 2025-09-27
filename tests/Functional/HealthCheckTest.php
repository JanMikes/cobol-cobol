<?php

namespace App\Tests\Functional;

class HealthCheckTest extends BaseTestCase
{
    public function testLivenessHealthCheckReturns200(): void
    {
        $this->client->request('GET', '/-/health-check/liveness');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('ok', $data['status']);
        $this->assertArrayHasKey('timestamp', $data);
    }
}