<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

final readonly class VideoSpeakerCollection
{
    /** @var list<VideoSpeaker> */
    private array $speakers;

    /**
     * @param list<VideoSpeaker> $speakers
     */
    public function __construct(array $speakers)
    {
        $this->speakers = array_values($speakers);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<VideoSpeaker>
     */
    public function all(): array
    {
        return $this->speakers;
    }

    public function count(): int
    {
        return count($this->speakers);
    }

    public function isEmpty(): bool
    {
        return [] === $this->speakers;
    }
}
