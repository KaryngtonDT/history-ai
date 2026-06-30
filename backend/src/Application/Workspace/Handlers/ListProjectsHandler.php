<?php

declare(strict_types=1);

namespace App\Application\Workspace\Handlers;

use App\Application\Workspace\DTO\ProjectResult;
use App\Domain\Workspace\ProjectRepositoryInterface;

final class ListProjectsHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    /**
     * @return list<ProjectResult>
     */
    public function __invoke(): array
    {
        return array_map(
            static fn ($project): ProjectResult => ProjectResult::fromProject($project),
            $this->projectRepository->findAll(),
        );
    }
}
