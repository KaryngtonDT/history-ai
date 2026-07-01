<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Collaboration;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'workspace_invitations')]
#[ORM\UniqueConstraint(name: 'uniq_workspace_invitation_token', columns: ['token'])]
class WorkspaceInvitationRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(name: 'workspace_id', type: Types::GUID)]
    private string $workspaceId;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $email;

    #[ORM\Column(type: Types::STRING, length: 32)]
    private string $role;

    #[ORM\Column(type: Types::STRING, length: 128)]
    private string $token;

    #[ORM\Column(type: Types::STRING, length: 32)]
    private string $status;

    #[ORM\Column(name: 'created_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'expires_at', type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $expiresAt;

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
        $record->email = (string) $payload['email'];
        $record->role = (string) $payload['role'];
        $record->token = (string) $payload['token'];
        $record->status = (string) $payload['status'];
        $record->createdAt = new \DateTimeImmutable((string) $payload['createdAt']);
        $record->expiresAt = new \DateTimeImmutable((string) $payload['expiresAt']);

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
            'email' => $this->email,
            'role' => $this->role,
            'token' => $this->token,
            'status' => $this->status,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'expiresAt' => $this->expiresAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
