<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowSecondBrain;

use App\Application\ShadowSecondBrain\KnowledgeWorkspaceSearch;
use App\Domain\ShadowSecondBrain\KnowledgeCollection;
use App\Domain\ShadowSecondBrain\KnowledgeEntry;
use PHPUnit\Framework\TestCase;

final class KnowledgeWorkspaceSearchTest extends TestCase
{
    private KnowledgeWorkspaceSearch $search;

    protected function setUp(): void
    {
        $this->search = new KnowledgeWorkspaceSearch();
    }

    public function testSearchMatchesConceptKeyLabelAndSummary(): void
    {
        $entries = new KnowledgeCollection([
            $this->entry('docker', 'Docker', 'Container runtime platform'),
            $this->entry('kubernetes', 'Kubernetes', 'Container orchestration'),
            $this->entry('symfony_messenger', 'Symfony Messenger', 'Async messaging component'),
        ]);

        $results = $this->search->search($entries, 'container');

        self::assertCount(2, $results);
        self::assertSame('docker', $results[0]->conceptKey());
    }

    public function testSearchReturnsEmptyForBlankQuery(): void
    {
        $entries = new KnowledgeCollection([
            $this->entry('docker', 'Docker', 'Container runtime platform'),
        ]);

        self::assertSame([], $this->search->search($entries, '   '));
    }

    private function entry(string $key, string $label, string $summary): KnowledgeEntry
    {
        $now = new \DateTimeImmutable();

        return new KnowledgeEntry(
            $key,
            $key,
            $label,
            $summary,
            50,
            $now,
            $now,
            1,
            0,
            0,
            [],
            [],
        );
    }
}
