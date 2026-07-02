<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Application\ShadowMemory\MemoryBuilder;
use App\Domain\ShadowTeaching\ShadowTeachingRepositoryInterface;
use App\Domain\ShadowTeaching\TeachingDifficulty;
use App\Domain\ShadowTeaching\TeachingPlan;
use App\Domain\ShadowTeaching\TeachingPreferences;
use App\Domain\ShadowTeaching\TeachingVoiceMode;

final class TeachingBuilder
{
    public function __construct(
        private readonly ShadowTeachingRepositoryInterface $repository,
        private readonly MemoryBuilder $memoryBuilder,
        private readonly TeachingPlanner $planner,
        private readonly TeachingProgressUpdater $progressUpdater,
    ) {
    }

    public function getOrCreate(string $scopeKey = 'default'): TeachingPlan
    {
        return $this->repository->findByScope($scopeKey) ?? TeachingPlan::create(scopeKey: $scopeKey);
    }

    public function syncPlan(string $scopeKey = 'default'): TeachingPlan
    {
        $memory = $this->memoryBuilder->ingestRelationship($scopeKey);
        $plan = $this->planner->plan($this->getOrCreate($scopeKey), $memory);
        $this->repository->save($plan);

        return $plan;
    }

    /** @param array<string, mixed> $payload */
    public function recordQuestion(string $scopeKey, array $payload): TeachingPlan
    {
        $this->memoryBuilder->recordPayload($scopeKey, [
            'source' => 'shadow',
            'kind' => 'question',
            'data' => $payload,
        ]);

        return $this->syncPlan($scopeKey);
    }

    /** @param array<string, mixed> $preferences */
    public function updatePreferences(string $scopeKey, array $preferences): TeachingPlan
    {
        $existing = $this->syncPlan($scopeKey)->preferences();
        $next = $existing
            ->withTeachingEnabled((bool) ($preferences['teachingEnabled'] ?? $existing->teachingEnabled()))
            ->withRevisionEnabled((bool) ($preferences['revisionEnabled'] ?? $existing->revisionEnabled()));

        if (is_string($preferences['voiceMode'] ?? null)) {
            $mode = TeachingVoiceMode::tryFrom($preferences['voiceMode']);

            if (null !== $mode) {
                $next = $next->withVoiceMode($mode);
            }
        }

        if (is_string($preferences['difficulty'] ?? null)) {
            $difficulty = TeachingDifficulty::tryFrom($preferences['difficulty']);

            if (null !== $difficulty) {
                $next = $next->withDifficulty($difficulty);
            }
        }

        $saved = $this->getOrCreate($scopeKey)->withPreferences($next);
        $saved = $this->planner->plan($saved, $this->memoryBuilder->ingestRelationship($scopeKey));
        $this->repository->save($saved);

        return $saved;
    }

    public function answerExercise(string $scopeKey, string $exerciseId, string $answer): TeachingPlan
    {
        $plan = $this->progressUpdater->answerExercise($this->syncPlan($scopeKey), $exerciseId, $answer);
        $this->repository->save($plan);

        return $plan;
    }

    public function completeCheckpoint(string $scopeKey, string $checkpointId): TeachingPlan
    {
        $plan = $this->progressUpdater->completeCheckpoint($this->syncPlan($scopeKey), $checkpointId);
        $this->repository->save($plan);

        return $plan;
    }

    public function reset(string $scopeKey = 'default'): TeachingPlan
    {
        $plan = $this->getOrCreate($scopeKey)->reset();
        $this->repository->save($plan);

        return $this->syncPlan($scopeKey);
    }
}
