<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Collaboration;

use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\Collaboration\WorkspaceInvitation;
use App\Domain\Collaboration\WorkspaceInvitationId;
use App\Domain\Collaboration\WorkspaceMember;
use App\Domain\Collaboration\WorkspaceMemberCollection;
use App\Domain\Collaboration\WorkspaceMemberId;
use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceRole;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CollaborationDomainTest extends TestCase
{
    public function testOwnerHasFullPermissions(): void
    {
        self::assertTrue(WorkspaceRole::Owner->allows(WorkspaceAction::ManageMembers));
        self::assertTrue(WorkspaceRole::Owner->allows(WorkspaceAction::Process));
        self::assertTrue(WorkspaceRole::Owner->allows(WorkspaceAction::Review));
    }

    public function testEditorCanProcessButNotManageMembers(): void
    {
        self::assertTrue(WorkspaceRole::Editor->allows(WorkspaceAction::Upload));
        self::assertTrue(WorkspaceRole::Editor->allows(WorkspaceAction::Process));
        self::assertFalse(WorkspaceRole::Editor->allows(WorkspaceAction::ManageMembers));
    }

    public function testReviewerCanReviewButNotProcess(): void
    {
        self::assertTrue(WorkspaceRole::Reviewer->allows(WorkspaceAction::Review));
        self::assertTrue(WorkspaceRole::Reviewer->allows(WorkspaceAction::Compare));
        self::assertFalse(WorkspaceRole::Reviewer->allows(WorkspaceAction::Process));
    }

    public function testViewerIsReadOnly(): void
    {
        self::assertTrue(WorkspaceRole::Viewer->allows(WorkspaceAction::Read));
        self::assertFalse(WorkspaceRole::Viewer->allows(WorkspaceAction::Review));
        self::assertFalse(WorkspaceRole::Viewer->allows(WorkspaceAction::Upload));
    }

    public function testAppendMember(): void
    {
        $workspaceId = WorkspaceId::generate();
        $collection = WorkspaceMemberCollection::empty()->append(
            $this->member($workspaceId, 'alice', WorkspaceRole::Owner),
        );

        self::assertSame(1, $collection->count());
        self::assertTrue($collection->hasUserId('alice'));
    }

    public function testRejectsDuplicateMembers(): void
    {
        $workspaceId = WorkspaceId::generate();
        $collection = WorkspaceMemberCollection::empty()->append(
            $this->member($workspaceId, 'alice', WorkspaceRole::Owner),
        );

        $this->expectException(InvalidWorkspaceMemberException::class);

        $collection->append($this->member($workspaceId, 'alice', WorkspaceRole::Editor));
    }

    public function testCannotRemoveLastOwner(): void
    {
        $workspaceId = WorkspaceId::generate();
        $owner = $this->member($workspaceId, 'alice', WorkspaceRole::Owner);
        $collection = WorkspaceMemberCollection::empty()->append($owner);

        $this->expectException(InvalidWorkspaceMemberException::class);

        $collection->remove($owner->id());
    }

    public function testCanRemoveOwnerWhenAnotherOwnerExists(): void
    {
        $workspaceId = WorkspaceId::generate();
        $alice = $this->member($workspaceId, 'alice', WorkspaceRole::Owner);
        $bob = $this->member($workspaceId, 'bob', WorkspaceRole::Owner);
        $collection = WorkspaceMemberCollection::empty()->append($alice)->append($bob);

        $updated = $collection->remove($alice->id());

        self::assertSame(1, $updated->count());
        self::assertFalse($updated->hasUserId('alice'));
    }

    public function testInvitationExpiresDeterministically(): void
    {
        $createdAt = new DateTimeImmutable('2026-06-01T10:00:00+00:00');
        $invitation = WorkspaceInvitation::createWithDuration(
            WorkspaceInvitationId::generate(),
            WorkspaceId::generate(),
            'bob@example.com',
            WorkspaceRole::Editor,
            'token-123',
            7,
            $createdAt,
        );

        self::assertTrue($invitation->isPending($createdAt->modify('+6 days')));
        self::assertFalse($invitation->isPending($createdAt->modify('+8 days')));
    }

    public function testAcceptPendingInvitation(): void
    {
        $now = new DateTimeImmutable('2026-06-01T10:00:00+00:00');
        $invitation = WorkspaceInvitation::createWithDuration(
            WorkspaceInvitationId::generate(),
            WorkspaceId::generate(),
            'charlie@example.com',
            WorkspaceRole::Reviewer,
            'token-456',
            7,
            $now,
        );

        $accepted = $invitation->accept($now->modify('+1 day'));

        self::assertSame('accepted', $accepted->status()->value);
    }

    public function testCannotAcceptExpiredInvitation(): void
    {
        $now = new DateTimeImmutable('2026-06-01T10:00:00+00:00');
        $invitation = WorkspaceInvitation::createWithDuration(
            WorkspaceInvitationId::generate(),
            WorkspaceId::generate(),
            'david@example.com',
            WorkspaceRole::Viewer,
            'token-789',
            3,
            $now,
        );

        $this->expectException(InvalidWorkspaceMemberException::class);

        $invitation->accept($now->modify('+4 days'));
    }

    private function member(WorkspaceId $workspaceId, string $userId, WorkspaceRole $role): WorkspaceMember
    {
        return WorkspaceMember::create(
            WorkspaceMemberId::generate(),
            $workspaceId,
            $userId,
            ucfirst($userId),
            $role,
        );
    }
}
