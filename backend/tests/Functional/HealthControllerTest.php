<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HealthControllerTest extends WebTestCase
{
    public function testHealthReturnsOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        self::assertResponseIsSuccessful();
        self::assertJsonStringEqualsJsonString(
            '{"status":"ok"}',
            $client->getResponse()->getContent()
        );
    }

    public function testReadyReturnsStructuredPayload(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ready');

        self::assertContains($client->getResponse()->getStatusCode(), [200, 503]);
        $payload = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertContains($payload['status'] ?? null, ['ready', 'not_ready']);
        self::assertArrayHasKey('checks', $payload);
        self::assertTrue($payload['checks']['postgres']['ok'] ?? false);
        self::assertTrue($payload['checks']['storage']['ok'] ?? false);
    }

    public function testLiveReturnsStructuredPayload(): void
    {
        $client = static::createClient();
        $client->request('GET', '/live');

        self::assertContains($client->getResponse()->getStatusCode(), [200, 503]);
        $payload = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertContains($payload['status'] ?? null, ['live', 'degraded']);
        self::assertArrayHasKey('checks', $payload);
        self::assertArrayHasKey('disk', $payload['checks']);
    }

    public function testProductionReadinessReturnsScore(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/platform/readiness');

        self::assertResponseIsSuccessful();
        $payload = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertArrayHasKey('score', $payload);
        self::assertArrayHasKey('checks', $payload);
    }
}
