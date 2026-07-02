<?php

declare(strict_types=1);

namespace App\Domain\ShadowKnowledge;

final readonly class KnowledgeMastery
{
    /** @param list<string> $videoIds */
    public function __construct(
        private string $nodeKey,
        private int $percent,
        private int $exposureCount,
        private int $exerciseCount,
        private int $explanationCount,
        private array $videoIds,
        private KnowledgeConfidence $confidence,
        private ?\DateTimeImmutable $firstSeenAt,
        private ?\DateTimeImmutable $lastSeenAt,
    ) {
    }

    public static function fromProgress(
        string $nodeKey,
        int $percent,
        int $exposureCount = 0,
        int $exerciseCount = 0,
        int $explanationCount = 0,
        array $videoIds = [],
    ): self {
        $confidence = match (true) {
            $percent >= 80 => KnowledgeConfidence::High,
            $percent >= 40 => KnowledgeConfidence::Medium,
            default => KnowledgeConfidence::Low,
        };

        return new self(
            $nodeKey,
            $percent,
            $exposureCount,
            $exerciseCount,
            $explanationCount,
            $videoIds,
            $confidence,
            null,
            null,
        );
    }

    public function nodeKey(): string
    {
        return $this->nodeKey;
    }

    public function percent(): int
    {
        return $this->percent;
    }

    public function exposureCount(): int
    {
        return $this->exposureCount;
    }

    public function exerciseCount(): int
    {
        return $this->exerciseCount;
    }

    public function explanationCount(): int
    {
        return $this->explanationCount;
    }

    /** @return list<string> */
    public function videoIds(): array
    {
        return $this->videoIds;
    }

    public function confidence(): KnowledgeConfidence
    {
        return $this->confidence;
    }

    public function firstSeenAt(): ?\DateTimeImmutable
    {
        return $this->firstSeenAt;
    }

    public function lastSeenAt(): ?\DateTimeImmutable
    {
        return $this->lastSeenAt;
    }

    public function mastered(): bool
    {
        return $this->percent >= 80;
    }
}
