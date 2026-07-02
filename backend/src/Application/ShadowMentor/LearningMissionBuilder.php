<?php

declare(strict_types=1);

namespace App\Application\ShadowMentor;

use App\Domain\ShadowGoals\LearningGoal;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowMentor\MentorMission;
use App\Domain\ShadowMentor\MentorMissionCollection;

final class LearningMissionBuilder
{
    /** @var list<array{string, string, string}> */
    private const PRESET = [
        ['dependency_injection', 'Understand Dependency Injection', 'dependency_injection'],
        ['doctrine', 'Doctrine Repository', 'doctrine'],
        ['ddd', 'Domain-Driven Design basics', 'ddd'],
        ['docker', 'Docker fundamentals', 'docker'],
        ['kubernetes', 'Kubernetes orchestration', 'kubernetes'],
    ];

    public function build(LearningGoal $goal, KnowledgeGraph $graph): MentorMissionCollection
    {
        $collection = MentorMissionCollection::empty();
        $activeSet = false;

        foreach (self::PRESET as [$key, $title, $conceptKey]) {
            if (null === $graph->nodes()->find($key) && !in_array($key, $goal->requiredKnowledge(), true)) {
                continue;
            }

            $mission = MentorMission::create(
                $goal->id(),
                $title,
                sprintf('Master %s to progress toward %s.', $title, $goal->title()),
                20,
                $conceptKey,
                $this->prerequisitesFor($key),
            );

            if (!$activeSet) {
                $mission = $mission->activate();
                $activeSet = true;
            }

            $collection = $collection->append($mission);
        }

        if ($collection->all() === []) {
            $mission = MentorMission::create(
                $goal->id(),
                'Define your first milestone',
                'Clarify the first concrete step toward your goal.',
                15,
                'goal_start',
            )->activate();

            $collection = $collection->append($mission);
        }

        return $collection;
    }

    /** @return list<string> */
    private function prerequisitesFor(string $key): array
    {
        return match ($key) {
            'doctrine' => ['dependency_injection'],
            'ddd' => ['symfony_messenger', 'repository_pattern'],
            'kubernetes' => ['docker'],
            default => [],
        };
    }
}
