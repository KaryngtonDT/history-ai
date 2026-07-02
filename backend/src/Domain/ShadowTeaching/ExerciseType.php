<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

enum ExerciseType: string
{
    case Quiz = 'quiz';
    case TrueFalse = 'true_false';
    case MultipleChoice = 'multiple_choice';
    case FillBlank = 'fill_blank';
    case ExplainBack = 'explain_back';
}
