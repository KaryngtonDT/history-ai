<?php

declare(strict_types=1);

namespace App\Application\Learning;

use App\Domain\Learning\LearningSignal;
use App\Domain\Learning\LearningSignalType;

final class TelemetryLearningSignalMapper
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return list<LearningSignal>
     */
    public function map(array $payload): array
    {
        $providerId = is_string($payload['providerId'] ?? null) ? trim($payload['providerId']) : '';
        $stage = is_string($payload['stage'] ?? null) ? trim($payload['stage']) : 'unknown';
        $qualityScore = is_numeric($payload['qualityScore'] ?? null) ? (int) $payload['qualityScore'] : null;
        $success = (bool) ($payload['success'] ?? true);

        if ('' === $providerId) {
            return [];
        }

        $signals = [
            LearningSignal::record(
                LearningSignalType::ProviderPerformanceObserved,
                [
                    'summary' => sprintf(
                        'Provider %s observed at stage %s (success=%s).',
                        $providerId,
                        $stage,
                        $success ? 'true' : 'false',
                    ),
                    'providerId' => $providerId,
                    'stage' => $stage,
                    'success' => $success,
                ],
            ),
        ];

        if (null !== $qualityScore) {
            $signals[] = LearningSignal::record(
                LearningSignalType::QualityScoreObserved,
                [
                    'summary' => sprintf(
                        'Quality score %d observed with provider %s.',
                        $qualityScore,
                        $providerId,
                    ),
                    'providerId' => $providerId,
                    'value' => $qualityScore,
                ],
            );
        }

        return $signals;
    }
}
