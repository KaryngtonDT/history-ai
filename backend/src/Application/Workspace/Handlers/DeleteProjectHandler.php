<?php

declare(strict_types=1);

namespace App\Application\Workspace\Handlers;

use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;

final class DeleteProjectHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    public function __invoke(string $projectId): void
    {
        $id = new ProjectId($projectId);

        if (null === $this->projectRepository->findById($id)) {
            throw new InvalidProjectException('Project not found.');
        }

        $this->projectRepository->delete($id);
    }
}
