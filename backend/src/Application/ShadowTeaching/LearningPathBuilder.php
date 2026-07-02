<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Domain\ShadowMemory\KnowledgeProgress;
use App\Domain\ShadowMemory\MemoryTimeline;
use App\Domain\ShadowTeaching\LearningModule;
use App\Domain\ShadowTeaching\LearningObjective;
use App\Domain\ShadowTeaching\LearningObjectiveCollection;
use App\Domain\ShadowTeaching\LearningPath;
use App\Domain\ShadowTeaching\TeachingProgressStatus;

final class LearningPathBuilder
{
    /** @var list<array{key: string, title: string, prerequisites: list<string>}> */
    private const CURRICULUM = [
        ['key' => 'dependency_injection', 'title' => 'Dependency Injection', 'prerequisites' => []],
        ['key' => 'symfony_messenger', 'title' => 'Symfony Messenger', 'prerequisites' => ['dependency_injection']],
        ['key' => 'repository_pattern', 'title' => 'Repository Pattern', 'prerequisites' => ['dependency_injection']],
        ['key' => 'ddd', 'title' => 'Domain-Driven Design', 'prerequisites' => ['repository_pattern']],
        ['key' => 'cqrs', 'title' => 'CQRS', 'prerequisites' => ['ddd', 'symfony_messenger']],
        ['key' => 'docker', 'title' => 'Docker', 'prerequisites' => []],
        ['key' => 'kubernetes', 'title' => 'Kubernetes', 'prerequisites' => ['docker']],
    ];

    public function __construct(private readonly LearningObjectiveResolver $objectiveResolver)
    {
    }

    public function build(MemoryTimeline $timeline): LearningPath
    {
        $objectives = [];
        $knownKeys = array_map(
            static fn ($item) => $item->key(),
            $timeline->knowledge()->all(),
        );

        foreach (self::CURRICULUM as $step) {
            $item = $timeline->knowledge()->find($step['key']);

            if (null !== $item) {
                $objectives[] = $this->objectiveResolver->fromKnowledgeItem($item, $step['prerequisites']);
                continue;
            }

            if ($this->prerequisitesMet($step['prerequisites'], $knownKeys, $timeline)) {
                $objectives[] = LearningObjective::create(
                    $step['key'],
                    $step['title'],
                    'Available next in your personalized curriculum.',
                    [$step['key']],
                    $step['prerequisites'],
                    TeachingProgressStatus::NotStarted,
                    0,
                    'Unlocked because prerequisite concepts are known.',
                );
            }
        }

        foreach ($timeline->knowledge()->all() as $item) {
            if (null === $this->findObjective($objectives, $item->key())) {
                $objectives[] = $this->objectiveResolver->fromKnowledgeItem($item);
            }
        }

        $module = new LearningModule('core', 'Core curriculum', $objectives);

        return new LearningPath(
            'Senior developer path',
            'Grow from foundations to cloud-native architecture.',
            [$module],
        );
    }

    public function objectives(MemoryTimeline $timeline): LearningObjectiveCollection
    {
        $path = $this->build($timeline);
        $items = [];

        foreach ($path->modules() as $module) {
            foreach ($module->objectives() as $objective) {
                $items[] = $objective;
            }
        }

        return new LearningObjectiveCollection($items);
    }

    /** @param list<string> $prerequisites */
    private function prerequisitesMet(array $prerequisites, array $knownKeys, MemoryTimeline $timeline): bool
    {
        if ([] === $prerequisites) {
            return true;
        }

        foreach ($prerequisites as $key) {
            $item = $timeline->knowledge()->find($key);

            if (null === $item || KnowledgeProgress::Mastered !== $item->progress()) {
                return false;
            }
        }

        return true;
    }

    /** @param list<LearningObjective> $objectives */
    private function findObjective(array $objectives, string $key): ?LearningObjective
    {
        foreach ($objectives as $objective) {
            if ($objective->key() === $key) {
                return $objective;
            }
        }

        return null;
    }
}
