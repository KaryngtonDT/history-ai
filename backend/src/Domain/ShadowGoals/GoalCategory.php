<?php

declare(strict_types=1);

namespace App\Domain\ShadowGoals;

enum GoalCategory: string
{
    case Career = 'career';
    case Language = 'language';
    case Programming = 'programming';
    case History = 'history';
    case Philosophy = 'philosophy';
    case University = 'university';
    case Certification = 'certification';
    case Personal = 'personal';
    case Custom = 'custom';
}
