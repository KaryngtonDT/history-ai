<?php

declare(strict_types=1);

namespace App\Application\Collaboration;

use App\Application\Collaboration\DTO\WorkspaceInvitationResult;
use App\Application\Collaboration\Queries\ListWorkspaceInvitationsQuery;
use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceInvitationRepositoryInterface;
use DateTimeImmutable;

final class ListWorkspaceInvitationsHandler
{
    public function __construct(
        private readonly WorkspaceInvitationRepositoryInterface $invitationRepository,
    ) {
    }

    /**
     * @return list<WorkspaceInvitationResult>
     */
    public function __invoke(ListWorkspaceInvitationsQuery $query): array
    {
        $now = new DateTimeImmutable();
        $invitations = $this->invitationRepository->findPendingByWorkspaceId(new WorkspaceId($query->workspaceId));

        return array_values(array_filter(array_map(
            static function ($invitation) use ($now): ?WorkspaceInvitationResult {
                if (!$invitation->isPending($now)) {
                    return null;
                }

                return WorkspaceInvitationResultMapper::fromInvitation($invitation);
            },
            $invitations,
        )));
    }
}
