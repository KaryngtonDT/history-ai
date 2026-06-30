<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

use App\Domain\Workspace\Exception\InvalidProjectException;

final readonly class BatchJobProgress
{
    private function __construct(private int $value)
    {
    }

    public static function fromPercentage(int $value): self
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidProjectException(
                sprintf('Batch progress must be between 0 and 100, got %d.', $value),
            );
        }

        return new self($value);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public static function complete(): self
    {
        return new self(100);
    }

    public static function fromFinishedCount(int $finished, int $total): self
    {
        if ($total <= 0) {
            return self::zero();
        }

        return self::fromPercentage((int) round(($finished / $total) * 100));
    }

    public function percentage(): int
    {
        return $this->value;
    }

    public function isComplete(): bool
    {
        return 100 === $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
