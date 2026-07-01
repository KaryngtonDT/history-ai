<?php

declare(strict_types=1);

namespace App\Application\Learning;

use App\Domain\Learning\LearningSignal;
use App\Domain\Learning\LearningSignalType;

final class QualityLearningSignalMapper
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return list<LearningSignal>
     */
    public function map(array $payload): array
    {
        $overallScore = is_numeric($payload['overallScore'] ?? null) ? (int) $payload['overallScore'] : null;
        $reportId = is_string($payload['reportId'] ?? null) ? $payload['reportId'] : 'unknown';
        $providerId = is_string($payload['providerId'] ?? null) ? trim($payload['providerId']) : null;

        if (null === $overallScore) {
            return [];
        }

        $signals = [
            LearningSignal::record(
                LearningSignalType::QualityScoreObserved,
                [
                    'summary' => sprintf('Quality report %s scored %d.', $reportId, $overallScore),
                    'reportId' => $reportId,
                    'value' => $overallScore,
                ],
            ),
        ];

        if (null !== $providerId && '' !== $providerId) {
            $signals[] = LearningSignal::record(
                LearningSignalType::ProviderPerformanceObserved,
                [
                    'summary' => sprintf(
                        'Provider %s associated with quality score %d.',
                        $providerId,
                        $overallScore,
                    ),
                    'providerId' => $providerId,
                    'qualityScore' => $overallScore,
                    'success' => $overallScore >= 75,
                ],
            );
        }

        return $signals;
    }
}
