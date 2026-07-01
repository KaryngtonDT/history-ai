<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Collaboration;

use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceMember;
use App\Domain\Collaboration\WorkspaceMemberCollection;
use App\Domain\Collaboration\WorkspaceMemberRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineWorkspaceMemberRepository implements WorkspaceMemberRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CollaborationJsonMapper $mapper,
    ) {
    }

    public function saveCollection(WorkspaceId $workspaceId, WorkspaceMemberCollection $members): void
    {
        $existing = $this->entityManager->getRepository(WorkspaceMemberRecord::class)->findBy([
            'workspaceId' => $workspaceId->value,
        ]);

        foreach ($existing as $record) {
            $this->entityManager->remove($record);
        }

        foreach ($members->all() as $member) {
            $this->entityManager->persist(
                WorkspaceMemberRecord::fromPayload($this->mapper->memberToArray($member)),
            );
        }

        $this->entityManager->flush();
    }

    public function findByWorkspaceId(WorkspaceId $workspaceId): WorkspaceMemberCollection
    {
        /** @var list<WorkspaceMemberRecord> $records */
        $records = $this->entityManager->getRepository(WorkspaceMemberRecord::class)->findBy([
            'workspaceId' => $workspaceId->value,
        ], ['joinedAt' => 'ASC']);

        $members = array_map(
            fn (WorkspaceMemberRecord $record): WorkspaceMember => $this->mapper->memberFromArray($record->toPayload()),
            $records,
        );

        return new WorkspaceMemberCollection($members);
    }

    public function findMember(WorkspaceId $workspaceId, string $userId): ?WorkspaceMember
    {
        return $this->findByWorkspaceId($workspaceId)->findByUserId($userId);
    }
}
