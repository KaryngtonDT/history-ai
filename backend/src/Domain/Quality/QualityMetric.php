<?php

declare(strict_types=1);

namespace App\Domain\Quality;

use App\Domain\Quality\Exception\InvalidQualityReportException;

final readonly class QualityMetric
{
    public function __construct(
        private QualityCategory $category,
        private QualityScore $score,
        private string $explanation = '',
    ) {
        if (QualityCategory::Overall === $this->category) {
            throw new InvalidQualityReportException('Overall score must not be stored as a metric.');
        }
    }

    public static function create(
        QualityCategory $category,
        QualityScore $score,
        string $explanation = '',
    ): self {
        return new self($category, $score, trim($explanation));
    }

    public function category(): QualityCategory
    {
        return $this->category;
    }

    public function score(): QualityScore
    {
        return $this->score;
    }

    public function explanation(): string
    {
        return $this->explanation;
    }
}
