<?php

declare(strict_types=1);

namespace App\Domain\ShadowGoals;

use App\Domain\ShadowGoals\Exception\InvalidShadowGoalException;

final readonly class GoalPortfolio
{
    public function __construct(
        private GoalPortfolioId $id,
        private string $scopeKey,
        private LearningGoalCollection $goals,
        private bool $mentorEnabled,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidShadowGoalException('Goal portfolio scope cannot be empty.');
        }
    }

    public static function create(
        ?GoalPortfolioId $id = null,
        string $scopeKey = 'default',
    ): self {
        return new self(
            $id ?? GoalPortfolioId::generate(),
            trim($scopeKey),
            LearningGoalCollection::empty(),
            true,
        );
    }

    public function id(): GoalPortfolioId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function goals(): LearningGoalCollection
    {
        return $this->goals;
    }

    public function mentorEnabled(): bool
    {
        return $this->mentorEnabled;
    }

    public function primaryGoal(): ?LearningGoal
    {
        return $this->goals->primary();
    }

    public function addGoal(LearningGoal $goal): self
    {
        return new self($this->id, $this->scopeKey, $this->goals->upsert($goal), $this->mentorEnabled);
    }

    public function updateGoal(LearningGoal $goal): self
    {
        if (null === $this->goals->find($goal->id())) {
            throw new InvalidShadowGoalException('Goal not found in portfolio.');
        }

        return new self($this->id, $this->scopeKey, $this->goals->upsert($goal), $this->mentorEnabled);
    }

    public function removeGoal(string $goalId): self
    {
        return new self($this->id, $this->scopeKey, $this->goals->remove($goalId), $this->mentorEnabled);
    }

    public function reset(): self
    {
        return self::create($this->id, $this->scopeKey);
    }
}
