<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Domain\ShadowExecutive\DecisionType;
use App\Domain\ShadowExecutive\ExecutivePriority;
use App\Domain\ShadowExecutive\ExecutiveRecommendation;
use App\Domain\ShadowExecutive\ExecutiveRecommendationCollection;
use App\Domain\ShadowMentor\MentorMission;
use App\Domain\ShadowMentor\MentorPlan;
use App\Domain\ShadowTeaching\ExerciseStatus;
use App\Domain\ShadowTeaching\TeachingPlan;

final class ResourceRecommendationEngine
{
    public function recommend(MentorPlan $mentorPlan, TeachingPlan $teaching): ExecutiveRecommendationCollection
    {
        $collection = ExecutiveRecommendationCollection::empty();
        $currentMission = $mentorPlan->currentMission();

        if (null !== $currentMission) {
            $collection = $collection->append(
                ExecutiveRecommendation::create(
                    DecisionType::RecommendMission,
                    sprintf('Continue mission: %s', $currentMission->title()),
                    $currentMission->objective(),
                    ExecutivePriority::High,
                    $currentMission->unlockedConceptKey() !== '' ? $currentMission->unlockedConceptKey() : null,
                    $currentMission->id(),
                ),
            );
        }

        foreach ($teaching->exercises()->all() as $exercise) {
            if (ExerciseStatus::Correct === $exercise->status()) {
                continue;
            }

            $collection = $collection->append(
                ExecutiveRecommendation::create(
                    DecisionType::RecommendExercise,
                    sprintf('Practice: %s', $exercise->question()),
                    $exercise->explanation(),
                    ExecutivePriority::Normal,
                    $exercise->objectiveKey() !== '' ? $exercise->objectiveKey() : null,
                ),
            );
        }

        if (null !== $currentMission) {
            $collection = $collection->append(
                ExecutiveRecommendation::create(
                    DecisionType::RecommendVideo,
                    'Watch a focused video for the current mission',
                    $this->videoDetail($currentMission),
                    ExecutivePriority::Normal,
                    $currentMission->unlockedConceptKey() !== '' ? $currentMission->unlockedConceptKey() : null,
                ),
            );

            $collection = $collection->append(
                ExecutiveRecommendation::create(
                    DecisionType::RecommendPdf,
                    'Read supporting material for the current mission',
                    sprintf('Review documentation related to %s.', $currentMission->title()),
                    ExecutivePriority::Low,
                    $currentMission->unlockedConceptKey() !== '' ? $currentMission->unlockedConceptKey() : null,
                ),
            );
        }

        return $collection;
    }

    private function videoDetail(MentorMission $mission): string
    {
        if ([] === $mission->prerequisiteKeys()) {
            return sprintf('Pick a video that advances %s.', $mission->title());
        }

        return sprintf(
            'Ensure prerequisites (%s) are solid, then watch content for %s.',
            implode(', ', $mission->prerequisiteKeys()),
            $mission->title(),
        );
    }
}
