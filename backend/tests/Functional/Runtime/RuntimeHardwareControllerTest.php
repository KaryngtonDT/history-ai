<?php

declare(strict_types=1);

namespace App\Tests\Functional\Runtime;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RuntimeHardwareControllerTest extends WebTestCase
{
    public function testHardwareEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/runtime/hardware');

        self::assertResponseIsSuccessful();

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('profile', $payload);
        self::assertArrayHasKey('capabilities', $payload);
        self::assertArrayHasKey('recommendedPipeline', $payload);
    }

    public function testCompatibilityEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/runtime/compatibility');

        self::assertResponseIsSuccessful();

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('engines', $payload);
        self::assertArrayHasKey('blockedByHardware', $payload);
        self::assertArrayHasKey('readyNow', $payload);
    }

    public function testLatentSyncBlockedReasonEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/runtime/engines/latentsync/blocked-reason');

        self::assertResponseIsSuccessful();

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('latentsync', $payload['engineId']);
        self::assertArrayHasKey('humanReason', $payload);
        self::assertArrayHasKey('missingRequirements', $payload);
        self::assertArrayHasKey('recommendedAlternative', $payload);
    }

    public function testProvisioningPlanEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/runtime/hardware');
        self::assertResponseIsSuccessful();

        $client->request('GET', '/api/runtime/provision/plan');
        self::assertResponseIsSuccessful();

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('toProvision', $payload);
        self::assertArrayHasKey('skipped', $payload);
    }
}
