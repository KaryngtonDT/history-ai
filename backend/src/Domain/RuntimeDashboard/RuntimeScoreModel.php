<?php

declare(strict_types=1);

namespace App\Domain\RuntimeDashboard;

final readonly class RuntimeScoreModel
{
    public function __construct(
        public float $coreScore,
        public float $extensionScore,
        public float $premiumScore,
        public float $recommendationScore,
        public float $hardwareCompatibilityScore,
        public float $installationCoverage,
        public float $benchmarkCoverage,
        public float $predictionAccuracy,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'coreScore' => $this->coreScore,
            'extensionScore' => $this->extensionScore,
            'premiumScore' => $this->premiumScore,
            'recommendationScore' => $this->recommendationScore,
            'hardwareCompatibilityScore' => $this->hardwareCompatibilityScore,
            'installationCoverage' => $this->installationCoverage,
            'benchmarkCoverage' => $this->benchmarkCoverage,
            'predictionAccuracy' => $this->predictionAccuracy,
        ];
    }
}
