<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

enum ExerciseStatus: string
{
    case Pending = 'pending';
    case Answered = 'answered';
    case Correct = 'correct';
    case Incorrect = 'incorrect';
}
