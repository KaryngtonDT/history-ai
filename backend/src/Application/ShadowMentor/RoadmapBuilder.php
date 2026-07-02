<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\LearningGoal;
use App\Domain\ShadowMentor\RoadmapHorizon;
use App\Domain\ShadowMentor\RoadmapStep;
use App\Domain\ShadowMentor\RoadmapStepCollection;

final class RoadmapBuilder
{
    /** @var list<string> */
    private const BACKEND_PATH = [
        'PHP', 'Symfony', 'Doctrine', 'DDD', 'CQRS', 'Architecture',
        'Microservices', 'Docker', 'Kubernetes', 'Cloud',
    ];

    public function build(?LearningGoal $primaryGoal): RoadmapStepCollection
    {
        if (null === $primaryGoal) {
            return RoadmapStepCollection::empty();
        }

        $labels = $this->labelsForGoal($primaryGoal);
        $collection = RoadmapStepCollection::empty();
        $horizons = [
            RoadmapHorizon::Today,
            RoadmapHorizon::Week,
            RoadmapHorizon::Month,
            RoadmapHorizon::Quarter,
            RoadmapHorizon::Goal,
        ];

        foreach ($horizons as $index => $horizon) {
            $label = $labels[$index] ?? $primaryGoal->title();
            $collection = $collection->append(RoadmapStep::create(
                $horizon,
                $label,
                $this->detailForHorizon($horizon, $label),
                $index,
            ));
        }

        return $collection;
    }

    /** @return list<string> */
    private function labelsForGoal(LearningGoal $goal): array
    {
        $lower = strtolower($goal->title());

        if (str_contains($lower, 'backend') || str_contains($lower, 'php') || str_contains($lower, 'developer')) {
            return [
                self::BACKEND_PATH[0],
                self::BACKEND_PATH[2],
                self::BACKEND_PATH[4],
                self::BACKEND_PATH[7],
                self::BACKEND_PATH[9],
            ];
        }

        if ([] !== $goal->targetSkills()) {
            $skills = $goal->targetSkills();

            return [
                $skills[0] ?? $goal->title(),
                $skills[1] ?? 'Practice',
                $skills[2] ?? 'Apply',
                $skills[3] ?? 'Integrate',
                $goal->title(),
            ];
        }

        return [$goal->title(), 'Practice', 'Apply', 'Integrate', 'Achieve goal'];
    }

    private function detailForHorizon(RoadmapHorizon $horizon, string $label): string
    {
        return match ($horizon) {
            RoadmapHorizon::Today => sprintf('Focus on %s in your next session.', $label),
            RoadmapHorizon::Week => sprintf('Complete one mission on %s this week.', $label),
            RoadmapHorizon::Month => sprintf('Reach intermediate mastery on %s.', $label),
            RoadmapHorizon::Quarter => sprintf('Connect %s to adjacent skills.', $label),
            RoadmapHorizon::Goal => sprintf('Achieve your goal through %s mastery.', $label),
        };
    }
}
