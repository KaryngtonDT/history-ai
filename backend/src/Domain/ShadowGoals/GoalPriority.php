<?php

declare(strict_types=1);

namespace App\Domain\ShadowGoals;

enum GoalPriority: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Background = 'background';
}
