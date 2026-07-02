<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowTeaching;

use App\Domain\ShadowTeaching\Exception\InvalidShadowTeachingException;
use App\Domain\ShadowTeaching\ExerciseStatus;
use App\Domain\ShadowTeaching\ExerciseType;
use App\Domain\ShadowTeaching\LearningCheckpoint;
use App\Domain\ShadowTeaching\LearningCheckpointCollection;
use App\Domain\ShadowTeaching\LearningModule;
use App\Domain\ShadowTeaching\LearningObjective;
use App\Domain\ShadowTeaching\LearningObjectiveCollection;
use App\Domain\ShadowTeaching\LearningPath;
use App\Domain\ShadowTeaching\RevisionItem;
use App\Domain\ShadowTeaching\RevisionItemCollection;
use App\Domain\ShadowTeaching\TeachingDifficulty;
use App\Domain\ShadowTeaching\TeachingExercise;
use App\Domain\ShadowTeaching\TeachingExerciseCollection;
use App\Domain\ShadowTeaching\TeachingHistoryCollection;
use App\Domain\ShadowTeaching\TeachingMission;
use App\Domain\ShadowTeaching\TeachingMissionCollection;
use App\Domain\ShadowTeaching\TeachingPlan;
use App\Domain\ShadowTeaching\TeachingPlanId;
use App\Domain\ShadowTeaching\TeachingPreferences;
use App\Domain\ShadowTeaching\TeachingProgressStatus;
use App\Domain\ShadowTeaching\TeachingSessionRecord;
use App\Domain\ShadowTeaching\TeachingVoiceMode;
use JsonException;

final class ShadowTeachingPersistenceMapper
{
    /** @return array<string, mixed> */
    public function toArray(TeachingPlan $plan): array
    {
        return [
            'id' => $plan->id()->value,
            'scopeKey' => $plan->scopeKey(),
            'preferences' => [
                'teachingEnabled' => $plan->preferences()->teachingEnabled(),
                'voiceMode' => $plan->preferences()->voiceMode()->value,
                'difficulty' => $plan->preferences()->difficulty()->value,
                'revisionEnabled' => $plan->preferences()->revisionEnabled(),
            ],
            'path' => [
                'title' => $plan->path()->title(),
                'goal' => $plan->path()->goal(),
                'modules' => array_map($this->moduleToArray(...), $plan->path()->modules()),
            ],
            'objectives' => array_map($this->objectiveToArray(...), $plan->objectives()->all()),
            'exercises' => array_map($this->exerciseToArray(...), $plan->exercises()->all()),
            'revisions' => array_map($this->revisionToArray(...), $plan->revisions()->all()),
            'checkpoints' => array_map($this->checkpointToArray(...), $plan->checkpoints()->all()),
            'missions' => array_map($this->missionToArray(...), $plan->missions()->all()),
            'history' => array_map($this->historyToArray(...), $plan->history()->all()),
            'currentObjectiveKey' => $plan->currentObjectiveKey(),
        ];
    }

    public function fromJson(string $json): TeachingPlan
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidShadowTeachingException('Stored teaching plan is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || !is_string($decoded['id'] ?? null)) {
            throw new InvalidShadowTeachingException('Stored teaching plan is invalid.');
        }

        $scopeKey = is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default';

        return new TeachingPlan(
            new TeachingPlanId($decoded['id']),
            $scopeKey,
            $this->pathFromArray(is_array($decoded['path'] ?? null) ? $decoded['path'] : []),
            $this->objectivesFromArray(is_array($decoded['objectives'] ?? null) ? $decoded['objectives'] : []),
            $this->exercisesFromArray(is_array($decoded['exercises'] ?? null) ? $decoded['exercises'] : []),
            $this->revisionsFromArray(is_array($decoded['revisions'] ?? null) ? $decoded['revisions'] : []),
            $this->checkpointsFromArray(is_array($decoded['checkpoints'] ?? null) ? $decoded['checkpoints'] : []),
            $this->missionsFromArray(is_array($decoded['missions'] ?? null) ? $decoded['missions'] : []),
            $this->historyFromArray(is_array($decoded['history'] ?? null) ? $decoded['history'] : []),
            $this->preferencesFromArray(is_array($decoded['preferences'] ?? null) ? $decoded['preferences'] : []),
            is_string($decoded['currentObjectiveKey'] ?? null) ? $decoded['currentObjectiveKey'] : null,
        );
    }

    /** @return array<string, mixed> */
    private function moduleToArray(LearningModule $module): array
    {
        return [
            'key' => $module->key(),
            'title' => $module->title(),
            'objectives' => array_map($this->objectiveToArray(...), $module->objectives()),
        ];
    }

    /** @return array<string, mixed> */
    private function objectiveToArray(LearningObjective $objective): array
    {
        return [
            'key' => $objective->key(),
            'title' => $objective->title(),
            'description' => $objective->description(),
            'concepts' => $objective->concepts(),
            'prerequisites' => $objective->prerequisites(),
            'status' => $objective->status()->value,
            'progressPercent' => $objective->progressPercent(),
            'explanation' => $objective->explanation(),
        ];
    }

    /** @return array<string, mixed> */
    private function exerciseToArray(TeachingExercise $exercise): array
    {
        return [
            'id' => $exercise->id(),
            'type' => $exercise->type()->value,
            'question' => $exercise->question(),
            'options' => $exercise->options(),
            'correctAnswer' => $exercise->correctAnswer(),
            'explanation' => $exercise->explanation(),
            'objectiveKey' => $exercise->objectiveKey(),
            'status' => $exercise->status()->value,
        ];
    }

    /** @return array<string, mixed> */
    private function revisionToArray(RevisionItem $item): array
    {
        return [
            'conceptKey' => $item->conceptKey(),
            'label' => $item->label(),
            'dueAt' => $item->dueAt()->format(DATE_ATOM),
            'intervalDays' => $item->intervalDays(),
            'reason' => $item->reason(),
        ];
    }

    /** @return array<string, mixed> */
    private function checkpointToArray(LearningCheckpoint $checkpoint): array
    {
        return [
            'id' => $checkpoint->id(),
            'objectiveKey' => $checkpoint->objectiveKey(),
            'label' => $checkpoint->label(),
            'completed' => $checkpoint->completed(),
            'completedAt' => $checkpoint->completedAt()?->format(DATE_ATOM),
        ];
    }

    /** @return array<string, mixed> */
    private function missionToArray(TeachingMission $mission): array
    {
        return [
            'number' => $mission->number(),
            'title' => $mission->title(),
            'objectiveKey' => $mission->objectiveKey(),
            'durationMinutes' => $mission->durationMinutes(),
            'exerciseCount' => $mission->exerciseCount(),
            'checkpointCount' => $mission->checkpointCount(),
            'rewardLabel' => $mission->rewardLabel(),
            'status' => $mission->status()->value,
        ];
    }

    /** @return array<string, mixed> */
    private function historyToArray(TeachingSessionRecord $record): array
    {
        return [
            'id' => $record->id(),
            'label' => $record->label(),
            'detail' => $record->detail(),
            'recordedAt' => $record->recordedAt()->format(DATE_ATOM),
        ];
    }

    /** @param array<string, mixed> $row */
    private function pathFromArray(array $row): LearningPath
    {
        $modulesRaw = is_array($row['modules'] ?? null) ? $row['modules'] : [];
        $modules = [];

        foreach ($modulesRaw as $moduleRaw) {
            if (!is_array($moduleRaw) || !is_string($moduleRaw['key'] ?? null)) {
                continue;
            }

            $modules[] = new LearningModule(
                $moduleRaw['key'],
                is_string($moduleRaw['title'] ?? null) ? $moduleRaw['title'] : '',
                $this->objectivesFromArray(is_array($moduleRaw['objectives'] ?? null) ? $moduleRaw['objectives'] : [])->all(),
            );
        }

        return new LearningPath(
            is_string($row['title'] ?? null) ? $row['title'] : 'Personal learning path',
            is_string($row['goal'] ?? null) ? $row['goal'] : 'Grow with Shadow',
            $modules,
        );
    }

    /** @param list<array<string, mixed>> $rows */
    private function objectivesFromArray(array $rows): LearningObjectiveCollection
    {
        $items = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $status = TeachingProgressStatus::tryFrom((string) ($row['status'] ?? ''));

            if (!is_string($row['key'] ?? null) || !is_string($row['title'] ?? null) || null === $status) {
                continue;
            }

            $items[] = LearningObjective::create(
                $row['key'],
                $row['title'],
                is_string($row['description'] ?? null) ? $row['description'] : '',
                is_array($row['concepts'] ?? null) ? array_values(array_filter($row['concepts'], 'is_string')) : [],
                is_array($row['prerequisites'] ?? null) ? array_values(array_filter($row['prerequisites'], 'is_string')) : [],
                $status,
                (int) ($row['progressPercent'] ?? 0),
                is_string($row['explanation'] ?? null) ? $row['explanation'] : '',
            );
        }

        return new LearningObjectiveCollection($items);
    }

    /** @param list<array<string, mixed>> $rows */
    private function exercisesFromArray(array $rows): TeachingExerciseCollection
    {
        $items = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $type = ExerciseType::tryFrom((string) ($row['type'] ?? ''));
            $status = ExerciseStatus::tryFrom((string) ($row['status'] ?? ''));

            if (
                null === $type
                || null === $status
                || !is_string($row['id'] ?? null)
                || !is_string($row['question'] ?? null)
                || !is_string($row['objectiveKey'] ?? null)
            ) {
                continue;
            }

            $items[] = new TeachingExercise(
                $row['id'],
                $type,
                $row['question'],
                is_array($row['options'] ?? null) ? array_values(array_filter($row['options'], 'is_string')) : [],
                is_string($row['correctAnswer'] ?? null) ? $row['correctAnswer'] : '',
                is_string($row['explanation'] ?? null) ? $row['explanation'] : '',
                $row['objectiveKey'],
                $status,
            );
        }

        return new TeachingExerciseCollection($items);
    }

    /** @param list<array<string, mixed>> $rows */
    private function revisionsFromArray(array $rows): RevisionItemCollection
    {
        $items = [];

        foreach ($rows as $row) {
            if (!is_array($row) || !is_string($row['conceptKey'] ?? null)) {
                continue;
            }

            $items[] = new RevisionItem(
                $row['conceptKey'],
                is_string($row['label'] ?? null) ? $row['label'] : $row['conceptKey'],
                \DateTimeImmutable::createFromFormat(DATE_ATOM, (string) ($row['dueAt'] ?? '')) ?: new \DateTimeImmutable(),
                (int) ($row['intervalDays'] ?? 0),
                is_string($row['reason'] ?? null) ? $row['reason'] : '',
            );
        }

        return new RevisionItemCollection($items);
    }

    /** @param list<array<string, mixed>> $rows */
    private function checkpointsFromArray(array $rows): LearningCheckpointCollection
    {
        $items = [];

        foreach ($rows as $row) {
            if (
                !is_array($row)
                || !is_string($row['id'] ?? null)
                || !is_string($row['objectiveKey'] ?? null)
                || !is_string($row['label'] ?? null)
            ) {
                continue;
            }

            $completed = (bool) ($row['completed'] ?? false);
            $completedAt = \DateTimeImmutable::createFromFormat(DATE_ATOM, (string) ($row['completedAt'] ?? '')) ?: null;

            $items[] = new LearningCheckpoint(
                $row['id'],
                $row['objectiveKey'],
                $row['label'],
                $completed,
                $completed ? $completedAt : null,
            );
        }

        return new LearningCheckpointCollection($items);
    }

    /** @param list<array<string, mixed>> $rows */
    private function missionsFromArray(array $rows): TeachingMissionCollection
    {
        $items = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $status = TeachingProgressStatus::tryFrom((string) ($row['status'] ?? ''));

            if (null === $status) {
                continue;
            }

            $items[] = new TeachingMission(
                (int) ($row['number'] ?? 0),
                is_string($row['title'] ?? null) ? $row['title'] : '',
                is_string($row['objectiveKey'] ?? null) ? $row['objectiveKey'] : '',
                (int) ($row['durationMinutes'] ?? 0),
                (int) ($row['exerciseCount'] ?? 0),
                (int) ($row['checkpointCount'] ?? 0),
                is_string($row['rewardLabel'] ?? null) ? $row['rewardLabel'] : '',
                $status,
            );
        }

        return new TeachingMissionCollection($items);
    }

    /** @param list<array<string, mixed>> $rows */
    private function historyFromArray(array $rows): TeachingHistoryCollection
    {
        $items = [];

        foreach ($rows as $row) {
            if (!is_array($row) || !is_string($row['id'] ?? null)) {
                continue;
            }

            $items[] = new TeachingSessionRecord(
                $row['id'],
                is_string($row['label'] ?? null) ? $row['label'] : '',
                is_string($row['detail'] ?? null) ? $row['detail'] : '',
                \DateTimeImmutable::createFromFormat(DATE_ATOM, (string) ($row['recordedAt'] ?? '')) ?: new \DateTimeImmutable(),
            );
        }

        return new TeachingHistoryCollection($items);
    }

    /** @param array<string, mixed> $row */
    private function preferencesFromArray(array $row): TeachingPreferences
    {
        $voiceMode = TeachingVoiceMode::tryFrom((string) ($row['voiceMode'] ?? '')) ?? TeachingVoiceMode::Professor;
        $difficulty = TeachingDifficulty::tryFrom((string) ($row['difficulty'] ?? '')) ?? TeachingDifficulty::Normal;

        return new TeachingPreferences(
            (bool) ($row['teachingEnabled'] ?? true),
            $voiceMode,
            $difficulty,
            (bool) ($row['revisionEnabled'] ?? true),
        );
    }
}
