<?php

declare(strict_types=1);

namespace App\Domain\History;

use App\Domain\Video\VideoId;

final readonly class ExecutionHistory
{
    public function __construct(
        private ExecutionHistoryId $id,
        private VideoId $videoId,
        private ExecutionVersionCollection $versions,
    ) {
    }

    public static function create(ExecutionHistoryId $id, VideoId $videoId): self
    {
        return new self($id, $videoId, ExecutionVersionCollection::empty());
    }

    public static function reconstitute(
        ExecutionHistoryId $id,
        VideoId $videoId,
        ExecutionVersionCollection $versions,
    ): self {
        return new self($id, $videoId, $versions);
    }

    public function appendVersion(ExecutionVersion $version): self
    {
        return new self(
            $this->id,
            $this->videoId,
            $this->versions->append($version),
        );
    }

    public function appendSnapshot(ExecutionSnapshot $snapshot, ?\DateTimeImmutable $createdAt = null): self
    {
        return $this->appendVersion(
            $snapshot->toVersion($this->versions->nextVersionNumber(), $createdAt),
        );
    }

    public function id(): ExecutionHistoryId
    {
        return $this->id;
    }

    public function videoId(): VideoId
    {
        return $this->videoId;
    }

    public function versions(): ExecutionVersionCollection
    {
        return $this->versions;
    }

    public function latest(): ?ExecutionVersion
    {
        return $this->versions->latest();
    }

    public function version(int $versionNumber): ExecutionVersion
    {
        return $this->versions->version($versionNumber);
    }

    public function isEmpty(): bool
    {
        return $this->versions->isEmpty();
    }

    public function count(): int
    {
        return $this->versions->count();
    }
}
