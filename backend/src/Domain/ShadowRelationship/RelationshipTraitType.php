<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

enum RelationshipTraitType: string
{
    case Interest = 'interest';
    case Habit = 'habit';
    case Motivator = 'motivator';
    case Communication = 'communication';
}
