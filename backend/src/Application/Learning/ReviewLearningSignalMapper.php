<?php

declare(strict_types=1);

namespace App\Application\Learning;

use App\Domain\Learning\LearningSignal;
use App\Domain\Learning\LearningSignalType;

final class ReviewLearningSignalMapper
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return list<LearningSignal>
     */
    public function map(array $payload): array
    {
        $event = is_string($payload['event'] ?? null) ? $payload['event'] : 'review_submitted';

        if ('review_submitted' !== $event) {
            return [];
        }

        $translationScore = is_numeric($payload['translationScore'] ?? null) ? (int) $payload['translationScore'] : null;
        $overallScore = is_numeric($payload['overallScore'] ?? null) ? (int) $payload['overallScore'] : null;
        $videoId = is_string($payload['videoId'] ?? null) ? $payload['videoId'] : 'unknown';

        $signals = [
            LearningSignal::record(
                LearningSignalType::UserReviewSubmitted,
                [
                    'summary' => sprintf('User review submitted for video %s.', $videoId),
                    'videoId' => $videoId,
                    'overallScore' => $overallScore,
                ],
            ),
        ];

        if (null !== $translationScore) {
            $style = $translationScore >= 80 ? 'natural' : 'literal';

            $signals[] = LearningSignal::record(
                LearningSignalType::TranslationStylePreference,
                [
                    'summary' => sprintf(
                        'Review translation score %d suggests %s style preference.',
                        $translationScore,
                        $style,
                    ),
                    'style' => $style,
                    'score' => $translationScore,
                ],
            );
        }

        return $signals;
    }
}
