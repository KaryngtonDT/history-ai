<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

enum DecisionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Deferred = 'deferred';
    case Ignored = 'ignored';
}
