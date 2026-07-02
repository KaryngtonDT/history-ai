<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

use App\Domain\ShadowIdentity\Exception\InvalidShadowIdentityException;

final readonly class ShadowInterruptionPolicy
{
    public function __construct(
        private bool $allowInterruptions,
        private bool $thinkingPauses,
        private int $maxInterruptionsPerMinute,
    ) {
        if ($maxInterruptionsPerMinute < 0) {
            throw new InvalidShadowIdentityException(
                'Max interruptions per minute cannot be negative.',
            );
        }
    }

    public static function default(): self
    {
        return new self(
            allowInterruptions: true,
            thinkingPauses: true,
            maxInterruptionsPerMinute: 4,
        );
    }

    public function allowInterruptions(): bool
    {
        return $this->allowInterruptions;
    }

    public function thinkingPauses(): bool
    {
        return $this->thinkingPauses;
    }

    public function maxInterruptionsPerMinute(): int
    {
        return $this->maxInterruptionsPerMinute;
    }

    public function withThinkingPauses(bool $enabled): self
    {
        return new self(
            $this->allowInterruptions,
            $enabled,
            $this->maxInterruptionsPerMinute,
        );
    }
}
