<?php

declare(strict_types=1);

namespace App\Application\Learning;

use App\Domain\Learning\LearningSignal;

final class LearningSignalCollector
{
    public function __construct(
        private readonly ShadowLearningSignalMapper $shadowMapper,
        private readonly ReviewLearningSignalMapper $reviewMapper,
        private readonly TelemetryLearningSignalMapper $telemetryMapper,
        private readonly QualityLearningSignalMapper $qualityMapper,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return list<LearningSignal>
     */
    public function collect(array $payload): array
    {
        $source = is_string($payload['source'] ?? null) ? $payload['source'] : '';

        return match ($source) {
            'shadow' => $this->shadowMapper->map($payload),
            'review' => $this->reviewMapper->map($payload),
            'telemetry' => $this->telemetryMapper->map($payload),
            'quality' => $this->qualityMapper->map($payload),
            default => [],
        };
    }
}
