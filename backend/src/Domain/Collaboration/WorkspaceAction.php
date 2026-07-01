<?php

declare(strict_types=1);

namespace App\Domain\Collaboration;

enum WorkspaceAction: string
{
    case ManageMembers = 'manage_members';
    case DeleteWorkspace = 'delete_workspace';
    case ManagePipelineDefaults = 'manage_pipeline_defaults';
    case Upload = 'upload';
    case Process = 'process';
    case Reprocess = 'reprocess';
    case Review = 'review';
    case Compare = 'compare';
    case Comment = 'comment';
    case Read = 'read';
    case ManageProjects = 'manage_projects';
}
