<?php

declare(strict_types=1);

namespace App\Domain\Quality;

use App\Domain\Quality\Exception\InvalidQualityReportException;

final readonly class QualityReport
{
    /**
     * @param list<string> $explanations
     */
    public function __construct(
        private QualityReportId $id,
        private QualityMetricCollection $metrics,
        private QualityScore $overallScore,
        private PublicationRecommendation $recommendation,
        private array $explanations = [],
    ) {
        if ($this->metrics->count() !== count(QualityCategory::scored())) {
            throw new InvalidQualityReportException(sprintf(
                'Quality report must contain exactly %d category metrics.',
                count(QualityCategory::scored()),
            ));
        }

        foreach (QualityCategory::scored() as $category) {
            if (null === $this->metrics->forCategory($category)) {
                throw new InvalidQualityReportException(sprintf(
                    'Quality report is missing metric "%s".',
                    $category->value,
                ));
            }
        }
    }

    /**
     * @param list<string> $explanations
     */
    public static function create(
        QualityReportId $id,
        QualityMetricCollection $metrics,
        QualityScore $overallScore,
        PublicationRecommendation $recommendation,
        array $explanations = [],
    ): self {
        return new self(
            $id,
            $metrics,
            $overallScore,
            $recommendation,
            array_values($explanations),
        );
    }

    public static function recommendationFor(QualityScore $overallScore): PublicationRecommendation
    {
        if ($overallScore->value() >= 90) {
            return PublicationRecommendation::Ready;
        }

        if ($overallScore->value() >= 75) {
            return PublicationRecommendation::ReviewRecommended;
        }

        return PublicationRecommendation::RegenerateRequired;
    }

    public function id(): QualityReportId
    {
        return $this->id;
    }

    public function metrics(): QualityMetricCollection
    {
        return $this->metrics;
    }

    public function overallScore(): QualityScore
    {
        return $this->overallScore;
    }

    public function recommendation(): PublicationRecommendation
    {
        return $this->recommendation;
    }

    /**
     * @return list<string>
     */
    public function explanations(): array
    {
        return $this->explanations;
    }
}
