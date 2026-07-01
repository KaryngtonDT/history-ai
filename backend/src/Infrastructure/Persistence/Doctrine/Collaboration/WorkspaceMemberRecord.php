<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Collaboration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'workspace_members')]
#[ORM\UniqueConstraint(name: 'uniq_workspace_member_user', columns: ['workspace_id', 'user_id'])]
class WorkspaceMemberRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(name: 'workspace_id', type: Types::GUID)]
    private string $workspaceId;

    #[ORM\Column(name: 'user_id', type: Types::STRING, length: 255)]
    private string $userId;

    #[ORM\Column(name: 'display_name', type: Types::STRING, length: 255)]
    private string $displayName;

    #[ORM\Column(type: Types::STRING, length: 32)]
    private string $role;

    #[ORM\Column(name: 'joined_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $joinedAt;

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        $record = new self();
        $record->id = (string) $payload['id'];
        $record->workspaceId = (string) $payload['workspaceId'];
        $record->userId = (string) $payload['userId'];
        $record->displayName = (string) $payload['displayName'];
        $record->role = (string) $payload['role'];
        $record->joinedAt = new \DateTimeImmutable((string) $payload['joinedAt']);

        return $record;
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'id' => $this->id,
            'workspaceId' => $this->workspaceId,
            'userId' => $this->userId,
            'displayName' => $this->displayName,
            'role' => $this->role,
            'joinedAt' => $this->joinedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
