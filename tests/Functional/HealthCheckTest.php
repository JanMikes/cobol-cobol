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
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('ok', $data['status']);
        $this->assertArrayHasKey('timestamp', $data);
    }
}