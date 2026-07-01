<?php

declare(strict_types=1);

namespace App\Application\Collaboration;

use App\Application\Collaboration\DTO\WorkspaceMemberResult;
use App\Application\Collaboration\Queries\ListWorkspaceMembersQuery;
use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceMemberRepositoryInterface;

final class ListWorkspaceMembersHandler
{
    public function __construct(
        private readonly WorkspaceMemberRepositoryInterface $memberRepository,
    ) {
    }

    /**
     * @return list<WorkspaceMemberResult>
     */
    public function __invoke(ListWorkspaceMembersQuery $query): array
    {
        $members = $this->memberRepository->findByWorkspaceId(new WorkspaceId($query->workspaceId));

        return array_map(
            static fn ($member): WorkspaceMemberResult => WorkspaceMemberResultMapper::fromMember($member),
            $members->all(),
        );
    }
}
