<?php

declare(strict_types=1);

namespace App\Domain\Speech;

use App\Domain\Speech\Exception\InvalidTranscriptException;

final readonly class TranscriptSegment
{
    public function __construct(
        private int $index,
        private float $startTime,
        private float $endTime,
        private string $text,
    ) {
        if ($index < 0) {
            throw new InvalidTranscriptException('Transcript segment index cannot be negative.');
        }

        if ($endTime < $startTime) {
            throw new InvalidTranscriptException('Transcript segment end time must be greater than or equal to start time.');
        }

        if ('' === trim($text)) {
            throw new InvalidTranscriptException('Transcript segment text cannot be empty.');
        }
    }

    public static function create(
        int $index,
        float $startTime,
        float $endTime,
        string $text,
    ): self {
        return new self($index, $startTime, $endTime, trim($text));
    }

    public function index(): int
    {
        return $this->index;
    }

    public function startTime(): float
    {
        return $this->startTime;
    }

    public function endTime(): float
    {
        return $this->endTime;
    }

    public function text(): string
    {
        return $this->text;
    }
}
