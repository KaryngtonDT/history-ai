<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

use App\Domain\ShadowExecutive\Exception\InvalidShadowExecutiveException;

final readonly class ExecutivePlan
{
    public function __construct(
        private ExecutivePlanId $id,
        private string $scopeKey,
        private ExecutiveAgenda $agenda,
        private ExecutiveDecisionCollection $decisions,
        private ExecutiveRecommendationCollection $recommendations,
        private ExecutiveWeeklyReview $weeklyReview,
        private bool $executiveEnabled,
        private ?int $availableMinutes,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidShadowExecutiveException('Executive plan scope cannot be empty.');
        }

        if (null !== $availableMinutes && $availableMinutes < 0) {
            throw new InvalidShadowExecutiveException('Available minutes cannot be negative.');
        }
    }

    public static function create(
        ?ExecutivePlanId $id = null,
        string $scopeKey = 'default',
    ): self {
        return new self(
            $id ?? ExecutivePlanId::generate(),
            trim($scopeKey),
            ExecutiveAgenda::empty(),
            ExecutiveDecisionCollection::empty(),
            ExecutiveRecommendationCollection::empty(),
            ExecutiveWeeklyReview::empty(),
            true,
            null,
        );
    }

    public function id(): ExecutivePlanId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function agenda(): ExecutiveAgenda
    {
        return $this->agenda;
    }

    public function decisions(): ExecutiveDecisionCollection
    {
        return $this->decisions;
    }

    public function recommendations(): ExecutiveRecommendationCollection
    {
        return $this->recommendations;
    }

    public function weeklyReview(): ExecutiveWeeklyReview
    {
        return $this->weeklyReview;
    }

    public function executiveEnabled(): bool
    {
        return $this->executiveEnabled;
    }

    public function availableMinutes(): ?int
    {
        return $this->availableMinutes;
    }

    public function findDecision(string $id): ?ExecutiveDecision
    {
        return $this->decisions->find($id);
    }

    public function pendingDecisions(): ExecutiveDecisionCollection
    {
        return $this->decisions->pending();
    }

    public function withAgenda(ExecutiveAgenda $agenda): self
    {
        return $this->replace(agenda: $agenda);
    }

    public function withDecisions(ExecutiveDecisionCollection $decisions): self
    {
        return $this->replace(decisions: $decisions);
    }

    public function withRecommendations(ExecutiveRecommendationCollection $recommendations): self
    {
        return $this->replace(recommendations: $recommendations);
    }

    public function withWeeklyReview(ExecutiveWeeklyReview $weeklyReview): self
    {
        return $this->replace(weeklyReview: $weeklyReview);
    }

    public function withExecutiveEnabled(bool $executiveEnabled): self
    {
        return $this->replace(executiveEnabled: $executiveEnabled);
    }

    public function withAvailableMinutes(?int $availableMinutes): self
    {
        return $this->replace(availableMinutes: $availableMinutes);
    }

    public function reset(): self
    {
        return self::create($this->id, $this->scopeKey);
    }

    private function replace(
        ?ExecutiveAgenda $agenda = null,
        ?ExecutiveDecisionCollection $decisions = null,
        ?ExecutiveRecommendationCollection $recommendations = null,
        ?ExecutiveWeeklyReview $weeklyReview = null,
        ?bool $executiveEnabled = null,
        ?int $availableMinutes = null,
    ): self {
        return new self(
            $this->id,
            $this->scopeKey,
            $agenda ?? $this->agenda,
            $decisions ?? $this->decisions,
            $recommendations ?? $this->recommendations,
            $weeklyReview ?? $this->weeklyReview,
            $executiveEnabled ?? $this->executiveEnabled,
            $availableMinutes ?? $this->availableMinutes,
        );
    }
}
