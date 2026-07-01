<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Collaboration;

use App\Application\Collaboration\AcceptWorkspaceInvitationHandler;
use App\Application\Collaboration\Commands\AcceptWorkspaceInvitationCommand;
use App\Application\Collaboration\Commands\InviteWorkspaceMemberCommand;
use App\Application\Collaboration\Commands\RemoveWorkspaceMemberCommand;
use App\Application\Collaboration\EnsureWorkspaceOwnerHandler;
use App\Application\Collaboration\InviteWorkspaceMemberHandler;
use App\Application\Collaboration\RemoveWorkspaceMemberHandler;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceInvitation;
use App\Domain\Collaboration\WorkspaceInvitationId;
use App\Domain\Collaboration\WorkspaceMember;
use App\Domain\Collaboration\WorkspaceMemberCollection;
use App\Domain\Collaboration\WorkspaceMemberId;
use App\Domain\Collaboration\WorkspaceMemberRepositoryInterface;
use App\Domain\Collaboration\WorkspaceInvitationRepositoryInterface;
use App\Domain\Collaboration\WorkspaceRole;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CollaborationHandlersTest extends TestCase
{
    public function testInviteCreatesDeterministicInvitation(): void
    {
        $workspaceId = WorkspaceId::generate();
        $members = new InMemoryWorkspaceMemberRepository();
        $invitations = new InMemoryWorkspaceInvitationRepository();
        $members->saveCollection(
            $workspaceId,
            WorkspaceMemberCollection::empty()->append($this->owner($workspaceId, 'alice')),
        );

        $handler = new InviteWorkspaceMemberHandler($members, $invitations);
        $result = $handler(new InviteWorkspaceMemberCommand(
            $workspaceId->value,
            'alice',
            'bob@example.com',
            WorkspaceRole::Editor,
        ));

        self::assertSame('bob@example.com', $result->email);
        self::assertSame('editor', $result->role);
        self::assertSame(
            hash('sha256', 'bob@example.com'.$workspaceId->value.WorkspaceRole::Editor->value),
            $result->token,
        );
    }

    public function testAcceptInvitationAddsMember(): void
    {
        $workspaceId = WorkspaceId::generate();
        $members = new InMemoryWorkspaceMemberRepository();
        $invitations = new InMemoryWorkspaceInvitationRepository();
        $token = 'invite-token';
        $invitations->save(WorkspaceInvitation::createWithDuration(
            WorkspaceInvitationId::generate(),
            $workspaceId,
            'bob@example.com',
            WorkspaceRole::Editor,
            $token,
            7,
        ));

        $handler = new AcceptWorkspaceInvitationHandler($invitations, $members);
        $result = $handler(new AcceptWorkspaceInvitationCommand($token, 'bob', 'Bob'));

        self::assertSame('bob', $result->userId);
        self::assertTrue($members->findByWorkspaceId($workspaceId)->hasUserId('bob'));
    }

    public function testRejectDuplicateMemberOnInvite(): void
    {
        $workspaceId = WorkspaceId::generate();
        $members = new InMemoryWorkspaceMemberRepository();
        $members->saveCollection(
            $workspaceId,
            WorkspaceMemberCollection::empty()->append($this->owner($workspaceId, 'alice')),
        );

        $handler = new InviteWorkspaceMemberHandler($members, new InMemoryWorkspaceInvitationRepository());

        $this->expectException(InvalidWorkspaceMemberException::class);

        $handler(new InviteWorkspaceMemberCommand(
            $workspaceId->value,
            'alice',
            'alice',
            WorkspaceRole::Editor,
        ));
    }

    public function testRemoveMemberProtectsLastOwner(): void
    {
        $workspaceId = WorkspaceId::generate();
        $members = new InMemoryWorkspaceMemberRepository();
        $owner = $this->owner($workspaceId, 'alice');
        $members->saveCollection($workspaceId, WorkspaceMemberCollection::empty()->append($owner));

        $handler = new RemoveWorkspaceMemberHandler($members);

        $this->expectException(InvalidWorkspaceMemberException::class);

        $handler(new RemoveWorkspaceMemberCommand(
            $workspaceId->value,
            $owner->id()->value,
            'alice',
        ));
    }

    public function testEnsureOwnerCreatesOwnerOnce(): void
    {
        $workspaceId = WorkspaceId::generate();
        $members = new InMemoryWorkspaceMemberRepository();
        $handler = new EnsureWorkspaceOwnerHandler($members);

        $handler->ensureOwner($workspaceId, 'alice', 'Alice');
        $handler->ensureOwner($workspaceId, 'bob', 'Bob');

        self::assertSame(1, $members->findByWorkspaceId($workspaceId)->count());
        self::assertTrue($members->findByWorkspaceId($workspaceId)->hasUserId('alice'));
    }

    private function owner(WorkspaceId $workspaceId, string $userId): WorkspaceMember
    {
        return WorkspaceMember::create(
            WorkspaceMemberId::generate(),
            $workspaceId,
            $userId,
            ucfirst($userId),
            WorkspaceRole::Owner,
        );
    }
}

final class InMemoryWorkspaceMemberRepository implements WorkspaceMemberRepositoryInterface
{
    /** @var array<string, WorkspaceMemberCollection> */
    private array $collections = [];

    public function saveCollection(WorkspaceId $workspaceId, WorkspaceMemberCollection $members): void
    {
        $this->collections[$workspaceId->value] = $members;
    }

    public function findByWorkspaceId(WorkspaceId $workspaceId): WorkspaceMemberCollection
    {
        return $this->collections[$workspaceId->value] ?? WorkspaceMemberCollection::empty();
    }

    public function findMember(WorkspaceId $workspaceId, string $userId): ?WorkspaceMember
    {
        return $this->findByWorkspaceId($workspaceId)->findByUserId($userId);
    }
}

final class InMemoryWorkspaceInvitationRepository implements WorkspaceInvitationRepositoryInterface
{
    /** @var array<string, WorkspaceInvitation> */
    private array $byToken = [];

    /** @var array<string, list<WorkspaceInvitation>> */
    private array $byWorkspace = [];

    public function save(WorkspaceInvitation $invitation): void
    {
        $this->byToken[$invitation->token()] = $invitation;
        $this->byWorkspace[$invitation->workspaceId()->value][] = $invitation;
    }

    public function findPendingByWorkspaceId(WorkspaceId $workspaceId): array
    {
        return $this->byWorkspace[$workspaceId->value] ?? [];
    }

    public function findByToken(string $token): ?WorkspaceInvitation
    {
        return $this->byToken[$token] ?? null;
    }
}
