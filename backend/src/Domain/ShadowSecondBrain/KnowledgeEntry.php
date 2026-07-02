<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\Exception\InvalidShadowSecondBrainException;

final readonly class KnowledgeEntry
{
    /** @param list<string> $relatedKeys */
    /** @param list<string> $recommendations */
    public function __construct(
        private string $id,
        private string $conceptKey,
        private string $label,
        private string $summary,
        private int $masteryPercent,
        private \DateTimeImmutable $firstSeenAt,
        private \DateTimeImmutable $lastSeenAt,
        private int $exposureCount,
        private int $exerciseCount,
        private int $explanationCount,
        private array $relatedKeys,
        private array $recommendations,
    ) {
        if ('' === trim($id)) {
            throw new InvalidShadowSecondBrainException('Knowledge entry id cannot be empty.');
        }

        if ('' === trim($conceptKey)) {
            throw new InvalidShadowSecondBrainException('Knowledge entry concept key cannot be empty.');
        }

        if ($masteryPercent < 0 || $masteryPercent > 100) {
            throw new InvalidShadowSecondBrainException('Knowledge entry mastery percent must be between 0 and 100.');
        }

        if ($exposureCount < 0 || $exerciseCount < 0 || $explanationCount < 0) {
            throw new InvalidShadowSecondBrainException('Knowledge entry counts cannot be negative.');
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function conceptKey(): string
    {
        return $this->conceptKey;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function summary(): string
    {
        return $this->summary;
    }

    public function masteryPercent(): int
    {
        return $this->masteryPercent;
    }

    public function firstSeenAt(): \DateTimeImmutable
    {
        return $this->firstSeenAt;
    }

    public function lastSeenAt(): \DateTimeImmutable
    {
        return $this->lastSeenAt;
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
    public function relatedKeys(): array
    {
        return $this->relatedKeys;
    }

    /** @return list<string> */
    public function recommendations(): array
    {
        return $this->recommendations;
    }
}
