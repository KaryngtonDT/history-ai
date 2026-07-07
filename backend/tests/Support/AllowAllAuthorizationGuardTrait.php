<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Application\Collaboration\WorkspaceAuthorizationGuard;
use App\Application\Collaboration\WorkspaceAuthorizationService;
use App\Domain\Collaboration\WorkspaceMemberCollection;
use App\Domain\Collaboration\WorkspaceMemberRepositoryInterface;
use App\Domain\Workspace\ProjectRepositoryInterface;

trait AllowAllAuthorizationGuardTrait
{
    private function allowAllAuthorizationGuard(): WorkspaceAuthorizationGuard
    {
        /** @var WorkspaceMemberRepositoryInterface $memberRepository */
        $memberRepository = $this->createStub(WorkspaceMemberRepositoryInterface::class);
        $memberRepository->method('findByWorkspaceId')->willReturn(WorkspaceMemberCollection::empty());

        /** @var ProjectRepositoryInterface $projectRepository */
        $projectRepository = $this->createStub(ProjectRepositoryInterface::class);
        $projectRepository->method('findProjectIdByVideoId')->willReturn(null);

        return new WorkspaceAuthorizationGuard(
            new WorkspaceAuthorizationService($memberRepository),
            $projectRepository,
        );
    }
}
