<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Domain\ShadowTeaching\ExerciseType;
use App\Domain\ShadowTeaching\LearningObjective;
use App\Domain\ShadowTeaching\TeachingExercise;
use App\Domain\ShadowTeaching\TeachingExerciseCollection;

final class ExercisePlanner
{
    public function forObjective(LearningObjective $objective): TeachingExerciseCollection
    {
        $exercises = [];

        $exercises[] = TeachingExercise::create(
            ExerciseType::Quiz,
            sprintf('What is the main idea behind %s?', $objective->title()),
            [],
            $objective->title(),
            $objective->explanation(),
            $objective->key(),
        );

        $exercises[] = TeachingExercise::create(
            ExerciseType::TrueFalse,
            sprintf('%s is already part of your learning path.', $objective->title()),
            ['true', 'false'],
            'true',
            'Shadow tracks this concept in your teaching plan.',
            $objective->key(),
        );

        $exercises[] = TeachingExercise::create(
            ExerciseType::ExplainBack,
            sprintf('Explain %s as if you were teaching it to someone else.', $objective->title()),
            [],
            $objective->title(),
            'A strong explanation mentions the core idea and one concrete example.',
            $objective->key(),
        );

        return new TeachingExerciseCollection($exercises);
    }
}
