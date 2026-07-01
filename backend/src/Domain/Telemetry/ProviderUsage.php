<?php

declare(strict_types=1);

namespace App\Domain\Telemetry;

use App\Domain\Telemetry\Exception\InvalidPipelineTelemetryException;

final readonly class ProviderUsage
{
    public function __construct(
        private string $stage,
        private string $providerId,
        private int $invocationCount,
        private float $totalDurationSeconds,
    ) {
        if ('' === trim($stage)) {
            throw new InvalidPipelineTelemetryException('Provider stage cannot be empty.');
        }

        if ('' === trim($providerId)) {
            throw new InvalidPipelineTelemetryException('Provider id cannot be empty.');
        }

        if ($invocationCount < 0) {
            throw new InvalidPipelineTelemetryException('Provider invocation count cannot be negative.');
        }

        if ($totalDurationSeconds < 0) {
            throw new InvalidPipelineTelemetryException('Provider duration cannot be negative.');
        }
    }

    public static function create(
        string $stage,
        string $providerId,
        int $invocationCount = 1,
        float $totalDurationSeconds = 0.0,
    ): self {
        return new self($stage, $providerId, $invocationCount, $totalDurationSeconds);
    }

    public function stage(): string
    {
        return $this->stage;
    }

    public function providerId(): string
    {
        return $this->providerId;
    }

    public function invocationCount(): int
    {
        return $this->invocationCount;
    }

    public function totalDurationSeconds(): float
    {
        return $this->totalDurationSeconds;
    }

    public function merge(self $other): self
    {
        if ($this->stage !== $other->stage || $this->providerId !== $other->providerId) {
            throw new InvalidPipelineTelemetryException('Cannot merge provider usage for different providers.');
        }

        return new self(
            $this->stage,
            $this->providerId,
            $this->invocationCount + $other->invocationCount,
            $this->totalDurationSeconds + $other->totalDurationSeconds,
        );
    }
}
