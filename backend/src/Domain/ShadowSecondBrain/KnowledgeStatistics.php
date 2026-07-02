<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

final readonly class KnowledgeStatistics
{
    /** @param list<KnowledgeDomainHeatmapEntry> $domainHeatmap */
    public function __construct(
        private int $videoCount,
        private int $pdfCount,
        private int $conversationCount,
        private int $exerciseCount,
        private int $missionCount,
        private int $conceptCount,
        private array $domainHeatmap,
    ) {
    }

    public static function empty(): self
    {
        return new self(0, 0, 0, 0, 0, 0, []);
    }

    public function videoCount(): int
    {
        return $this->videoCount;
    }

    public function pdfCount(): int
    {
        return $this->pdfCount;
    }

    public function conversationCount(): int
    {
        return $this->conversationCount;
    }

    public function exerciseCount(): int
    {
        return $this->exerciseCount;
    }

    public function missionCount(): int
    {
        return $this->missionCount;
    }

    public function conceptCount(): int
    {
        return $this->conceptCount;
    }

    /** @return list<KnowledgeDomainHeatmapEntry> */
    public function domainHeatmap(): array
    {
        return $this->domainHeatmap;
    }
}
