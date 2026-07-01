<?php

declare(strict_types=1);

namespace App\Domain\Telemetry;

use App\Domain\Telemetry\Exception\InvalidPipelineTelemetryException;
use App\Domain\Workspace\ProjectId;

final readonly class PipelineTelemetryId
{
    public function __construct(private string $value)
    {
        if (!ProjectId::isValid($value)) {
            throw new InvalidPipelineTelemetryException('Pipeline telemetry id must be a valid UUID.');
        }
    }

    public static function generate(): self
    {
        return new self(ProjectId::generate()->value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
