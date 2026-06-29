<?php

declare(strict_types=1);

namespace App\Domain\Video;

final readonly class VideoJobCollection
{
    /** @var list<VideoJob> */
    private array $jobs;

    /**
     * @param list<VideoJob> $jobs
     */
    public function __construct(array $jobs)
    {
        $this->jobs = array_values($jobs);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<VideoJob>
     */
    public function all(): array
    {
        return $this->jobs;
    }

    public function count(): int
    {
        return count($this->jobs);
    }

    public function isEmpty(): bool
    {
        return [] === $this->jobs;
    }

    public function append(VideoJob $job): self
    {
        return new self([...$this->jobs, $job]);
    }
}
