<?php

declare(strict_types=1);

namespace App\Tests\Functional\Platform;

use App\Application\Platform\PerformanceMetric;
use App\Application\Platform\PerformanceMetricCollection;
use App\Domain\Platform\CorrelationId;
use App\Infrastructure\Platform\InMemoryPerformanceMetricsStore;
use App\Tests\Unit\Application\Platform\Support\FixedClock;
use App\Tests\Unit\Application\Platform\Support\FixedRequestContextProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GetPlatformMetricsControllerTest extends WebTestCase
{
    public function testReturnsStoredSnapshotsAsJson(): void
    {
        $client = static::createClient();
        $store = new InMemoryPerformanceMetricsStore(
            new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d')),
            new FixedClock(1_700_000_000.0),
        );
        static::getContainer()->set(InMemoryPerformanceMetricsStore::class, $store);

        $store->record(PerformanceMetricCollection::empty()
            ->with(new PerformanceMetric('chunking_ms', 12))
            ->with(new PerformanceMetric('embedding_ms', 55)));

        $client->request('GET', '/internal/platform/metrics');

        self::assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('snapshots', $response);
        self::assertCount(1, $response['snapshots']);
        self::assertSame('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d', $response['snapshots'][0]['correlationId']);
        self::assertSame('2023-11-14T22:13:20+00:00', $response['snapshots'][0]['recordedAt']);
        self::assertSame(
            [
                ['name' => 'chunking_ms', 'durationMs' => 12],
                ['name' => 'embedding_ms', 'durationMs' => 55],
            ],
            $response['snapshots'][0]['metrics'],
        );
    }

    public function testUsesDefaultLimitOfTwenty(): void
    {
        $client = static::createClient();
        $store = new InMemoryPerformanceMetricsStore(
            new FixedRequestContextProvider(CorrelationId::generate()),
            new FixedClock(),
        );
        static::getContainer()->set(InMemoryPerformanceMetricsStore::class, $store);

        for ($index = 0; $index < 25; ++$index) {
            $store->record(PerformanceMetricCollection::empty()
                ->with(new PerformanceMetric('total_ms', $index)));
        }

        $client->request('GET', '/internal/platform/metrics');

        self::assertResponseIsSuccessful();
        self::assertCount(20, json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR)['snapshots']);
    }

    public function testAppliesLimitQueryParameter(): void
    {
        $client = static::createClient();
        $store = new InMemoryPerformanceMetricsStore(
            new FixedRequestContextProvider(CorrelationId::generate()),
            new FixedClock(),
        );
        static::getContainer()->set(InMemoryPerformanceMetricsStore::class, $store);

        for ($index = 0; $index < 5; ++$index) {
            $store->record(PerformanceMetricCollection::empty()
                ->with(new PerformanceMetric('total_ms', $index)));
        }

        $client->request('GET', '/internal/platform/metrics?limit=3');

        self::assertResponseIsSuccessful();
        self::assertCount(3, json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR)['snapshots']);
    }

    public function testReturnsBadRequestForInvalidLimit(): void
    {
        $client = static::createClient();

        $client->request('GET', '/internal/platform/metrics?limit=0');

        self::assertResponseStatusCodeSame(400);

        $client->request('GET', '/internal/platform/metrics?limit=101');

        self::assertResponseStatusCodeSame(400);

        $client->request('GET', '/internal/platform/metrics?limit=abc');

        self::assertResponseStatusCodeSame(400);
    }
}
