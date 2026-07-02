<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

enum MentorMissionStatus: string
{
    case Upcoming = 'upcoming';
    case Active = 'active';
    case Completed = 'completed';
}
