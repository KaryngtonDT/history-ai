<?php

declare(strict_types=1);

namespace App\Application\Workspace\DTO;

use App\Domain\Workspace\BatchJob;
use App\Domain\Workspace\Project;

final readonly class ProjectResult
{
    /**
     * @param list<ProjectVideoResult> $videos
     * @param list<string> $targetLanguages
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $createdAt,
        public array $videos,
        public ?string $batchJobId = null,
        public ?string $batchStatus = null,
        public ?int $batchProgress = null,
        public array $targetLanguages = [],
    ) {
    }

    public static function fromProject(Project $project, ?BatchJob $batchJob = null): self
    {
        $videos = array_map(
            static fn ($video): ProjectVideoResult => ProjectVideoResult::fromVideo($video),
            $project->videos()->all(),
        );

        return new self(
            $project->id()->value,
            $project->name(),
            $project->createdAt()->format(DATE_ATOM),
            $videos,
            $batchJob?->id()->value,
            $batchJob?->status()->value,
            $batchJob?->progress()->percentage(),
            $batchJob?->targetLanguages() ?? [],
        );
    }
}
