<?php

declare(strict_types=1);

namespace App\Domain\Processing;

use App\Domain\Processing\Exception\InvalidProcessingJobException;

final readonly class ProcessingJobProgress
{
    private function __construct(private int $value)
    {
    }

    public static function fromPercentage(int $value): self
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidProcessingJobException(
                sprintf('Progress must be between 0 and 100, got %d.', $value),
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

    public function percentage(): int
    {
        return $this->value;
    }

    public function isComplete(): bool
    {
        return 100 === $this->value;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->value > $other->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
