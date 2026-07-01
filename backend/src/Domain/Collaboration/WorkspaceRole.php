<?php

declare(strict_types=1);

namespace App\Domain\Collaboration;

enum WorkspaceRole: string
{
    case Owner = 'owner';
    case Editor = 'editor';
    case Reviewer = 'reviewer';
    case Viewer = 'viewer';

    public function canManageMembers(): bool
    {
        return self::Owner === $this;
    }

    public function canDeleteWorkspace(): bool
    {
        return self::Owner === $this;
    }

    public function canManagePipelineDefaults(): bool
    {
        return self::Owner === $this;
    }

    public function canUpload(): bool
    {
        return match ($this) {
            self::Owner, self::Editor => true,
            self::Reviewer, self::Viewer => false,
        };
    }

    public function canProcess(): bool
    {
        return match ($this) {
            self::Owner, self::Editor => true,
            self::Reviewer, self::Viewer => false,
        };
    }

    public function canReprocess(): bool
    {
        return match ($this) {
            self::Owner, self::Editor => true,
            self::Reviewer, self::Viewer => false,
        };
    }

    public function canReview(): bool
    {
        return match ($this) {
            self::Owner, self::Editor, self::Reviewer => true,
            self::Viewer => false,
        };
    }

    public function canCompare(): bool
    {
        return $this->canReview();
    }

    public function canComment(): bool
    {
        return $this->canReview();
    }

    public function canRead(): bool
    {
        return true;
    }

    public function allows(WorkspaceAction $action): bool
    {
        return match ($action) {
            WorkspaceAction::ManageMembers => $this->canManageMembers(),
            WorkspaceAction::DeleteWorkspace => $this->canDeleteWorkspace(),
            WorkspaceAction::ManagePipelineDefaults => $this->canManagePipelineDefaults(),
            WorkspaceAction::Upload => $this->canUpload(),
            WorkspaceAction::Process => $this->canProcess(),
            WorkspaceAction::Reprocess => $this->canReprocess(),
            WorkspaceAction::Review => $this->canReview(),
            WorkspaceAction::Compare => $this->canCompare(),
            WorkspaceAction::Comment => $this->canComment(),
            WorkspaceAction::Read => $this->canRead(),
            WorkspaceAction::ManageProjects => $this->canManageMembers() || self::Editor === $this,
        };
    }
}
