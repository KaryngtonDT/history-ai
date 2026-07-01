<?php

declare(strict_types=1);

namespace App\Domain\Learning;

use App\Domain\Learning\Exception\InvalidLearningProfileException;

final readonly class LearningInsight
{
    /**
     * @param list<string> $sourceSignalIds
     */
    public function __construct(
        private LearningInsightId $id,
        private LearningInsightType $type,
        private string $summary,
        private array $sourceSignalIds,
        private \DateTimeImmutable $generatedAt,
    ) {
        if ('' === trim($summary)) {
            throw new InvalidLearningProfileException('Learning insight summary cannot be empty.');
        }

        if ([] === $sourceSignalIds) {
            throw new InvalidLearningProfileException('Learning insight must reference source signals.');
        }
    }

    /**
     * @param list<string> $sourceSignalIds
     */
    public static function derive(
        LearningInsightType $type,
        string $summary,
        array $sourceSignalIds,
        ?LearningInsightId $id = null,
        ?\DateTimeImmutable $generatedAt = null,
    ): self {
        return new self(
            $id ?? LearningInsightId::generate(),
            $type,
            trim($summary),
            $sourceSignalIds,
            $generatedAt ?? new \DateTimeImmutable(),
        );
    }

    public function id(): LearningInsightId
    {
        return $this->id;
    }

    public function type(): LearningInsightType
    {
        return $this->type;
    }

    public function summary(): string
    {
        return $this->summary;
    }

    /**
     * @return list<string>
     */
    public function sourceSignalIds(): array
    {
        return $this->sourceSignalIds;
    }

    public function generatedAt(): \DateTimeImmutable
    {
        return $this->generatedAt;
    }
}
