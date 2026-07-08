<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

final readonly class ResolvedEngineIntelligence
{
    public function __construct(
        public ?string $alternativeEngineId = null,
        public ?string $alternativeDisplayName = null,
        public ?int $estimatedDurationSeconds = null,
        public ?float $expectedAccuracy = null,
        public ?float $expectedMemoryMb = null,
        public ?float $expectedCpuPercent = null,
        public ?string $explanation = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'alternativeEngineId' => $this->alternativeEngineId,
            'alternativeDisplayName' => $this->alternativeDisplayName,
            'estimatedDurationSeconds' => $this->estimatedDurationSeconds,
            'expectedAccuracy' => $this->expectedAccuracy,
            'expectedMemoryMb' => $this->expectedMemoryMb,
            'expectedCpuPercent' => $this->expectedCpuPercent,
            'explanation' => $this->explanation,
        ];
    }
}
