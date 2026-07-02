<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

use App\Domain\ShadowMemory\Exception\InvalidShadowMemoryException;

final readonly class KnowledgeItem
{
    /**
     * @param list<string> $videoIds
     * @param list<string> $sessionIds
     */
    public function __construct(
        private string $key,
        private string $label,
        private MemoryCategory $category,
        private KnowledgeProgress $progress,
        private int $exposureCount,
        private int $questionCount,
        private int $challengeSuccessCount,
        private array $videoIds,
        private array $sessionIds,
        private string $explanation,
        private ?\DateTimeImmutable $lastSeenAt,
    ) {
        if ('' === trim($key) || '' === trim($label)) {
            throw new InvalidShadowMemoryException('Knowledge item key and label cannot be empty.');
        }
    }

    public static function start(
        string $key,
        string $label,
        MemoryCategory $category,
        string $explanation,
    ): self {
        return new self(
            $key,
            $label,
            $category,
            KnowledgeProgress::New,
            1,
            0,
            0,
            [],
            [],
            $explanation,
            new \DateTimeImmutable(),
        );
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function category(): MemoryCategory
    {
        return $this->category;
    }

    public function progress(): KnowledgeProgress
    {
        return $this->progress;
    }

    public function exposureCount(): int
    {
        return $this->exposureCount;
    }

    public function questionCount(): int
    {
        return $this->questionCount;
    }

    public function challengeSuccessCount(): int
    {
        return $this->challengeSuccessCount;
    }

    /** @return list<string> */
    public function videoIds(): array
    {
        return $this->videoIds;
    }

    /** @return list<string> */
    public function sessionIds(): array
    {
        return $this->sessionIds;
    }

    public function explanation(): string
    {
        return $this->explanation;
    }

    public function lastSeenAt(): ?\DateTimeImmutable
    {
        return $this->lastSeenAt;
    }

    public function progressPercent(): int
    {
        $base = $this->progress->percent();
        $bonus = min(15, $this->challengeSuccessCount * 3 + $this->questionCount);

        return min(100, $base + $bonus);
    }

    public function withExposure(?string $videoId = null, ?string $sessionId = null): self
    {
        $videoIds = $this->videoIds;
        $sessionIds = $this->sessionIds;

        if (null !== $videoId && !in_array($videoId, $videoIds, true)) {
            $videoIds[] = $videoId;
        }

        if (null !== $sessionId && !in_array($sessionId, $sessionIds, true)) {
            $sessionIds[] = $sessionId;
        }

        return new self(
            $this->key,
            $this->label,
            $this->category,
            $this->nextProgress($this->exposureCount + 1),
            $this->exposureCount + 1,
            $this->questionCount,
            $this->challengeSuccessCount,
            $videoIds,
            $sessionIds,
            $this->explanation,
            new \DateTimeImmutable(),
        );
    }

    public function withQuestion(): self
    {
        return new self(
            $this->key,
            $this->label,
            $this->category,
            KnowledgeProgress::Learning,
            $this->exposureCount,
            $this->questionCount + 1,
            $this->challengeSuccessCount,
            $this->videoIds,
            $this->sessionIds,
            $this->explanation,
            new \DateTimeImmutable(),
        );
    }

    public function withChallengeSuccess(): self
    {
        return new self(
            $this->key,
            $this->label,
            $this->category,
            KnowledgeProgress::Practiced,
            $this->exposureCount,
            $this->questionCount,
            $this->challengeSuccessCount + 1,
            $this->videoIds,
            $this->sessionIds,
            $this->explanation,
            new \DateTimeImmutable(),
        );
    }

    private function nextProgress(int $exposureCount): KnowledgeProgress
    {
        return match (true) {
            $exposureCount >= 12 || $this->challengeSuccessCount >= 3 => KnowledgeProgress::Mastered,
            $exposureCount >= 6 => KnowledgeProgress::Practiced,
            $exposureCount >= 2 => KnowledgeProgress::Learning,
            default => KnowledgeProgress::New,
        };
    }
}
