<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowGoals;

use App\Domain\ShadowGoals\GoalCategory;
use App\Domain\ShadowGoals\GoalPortfolio;
use App\Domain\ShadowGoals\GoalPortfolioId;
use App\Domain\ShadowGoals\GoalPriority;
use App\Domain\ShadowGoals\GoalStatus;
use App\Domain\ShadowGoals\LearningGoal;
use App\Domain\ShadowGoals\LearningGoalCollection;
use App\Domain\ShadowGoals\Exception\InvalidShadowGoalException;
use JsonException;

final class ShadowGoalsPersistenceMapper
{
    /** @return array<string, mixed> */
    public function toArray(GoalPortfolio $portfolio): array
    {
        return [
            'id' => $portfolio->id()->value,
            'scopeKey' => $portfolio->scopeKey(),
            'mentorEnabled' => $portfolio->mentorEnabled(),
            'goals' => array_map($this->goalToArray(...), $portfolio->goals()->all()),
        ];
    }

    public function fromJson(string $json): GoalPortfolio
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidShadowGoalException('Stored goal portfolio is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || !is_string($decoded['id'] ?? null)) {
            throw new InvalidShadowGoalException('Stored goal portfolio is invalid.');
        }

        return new GoalPortfolio(
            new GoalPortfolioId($decoded['id']),
            is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default',
            $this->goalsFromArray(is_array($decoded['goals'] ?? null) ? $decoded['goals'] : []),
            (bool) ($decoded['mentorEnabled'] ?? true),
        );
    }

    /** @return array<string, mixed> */
    private function goalToArray(LearningGoal $goal): array
    {
        return [
            'id' => $goal->id(),
            'title' => $goal->title(),
            'description' => $goal->description(),
            'motivation' => $goal->motivation(),
            'category' => $goal->category()->value,
            'priority' => $goal->priority()->value,
            'status' => $goal->status()->value,
            'progressPercent' => $goal->progressPercent(),
            'deadline' => $goal->deadline()?->format(DATE_ATOM),
            'targetSkills' => $goal->targetSkills(),
            'requiredKnowledge' => $goal->requiredKnowledge(),
            'successCriteria' => $goal->successCriteria(),
        ];
    }

    /** @param list<array<string, mixed>> $items */
    private function goalsFromArray(array $items): LearningGoalCollection
    {
        $goals = [];

        foreach ($items as $item) {
            if (!is_array($item) || !is_string($item['id'] ?? null) || !is_string($item['title'] ?? null)) {
                continue;
            }

            $goals[] = new LearningGoal(
                $item['id'],
                $item['title'],
                is_string($item['description'] ?? null) ? $item['description'] : '',
                is_string($item['motivation'] ?? null) ? $item['motivation'] : '',
                GoalCategory::tryFrom((string) ($item['category'] ?? 'custom')) ?? GoalCategory::Custom,
                GoalPriority::tryFrom((string) ($item['priority'] ?? 'secondary')) ?? GoalPriority::Secondary,
                GoalStatus::tryFrom((string) ($item['status'] ?? 'active')) ?? GoalStatus::Active,
                (int) ($item['progressPercent'] ?? 0),
                isset($item['deadline']) && is_string($item['deadline'])
                    ? new \DateTimeImmutable($item['deadline'])
                    : null,
                is_array($item['targetSkills'] ?? null) ? array_values(array_map('strval', $item['targetSkills'])) : [],
                is_array($item['requiredKnowledge'] ?? null) ? array_values(array_map('strval', $item['requiredKnowledge'])) : [],
                is_array($item['successCriteria'] ?? null) ? array_values(array_map('strval', $item['successCriteria'])) : [],
                [],
            );
        }

        return LearningGoalCollection::from($goals);
    }
}
