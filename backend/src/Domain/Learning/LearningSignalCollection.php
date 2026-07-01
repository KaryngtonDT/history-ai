<?php

declare(strict_types=1);

namespace App\Domain\Learning;

final readonly class LearningSignalCollection
{
    /**
     * @param list<LearningSignal> $signals
     */
    public function __construct(private array $signals)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function append(LearningSignal $signal): self
    {
        return new self([...$this->signals, $signal]);
    }

    /**
     * @return list<LearningSignal>
     */
    public function all(): array
    {
        return $this->signals;
    }

    public function count(): int
    {
        return count($this->signals);
    }

    public function isEmpty(): bool
    {
        return [] === $this->signals;
    }

    public function ofType(LearningSignalType $type): self
    {
        return new self(array_values(array_filter(
            $this->signals,
            static fn (LearningSignal $signal): bool => $signal->type() === $type,
        )));
    }

    /**
     * @return list<LearningSignal>
     */
    public function recent(int $limit): array
    {
        if ($limit < 1) {
            return [];
        }

        return array_slice($this->signals, -$limit);
    }
}
