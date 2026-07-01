<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Collaboration;

use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceInvitation;
use App\Domain\Collaboration\WorkspaceInvitationRepositoryInterface;
use App\Domain\Collaboration\WorkspaceInvitationStatus;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineWorkspaceInvitationRepository implements WorkspaceInvitationRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CollaborationJsonMapper $mapper,
    ) {
    }

    public function save(WorkspaceInvitation $invitation): void
    {
        $existing = $this->entityManager->find(WorkspaceInvitationRecord::class, $invitation->id()->value);

        if (null !== $existing) {
            $this->entityManager->remove($existing);
            $this->entityManager->flush();
        }

        $this->entityManager->persist(
            WorkspaceInvitationRecord::fromPayload($this->mapper->invitationToArray($invitation)),
        );
        $this->entityManager->flush();
    }

    public function findPendingByWorkspaceId(WorkspaceId $workspaceId): array
    {
        /** @var list<WorkspaceInvitationRecord> $records */
        $records = $this->entityManager->getRepository(WorkspaceInvitationRecord::class)->findBy([
            'workspaceId' => $workspaceId->value,
            'status' => WorkspaceInvitationStatus::Pending->value,
        ], ['createdAt' => 'DESC']);

        return array_map(
            fn (WorkspaceInvitationRecord $record): WorkspaceInvitation => $this->mapper->invitationFromArray($record->toPayload()),
            $records,
        );
    }

    public function findByToken(string $token): ?WorkspaceInvitation
    {
        $record = $this->entityManager->getRepository(WorkspaceInvitationRecord::class)->findOneBy([
            'token' => $token,
        ]);

        return null === $record ? null : $this->mapper->invitationFromArray($record->toPayload());
    }
}
