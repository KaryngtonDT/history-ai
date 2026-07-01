<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

use App\Domain\Shadow\Exception\InvalidShadowSessionException;

final readonly class ShadowTimestamp
{
    public function __construct(private float $seconds)
    {
        if ($seconds < 0) {
            throw new InvalidShadowSessionException('Shadow timestamp cannot be negative.');
        }
    }

    public static function fromSeconds(float $seconds): self
    {
        return new self($seconds);
    }

    public static function zero(): self
    {
        return new self(0.0);
    }

    public function seconds(): float
    {
        return $this->seconds;
    }

    public function equals(self $other): bool
    {
        return abs($this->seconds - $other->seconds) < 0.001;
    }
}
