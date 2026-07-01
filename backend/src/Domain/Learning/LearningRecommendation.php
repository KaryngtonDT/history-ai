<?php

declare(strict_types=1);

namespace App\Domain\Learning;

use App\Domain\Learning\Exception\InvalidLearningProfileException;

final readonly class LearningRecommendation
{
    /**
     * @param list<string> $sourceInsightIds
     */
    public function __construct(
        private LearningRecommendationId $id,
        private LearningRecommendationType $type,
        private string $explanation,
        private array $sourceInsightIds,
        private \DateTimeImmutable $generatedAt,
    ) {
        if ('' === trim($explanation)) {
            throw new InvalidLearningProfileException('Learning recommendation explanation cannot be empty.');
        }

        if ([] === $sourceInsightIds) {
            throw new InvalidLearningProfileException('Learning recommendation must reference source insights.');
        }
    }

    /**
     * @param list<string> $sourceInsightIds
     */
    public static function derive(
        LearningRecommendationType $type,
        string $explanation,
        array $sourceInsightIds,
        ?LearningRecommendationId $id = null,
        ?\DateTimeImmutable $generatedAt = null,
    ): self {
        return new self(
            $id ?? LearningRecommendationId::generate(),
            $type,
            trim($explanation),
            $sourceInsightIds,
            $generatedAt ?? new \DateTimeImmutable(),
        );
    }

    public function id(): LearningRecommendationId
    {
        return $this->id;
    }

    public function type(): LearningRecommendationType
    {
        return $this->type;
    }

    public function explanation(): string
    {
        return $this->explanation;
    }

    /**
     * @return list<string>
     */
    public function sourceInsightIds(): array
    {
        return $this->sourceInsightIds;
    }

    public function generatedAt(): \DateTimeImmutable
    {
        return $this->generatedAt;
    }
}
