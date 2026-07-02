<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowSecondBrain;

use App\Application\ShadowSecondBrain\KnowledgeMergeEngine;
use App\Domain\ShadowSecondBrain\KnowledgeCollection;
use App\Domain\ShadowSecondBrain\KnowledgeEntry;
use PHPUnit\Framework\TestCase;

final class KnowledgeMergeEngineTest extends TestCase
{
    private KnowledgeMergeEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new KnowledgeMergeEngine();
    }

    public function testMergeCombinesDuplicateConceptEntries(): void
    {
        $entries = new KnowledgeCollection([
            new KnowledgeEntry(
                'docker',
                'docker',
                'Docker',
                'Primary summary',
                40,
                new \DateTimeImmutable('2026-01-01'),
                new \DateTimeImmutable('2026-02-01'),
                2,
                1,
                1,
                ['containers'],
                ['Practice networking'],
            ),
            new KnowledgeEntry(
                'docker_alt',
                'docker-alt',
                'Docker Alt',
                'Secondary summary',
                70,
                new \DateTimeImmutable('2025-12-01'),
                new \DateTimeImmutable('2026-03-01'),
                3,
                2,
                0,
                ['kubernetes'],
                [],
            ),
        ]);

        $merged = $this->engine->merge($entries, ['docker' => ['docker-alt']]);

        self::assertCount(1, $merged->all());
        $entry = $merged->findByKey('docker');
        self::assertNotNull($entry);
        self::assertSame(70, $entry->masteryPercent());
        self::assertSame(5, $entry->exposureCount());
        self::assertSame(3, $entry->exerciseCount());
        self::assertEquals(['containers', 'kubernetes'], $entry->relatedKeys());
    }
}
