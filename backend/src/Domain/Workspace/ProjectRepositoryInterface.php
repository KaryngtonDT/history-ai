<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

interface ProjectRepositoryInterface
{
    public function save(Project $project): void;

    public function findById(ProjectId $id): ?Project;

    /**
     * @return list<Project>
     */
    public function findAll(): array;

    public function delete(ProjectId $id): void;
}
