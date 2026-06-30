<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\Exception\InvalidVideoIntelligenceException;

final readonly class VideoSpeaker
{
    public function __construct(
        private int $index,
        private string $label,
    ) {
        if ($this->index < 1) {
            throw new InvalidVideoIntelligenceException('Speaker index must be at least 1.');
        }

        if ('' === trim($this->label)) {
            throw new InvalidVideoIntelligenceException('Speaker label must not be empty.');
        }
    }

    public static function create(int $index, string $label): self
    {
        return new self($index, $label);
    }

    public function index(): int
    {
        return $this->index;
    }

    public function label(): string
    {
        return $this->label;
    }
}
