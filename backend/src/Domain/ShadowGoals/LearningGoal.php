<?php

declare(strict_types=1);

namespace App\Domain\ShadowGoals;

use App\Domain\ShadowGoals\Exception\InvalidShadowGoalException;

final readonly class LearningGoal
{
    /** @param list<string> $targetSkills */
    /** @param list<string> $requiredKnowledge */
    /** @param list<string> $successCriteria */
    /** @param list<GoalConstraint> $constraints */
    public function __construct(
        private string $id,
        private string $title,
        private string $description,
        private string $motivation,
        private GoalCategory $category,
        private GoalPriority $priority,
        private GoalStatus $status,
        private int $progressPercent,
        private ?\DateTimeImmutable $deadline,
        private array $targetSkills,
        private array $requiredKnowledge,
        private array $successCriteria,
        private array $constraints,
    ) {
        if ('' === trim($title)) {
            throw new InvalidShadowGoalException('Goal title cannot be empty.');
        }

        if ($progressPercent < 0 || $progressPercent > 100) {
            throw new InvalidShadowGoalException('Goal progress must be between 0 and 100.');
        }
    }

    public static function create(
        string $title,
        GoalCategory $category = GoalCategory::Custom,
        GoalPriority $priority = GoalPriority::Secondary,
        string $description = '',
        string $motivation = '',
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            trim($title),
            $description,
            $motivation,
            $category,
            $priority,
            GoalStatus::Active,
            0,
            null,
            [],
            [],
            [],
            [],
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function motivation(): string
    {
        return $this->motivation;
    }

    public function category(): GoalCategory
    {
        return $this->category;
    }

    public function priority(): GoalPriority
    {
        return $this->priority;
    }

    public function status(): GoalStatus
    {
        return $this->status;
    }

    public function progressPercent(): int
    {
        return $this->progressPercent;
    }

    public function deadline(): ?\DateTimeImmutable
    {
        return $this->deadline;
    }

    /** @return list<string> */
    public function targetSkills(): array
    {
        return $this->targetSkills;
    }

    /** @return list<string> */
    public function requiredKnowledge(): array
    {
        return $this->requiredKnowledge;
    }

    /** @return list<string> */
    public function successCriteria(): array
    {
        return $this->successCriteria;
    }

    /** @return list<GoalConstraint> */
    public function constraints(): array
    {
        return $this->constraints;
    }

    public function withProgress(int $percent): self
    {
        return new self(
            $this->id,
            $this->title,
            $this->description,
            $this->motivation,
            $this->category,
            $this->priority,
            $this->status,
            max(0, min(100, $percent)),
            $this->deadline,
            $this->targetSkills,
            $this->requiredKnowledge,
            $this->successCriteria,
            $this->constraints,
        );
    }

    /** @param array<string, mixed> $data */
    public function applyUpdate(array $data): self
    {
        return new self(
            $this->id,
            is_string($data['title'] ?? null) ? trim($data['title']) : $this->title,
            is_string($data['description'] ?? null) ? $data['description'] : $this->description,
            is_string($data['motivation'] ?? null) ? $data['motivation'] : $this->motivation,
            isset($data['category']) ? (GoalCategory::tryFrom((string) $data['category']) ?? $this->category) : $this->category,
            isset($data['priority']) ? (GoalPriority::tryFrom((string) $data['priority']) ?? $this->priority) : $this->priority,
            isset($data['status']) ? (GoalStatus::tryFrom((string) $data['status']) ?? $this->status) : $this->status,
            isset($data['progressPercent']) ? (int) $data['progressPercent'] : $this->progressPercent,
            isset($data['deadline']) && is_string($data['deadline'])
                ? new \DateTimeImmutable($data['deadline'])
                : $this->deadline,
            is_array($data['targetSkills'] ?? null) ? array_values(array_map('strval', $data['targetSkills'])) : $this->targetSkills,
            is_array($data['requiredKnowledge'] ?? null) ? array_values(array_map('strval', $data['requiredKnowledge'])) : $this->requiredKnowledge,
            is_array($data['successCriteria'] ?? null) ? array_values(array_map('strval', $data['successCriteria'])) : $this->successCriteria,
            $this->constraints,
        );
    }
}
