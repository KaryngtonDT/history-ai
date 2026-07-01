<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

use App\Domain\Video\VideoId;

interface ProjectRepositoryInterface
{
    public function save(Project $project): void;

    public function findById(ProjectId $id): ?Project;

    public function findProjectIdByVideoId(VideoId $videoId): ?ProjectId;

    /**
     * @return list<Project>
     */
    public function findAll(): array;

    public function delete(ProjectId $id): void;
}
