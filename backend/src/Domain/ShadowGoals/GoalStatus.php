<?php

declare(strict_types=1);

namespace App\Domain\ShadowGoals;

enum GoalStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Archived = 'archived';
}
