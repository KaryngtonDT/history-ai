<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\Exception\InvalidVideoIntelligenceException;

final readonly class SpeechConfidence
{
    public function __construct(private int $percentage)
    {
        if ($this->percentage < 0 || $this->percentage > 100) {
            throw new InvalidVideoIntelligenceException('Speech confidence must be between 0 and 100.');
        }
    }

    public static function create(int $percentage): self
    {
        return new self($percentage);
    }

    public function percentage(): int
    {
        return $this->percentage;
    }

    public function isHigh(): bool
    {
        return $this->percentage >= 80;
    }

    public function isLow(): bool
    {
        return $this->percentage < 80;
    }
}
