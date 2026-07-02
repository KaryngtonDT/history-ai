<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

use App\Domain\ShadowIdentity\Exception\InvalidShadowIdentityException;

final readonly class ShadowChallengeProfile
{
    public function __construct(private int $level)
    {
        if ($level < 1 || $level > 5) {
            throw new InvalidShadowIdentityException('Challenge level must be between 1 and 5.');
        }
    }

    public static function default(): self
    {
        return new self(3);
    }

    public function level(): int
    {
        return $this->level;
    }

    public function withLevel(int $level): self
    {
        return new self($level);
    }

    public function increase(int $steps = 1): self
    {
        return new self(min(5, $this->level + $steps));
    }

    public function decrease(int $steps = 1): self
    {
        return new self(max(1, $this->level - $steps));
    }
}
