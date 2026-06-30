<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

use App\Domain\Video\VideoId;
use App\Domain\Workspace\Exception\InvalidProjectException;

final readonly class BatchJob
{
    /**
     * @param list<VideoId> $videoIds
     * @param list<string> $targetLanguages
     */
    public function __construct(
        private BatchJobId $id,
        private ProjectId $projectId,
        private array $videoIds,
        private array $targetLanguages,
        private BatchJobStatus $status,
        private BatchJobProgress $progress,
    ) {
        if ([] === $this->videoIds) {
            throw new InvalidProjectException('Batch job must contain at least one video.');
        }

        if ([] === $this->targetLanguages) {
            throw new InvalidProjectException('Batch job must contain at least one target language.');
        }

        $seen = [];

        foreach ($this->videoIds as $videoId) {
            $key = $videoId->value;

            if (isset($seen[$key])) {
                throw new InvalidProjectException(sprintf('Duplicate batch video "%s".', $key));
            }

            $seen[$key] = true;
        }
    }

    /**
     * @param list<VideoId> $videoIds
     * @param list<string> $targetLanguages
     */
    public static function create(
        BatchJobId $id,
        ProjectId $projectId,
        array $videoIds,
        array $targetLanguages,
    ): self {
        return new self(
            $id,
            $projectId,
            array_values($videoIds),
            array_values(array_filter($targetLanguages, static fn (string $language): bool => '' !== trim($language))),
            BatchJobStatus::Pending,
            BatchJobProgress::zero(),
        );
    }

    public function start(): self
    {
        return new self(
            $this->id,
            $this->projectId,
            $this->videoIds,
            $this->targetLanguages,
            BatchJobStatus::Running,
            $this->progress,
        );
    }

    public function withProgress(BatchJobProgress $progress): self
    {
        return new self(
            $this->id,
            $this->projectId,
            $this->videoIds,
            $this->targetLanguages,
            $this->status,
            $progress,
        );
    }

    public function withStatus(BatchJobStatus $status, BatchJobProgress $progress): self
    {
        return new self(
            $this->id,
            $this->projectId,
            $this->videoIds,
            $this->targetLanguages,
            $status,
            $progress,
        );
    }

    public static function resolveStatus(int $succeeded, int $failed, int $total): BatchJobStatus
    {
        if ($succeeded + $failed < $total) {
            return BatchJobStatus::Running;
        }

        if (0 === $succeeded) {
            return BatchJobStatus::Failed;
        }

        if ($failed > 0) {
            return BatchJobStatus::PartialFailure;
        }

        return BatchJobStatus::Completed;
    }

    public function id(): BatchJobId
    {
        return $this->id;
    }

    public function projectId(): ProjectId
    {
        return $this->projectId;
    }

    /**
     * @return list<VideoId>
     */
    public function videoIds(): array
    {
        return $this->videoIds;
    }

    /**
     * @return list<string>
     */
    public function targetLanguages(): array
    {
        return $this->targetLanguages;
    }

    public function status(): BatchJobStatus
    {
        return $this->status;
    }

    public function progress(): BatchJobProgress
    {
        return $this->progress;
    }

    public function totalVideos(): int
    {
        return count($this->videoIds);
    }
}
