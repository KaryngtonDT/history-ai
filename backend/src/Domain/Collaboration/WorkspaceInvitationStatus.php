<?php

declare(strict_types=1);

namespace App\Domain\Collaboration;

enum WorkspaceInvitationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
