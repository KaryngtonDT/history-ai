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
        self::assertArrayHasKey('coreRuntime', $payload);
        self::assertArrayHasKey('extensions', $payload);
        self::assertArrayHasKey('premium', $payload);

        $client->request('GET', '/api/runtime/report/'.$payload['pipelineId']);
        self::assertResponseIsSuccessful();
    }

    public function testCapabilityMaturityListsThirtyThreeEngines(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/runtime/capabilities/maturity');

        self::assertResponseIsSuccessful();

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(33, $payload['totalEngines']);
        self::assertIsArray($payload['capabilities']);
        self::assertCount(10, $payload['capabilities']);
    }

    public function testRuntimeDashboardAggregatesLiveRuntimeData(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/runtime/dashboard');

        self::assertResponseIsSuccessful();

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Lumen Runtime Health', $payload['title']);
        self::assertArrayHasKey('overallRuntimeScore', $payload);
        self::assertArrayHasKey('platformHealth', $payload);
        self::assertArrayHasKey('scoreModel', $payload);
        self::assertArrayHasKey('platformScore', $payload);
        self::assertArrayHasKey('summary', $payload);
        self::assertArrayHasKey('capabilityStatuses', $payload);
        self::assertArrayHasKey('hardware', $payload);
        self::assertArrayHasKey('shadowCommentary', $payload);
        self::assertIsArray($payload['capabilityStatuses']);
        self::assertGreaterThanOrEqual(10, count($payload['capabilityStatuses']));
    }

    public function testRuntimeCompletionPlanUsesDashboard(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/runtime/completion/plan');

        self::assertResponseIsSuccessful();

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Runtime Completion Plan', $payload['title']);
        self::assertFalse($payload['hardwareRedetected']);
        self::assertArrayHasKey('compatibleEngineCompletionPlan', $payload);
        self::assertArrayHasKey('capabilities', $payload);
    }
}
