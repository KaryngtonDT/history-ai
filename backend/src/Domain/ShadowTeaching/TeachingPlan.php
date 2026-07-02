<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

use App\Domain\ShadowTeaching\Exception\InvalidShadowTeachingException;

final readonly class TeachingPlan
{
    public function __construct(
        private TeachingPlanId $id,
        private string $scopeKey,
        private LearningPath $path,
        private LearningObjectiveCollection $objectives,
        private TeachingExerciseCollection $exercises,
        private RevisionItemCollection $revisions,
        private LearningCheckpointCollection $checkpoints,
        private TeachingMissionCollection $missions,
        private TeachingHistoryCollection $history,
        private TeachingPreferences $preferences,
        private ?string $currentObjectiveKey,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidShadowTeachingException('Teaching plan scope cannot be empty.');
        }
    }

    public static function create(
        ?TeachingPlanId $id = null,
        string $scopeKey = 'default',
    ): self {
        return new self(
            $id ?? TeachingPlanId::generate(),
            trim($scopeKey),
            LearningPath::empty(),
            LearningObjectiveCollection::empty(),
            TeachingExerciseCollection::empty(),
            RevisionItemCollection::empty(),
            LearningCheckpointCollection::empty(),
            TeachingMissionCollection::empty(),
            TeachingHistoryCollection::empty(),
            TeachingPreferences::defaults(),
            null,
        );
    }

    public function id(): TeachingPlanId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function path(): LearningPath
    {
        return $this->path;
    }

    public function objectives(): LearningObjectiveCollection
    {
        return $this->objectives;
    }

    public function exercises(): TeachingExerciseCollection
    {
        return $this->exercises;
    }

    public function revisions(): RevisionItemCollection
    {
        return $this->revisions;
    }

    public function checkpoints(): LearningCheckpointCollection
    {
        return $this->checkpoints;
    }

    public function missions(): TeachingMissionCollection
    {
        return $this->missions;
    }

    public function history(): TeachingHistoryCollection
    {
        return $this->history;
    }

    public function preferences(): TeachingPreferences
    {
        return $this->preferences;
    }

    public function currentObjectiveKey(): ?string
    {
        return $this->currentObjectiveKey;
    }

    public function currentObjective(): ?LearningObjective
    {
        return null !== $this->currentObjectiveKey
            ? $this->objectives->find($this->currentObjectiveKey)
            : null;
    }

    public function withPath(LearningPath $path): self
    {
        return $this->replace(path: $path);
    }

    public function withObjectives(LearningObjectiveCollection $objectives): self
    {
        return $this->replace(objectives: $objectives);
    }

    public function withExercises(TeachingExerciseCollection $exercises): self
    {
        return $this->replace(exercises: $exercises);
    }

    public function withRevisions(RevisionItemCollection $revisions): self
    {
        return $this->replace(revisions: $revisions);
    }

    public function withCheckpoints(LearningCheckpointCollection $checkpoints): self
    {
        return $this->replace(checkpoints: $checkpoints);
    }

    public function withMissions(TeachingMissionCollection $missions): self
    {
        return $this->replace(missions: $missions);
    }

    public function withHistory(TeachingHistoryCollection $history): self
    {
        return $this->replace(history: $history);
    }

    public function withPreferences(TeachingPreferences $preferences): self
    {
        return $this->replace(preferences: $preferences);
    }

    public function withCurrentObjectiveKey(?string $currentObjectiveKey): self
    {
        return $this->replace(currentObjectiveKey: $currentObjectiveKey);
    }

    public function upsertObjective(LearningObjective $objective): self
    {
        return $this->withObjectives($this->objectives->upsert($objective));
    }

    public function upsertExercise(TeachingExercise $exercise): self
    {
        return $this->withExercises($this->exercises->upsert($exercise));
    }

    public function upsertCheckpoint(LearningCheckpoint $checkpoint): self
    {
        return $this->withCheckpoints($this->checkpoints->upsert($checkpoint));
    }

    public function addHistory(TeachingSessionRecord $record): self
    {
        return $this->withHistory($this->history->append($record));
    }

    public function reset(): self
    {
        $plan = self::create($this->id, $this->scopeKey);

        return $plan->addHistory(TeachingSessionRecord::record(
            'Teaching plan reset',
            'User reset the teaching plan.',
        ));
    }

    private function replace(
        ?LearningPath $path = null,
        ?LearningObjectiveCollection $objectives = null,
        ?TeachingExerciseCollection $exercises = null,
        ?RevisionItemCollection $revisions = null,
        ?LearningCheckpointCollection $checkpoints = null,
        ?TeachingMissionCollection $missions = null,
        ?TeachingHistoryCollection $history = null,
        ?TeachingPreferences $preferences = null,
        ?string $currentObjectiveKey = null,
        bool $preserveCurrentObjective = true,
    ): self {
        return new self(
            $this->id,
            $this->scopeKey,
            $path ?? $this->path,
            $objectives ?? $this->objectives,
            $exercises ?? $this->exercises,
            $revisions ?? $this->revisions,
            $checkpoints ?? $this->checkpoints,
            $missions ?? $this->missions,
            $history ?? $this->history,
            $preferences ?? $this->preferences,
            $preserveCurrentObjective ? ($currentObjectiveKey ?? $this->currentObjectiveKey) : $currentObjectiveKey,
        );
    }
}
