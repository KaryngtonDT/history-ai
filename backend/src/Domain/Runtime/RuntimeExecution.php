<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

final readonly class RuntimeExecution
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $id,
        public string $engineId,
        public RuntimeCapability $capability,
        public RuntimeStatus $status,
        public float $durationMs,
        public bool $fallbackUsed,
        public ?string $requestedEngineId = null,
        public ?string $reason = null,
        public array $metadata = [],
        public ?string $startedAt = null,
        public ?string $completedAt = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'engineId' => $this->engineId,
            'capability' => $this->capability->value,
            'status' => $this->status->value,
            'durationMs' => $this->durationMs,
            'fallbackUsed' => $this->fallbackUsed,
            'requestedEngineId' => $this->requestedEngineId,
            'reason' => $this->reason,
            'metadata' => $this->metadata,
            'startedAt' => $this->startedAt,
            'completedAt' => $this->completedAt,
        ];
    }
}
