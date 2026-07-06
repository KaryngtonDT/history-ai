<?php

declare(strict_types=1);

namespace App\Domain\RuntimeDashboard;

final readonly class RuntimeScoreBreakdown
{
    public function __construct(
        public string $key,
        public string $label,
        public float $score,
        public float $weight,
        public float $weightedContribution,
        public string $explanation,
        public ?string $improvement = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'score' => $this->score,
            'weight' => $this->weight,
            'weightedContribution' => $this->weightedContribution,
            'explanation' => $this->explanation,
            'improvement' => $this->improvement,
        ];
    }
}
