<?php

declare(strict_types=1);

namespace App\Domain\Translation;

final readonly class TranslationSegmentCollection
{
    /** @var list<TranslationSegment> */
    private array $segments;

    /**
     * @param list<TranslationSegment> $segments
     */
    public function __construct(array $segments)
    {
        $this->segments = array_values($segments);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<TranslationSegment>
     */
    public function all(): array
    {
        return $this->segments;
    }

    public function count(): int
    {
        return count($this->segments);
    }

    public function isEmpty(): bool
    {
        return [] === $this->segments;
    }

    public function append(TranslationSegment $segment): self
    {
        return new self([...$this->segments, $segment]);
    }
}
