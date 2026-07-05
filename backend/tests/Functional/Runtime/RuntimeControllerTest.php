<?php

declare(strict_types=1);

namespace App\Tests\Functional\Runtime;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RuntimeControllerTest extends WebTestCase
{
    public function testRuntimeOverview(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/runtime');

        self::assertResponseIsSuccessful();

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('principle', $payload);
        self::assertArrayHasKey('status', $payload);
        self::assertArrayHasKey('health', $payload);
    }

    public function testRuntimeReadinessListsEngines(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/runtime/readiness');

        self::assertResponseIsSuccessful();

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('engines', $payload);
        self::assertIsArray($payload['engines']);
        self::assertNotSame([], $payload['engines']);
    }

    public function testPipelineValidationCreatesReport(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/runtime/pipeline/validate');

        self::assertResponseIsSuccessful();

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('pipelineId', $payload);
        self::assertArrayHasKey('steps', $payload);

        $client->request('GET', '/api/runtime/report/'.$payload['pipelineId']);
        self::assertResponseIsSuccessful();
    }
}
