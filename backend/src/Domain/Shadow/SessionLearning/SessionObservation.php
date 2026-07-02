<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;

final readonly class SessionObservation
{
    public function __construct(
        private SessionObservationType $type,
        private float $timeSeconds,
        private ?string $detail = null,
    ) {
        if ($timeSeconds < 0) {
            throw new InvalidShadowSessionException('Observation time cannot be negative.');
        }
    }

    public function type(): SessionObservationType
    {
        return $this->type;
    }

    public function timeSeconds(): float
    {
        return $this->timeSeconds;
    }

    public function detail(): ?string
    {
        return $this->detail;
    }
}
