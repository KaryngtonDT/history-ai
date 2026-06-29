<?php

declare(strict_types=1);

namespace App\Domain\Speech;

final readonly class TranscriptSegmentCollection
{
    /** @var list<TranscriptSegment> */
    private array $segments;

    /**
     * @param list<TranscriptSegment> $segments
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
     * @return list<TranscriptSegment>
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

    public function append(TranscriptSegment $segment): self
    {
        return new self([...$this->segments, $segment]);
    }
}
