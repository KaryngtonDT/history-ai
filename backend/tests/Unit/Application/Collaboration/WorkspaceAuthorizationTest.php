<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Collaboration;

use App\Application\Collaboration\WorkspaceAuthorizationGuard;
use App\Application\Collaboration\WorkspaceAuthorizationService;
use App\Domain\Collaboration\Exception\InvalidWorkspaceMemberException;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\Collaboration\WorkspaceId;
use App\Domain\Collaboration\WorkspaceMember;
use App\Domain\Collaboration\WorkspaceMemberCollection;
use App\Domain\Collaboration\WorkspaceMemberId;
use App\Domain\Collaboration\WorkspaceMemberRepositoryInterface;
use App\Domain\Collaboration\WorkspaceRole;
use App\Domain\Video\VideoId;
use App\Domain\Workspace\Project;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;
use App\Domain\Workspace\ProjectVideo;
use PHPUnit\Framework\TestCase;

final class WorkspaceAuthorizationTest extends TestCase
{
    public function testEmptyMembershipAllowsBackwardCompatibility(): void
    {
        $service = new WorkspaceAuthorizationService($this->memberRepository([]));

        self::assertTrue($service->isAllowed(WorkspaceId::generate()->value, 'alice', WorkspaceAction::Process));
    }

    public function testViewerCannotProcess(): void
    {
        $workspaceId = WorkspaceId::generate();
        $service = new WorkspaceAuthorizationService($this->memberRepository([
            $this->member($workspaceId, 'david', WorkspaceRole::Viewer),
        ]));

        self::assertFalse($service->isAllowed($workspaceId->value, 'david', WorkspaceAction::Process));
    }

    public function testEditorCanProcessButReviewerCannot(): void
    {
        $workspaceId = WorkspaceId::generate();
        $service = new WorkspaceAuthorizationService($this->memberRepository([
            $this->member($workspaceId, 'bob', WorkspaceRole::Editor),
            $this->member($workspaceId, 'charlie', WorkspaceRole::Reviewer),
        ]));

        self::assertTrue($service->isAllowed($workspaceId->value, 'bob', WorkspaceAction::Process));
        self::assertFalse($service->isAllowed($workspaceId->value, 'charlie', WorkspaceAction::Process));
        self::assertTrue($service->isAllowed($workspaceId->value, 'charlie', WorkspaceAction::Review));
    }

    public function testGuardBlocksForbiddenProjectAction(): void
    {
        $workspaceId = WorkspaceId::generate();
        $guard = new WorkspaceAuthorizationGuard(
            new WorkspaceAuthorizationService($this->memberRepository([
                $this->member($workspaceId, 'david', WorkspaceRole::Viewer),
            ])),
            $this->createMock(ProjectRepositoryInterface::class),
        );

        $this->expectException(InvalidWorkspaceMemberException::class);

        $guard->assertProjectAction($workspaceId->value, 'david', WorkspaceAction::Upload);
    }

    public function testGuardAllowsVideoActionWhenVideoNotInProject(): void
    {
        $projectRepository = $this->createMock(ProjectRepositoryInterface::class);
        $projectRepository->method('findProjectIdByVideoId')->willReturn(null);

        $guard = new WorkspaceAuthorizationGuard(
            new WorkspaceAuthorizationService($this->memberRepository([
                $this->member(WorkspaceId::generate(), 'david', WorkspaceRole::Viewer),
            ])),
            $projectRepository,
        );

        $guard->assertVideoAction('550e8400-e29b-41d4-a716-446655440099', 'david', WorkspaceAction::Review);

        self::assertTrue(true);
    }

    public function testGuardEnforcesVideoActionWhenVideoBelongsToProject(): void
    {
        $workspaceId = WorkspaceId::generate();
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440010');
        $project = Project::create(new ProjectId($workspaceId->value), 'Campaign')
            ->addVideo(ProjectVideo::create($videoId, 'clip.mp4'));

        $projectRepository = $this->createMock(ProjectRepositoryInterface::class);
        $projectRepository->method('findProjectIdByVideoId')->willReturn($project->id());

        $guard = new WorkspaceAuthorizationGuard(
            new WorkspaceAuthorizationService($this->memberRepository([
                $this->member($workspaceId, 'david', WorkspaceRole::Viewer),
            ])),
            $projectRepository,
        );

        $this->expectException(InvalidWorkspaceMemberException::class);

        $guard->assertVideoAction($videoId->value, 'david', WorkspaceAction::Review);
    }

    /**
     * @param list<WorkspaceMember> $members
     */
    private function memberRepository(array $members): WorkspaceMemberRepositoryInterface
    {
        $collections = [];

        foreach ($members as $member) {
            $workspaceId = $member->workspaceId()->value;
            $collections[$workspaceId] = isset($collections[$workspaceId])
                ? $collections[$workspaceId]->append($member)
                : WorkspaceMemberCollection::empty()->append($member);
        }

        $repository = $this->createMock(WorkspaceMemberRepositoryInterface::class);
        $repository->method('findByWorkspaceId')->willReturnCallback(
            fn (WorkspaceId $workspaceId): WorkspaceMemberCollection => $collections[$workspaceId->value]
                ?? WorkspaceMemberCollection::empty(),
        );
        $repository->method('findMember')->willReturnCallback(
            fn (WorkspaceId $workspaceId, string $userId): ?WorkspaceMember => ($collections[$workspaceId->value] ?? WorkspaceMemberCollection::empty())
                ->findByUserId($userId),
        );

        return $repository;
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
