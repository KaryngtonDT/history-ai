<?php

declare(strict_types=1);

namespace App\Domain\Quality;

use App\Domain\Quality\Exception\InvalidQualityReportException;

final readonly class QualityScore
{
    public function __construct(private int $value)
    {
        if ($this->value < 0 || $this->value > 100) {
            throw new InvalidQualityReportException('Quality score must be between 0 and 100.');
        }
    }

    public static function create(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function cap(int $maximum): self
    {
        return new self(min($this->value, $maximum));
    }

    public function penalize(int $amount): self
    {
        return new self(max(0, $this->value - $amount));
    }

    public function bonus(int $amount): self
    {
        return new self(min(100, $this->value + $amount));
    }
}
