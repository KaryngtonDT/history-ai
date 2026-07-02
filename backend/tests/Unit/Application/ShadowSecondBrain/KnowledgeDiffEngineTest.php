<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowSecondBrain;

use App\Application\ShadowSecondBrain\KnowledgeDiffEngine;
use App\Domain\ShadowSecondBrain\KnowledgeCollection;
use App\Domain\ShadowSecondBrain\KnowledgeEntry;
use App\Domain\ShadowSecondBrain\KnowledgeWorkspace;
use PHPUnit\Framework\TestCase;

final class KnowledgeDiffEngineTest extends TestCase
{
    private KnowledgeDiffEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new KnowledgeDiffEngine();
    }

    public function testDiffReportsNovelAndKnownConcepts(): void
    {
        $workspace = $this->workspaceWithConcept('docker', 60, new \DateTimeImmutable('-30 days'));

        $result = $this->engine->diff(
            'video',
            'video-1',
            ['docker', 'kubernetes', 'helm'],
            $workspace,
        );

        self::assertSame(2, $result['newConcepts']);
        self::assertSame(1, $result['knownConcepts']);
        self::assertSame(['kubernetes', 'helm'], $result['novelConceptKeys']);
        self::assertSame(['docker'], $result['knownConceptKeys']);
        self::assertSame(33, $result['redundancyPercent']);
    }

    public function testDiffFlagsRevisionDueForStaleConcepts(): void
    {
        $workspace = $this->workspaceWithConcept('docker', 40, new \DateTimeImmutable('-120 days'));

        $result = $this->engine->diff(
            'pdf',
            'pdf-1',
            ['docker'],
            $workspace,
        );

        self::assertSame(1, $result['revisionDue']);
        self::assertSame(['docker'], $result['revisionConceptKeys']);
        self::assertSame(100, $result['redundancyPercent']);
    }

    public function testDiffReturnsZeroRedundancyWhenNoConceptKeysProvided(): void
    {
        $workspace = KnowledgeWorkspace::create();

        $result = $this->engine->diff('video', 'video-2', [], $workspace);

        self::assertSame(0, $result['newConcepts']);
        self::assertSame(0, $result['knownConcepts']);
        self::assertSame(0, $result['redundancyPercent']);
    }

    private function workspaceWithConcept(string $key, int $mastery, \DateTimeImmutable $lastSeen): KnowledgeWorkspace
    {
        $entry = new KnowledgeEntry(
            $key,
            $key,
            ucfirst($key),
            'Summary for '.$key,
            $mastery,
            $lastSeen,
            $lastSeen,
            2,
            1,
            1,
            [],
            [],
        );

        return KnowledgeWorkspace::create()->withEntries(
            KnowledgeCollection::empty()->upsert($entry),
        );
    }
}
