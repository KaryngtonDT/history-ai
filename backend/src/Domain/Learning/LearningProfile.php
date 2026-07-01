<?php

declare(strict_types=1);

namespace App\Domain\Learning;

use App\Domain\Learning\Exception\InvalidLearningProfileException;

final readonly class LearningProfile
{
    public function __construct(
        private LearningProfileId $id,
        private string $scopeKey,
        private LearningPreferenceCollection $preferences,
        private LearningSignalCollection $signals,
        private LearningInsightCollection $insights,
        private LearningRecommendationCollection $recommendations,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidLearningProfileException('Learning profile scope cannot be empty.');
        }
    }

    public static function create(
        ?LearningProfileId $id = null,
        string $scopeKey = 'default',
    ): self {
        return new self(
            $id ?? LearningProfileId::generate(),
            trim($scopeKey),
            LearningPreferenceCollection::default(),
            LearningSignalCollection::empty(),
            LearningInsightCollection::empty(),
            LearningRecommendationCollection::empty(),
        );
    }

    public function id(): LearningProfileId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function preferences(): LearningPreferenceCollection
    {
        return $this->preferences;
    }

    public function signals(): LearningSignalCollection
    {
        return $this->signals;
    }

    public function insights(): LearningInsightCollection
    {
        return $this->insights;
    }

    public function recommendations(): LearningRecommendationCollection
    {
        return $this->recommendations;
    }

    public function adaptiveRecommendationsEnabled(): bool
    {
        return $this->preferences->adaptiveRecommendationsEnabled();
    }

    public function recordSignal(LearningSignal $signal): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->preferences,
            $this->signals->append($signal),
            $this->insights,
            $this->recommendations,
        );
    }

    public function withInsights(LearningInsightCollection $insights): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->preferences,
            $this->signals,
            $insights,
            $this->recommendations,
        );
    }

    public function withRecommendations(LearningRecommendationCollection $recommendations): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->preferences,
            $this->signals,
            $this->insights,
            $recommendations,
        );
    }

    public function withPreferences(LearningPreferenceCollection $preferences): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $preferences,
            $this->signals,
            $this->insights,
            $this->recommendations,
        );
    }

    public function enableAdaptiveRecommendations(): self
    {
        return $this->withPreferences(
            $this->preferences->withPreference(
                LearningPreference::adaptiveRecommendationsEnabled(true),
            ),
        );
    }

    public function disableAdaptiveRecommendations(): self
    {
        return $this->withPreferences(
            $this->preferences->withPreference(
                LearningPreference::adaptiveRecommendationsEnabled(false),
            ),
        );
    }

    public function reset(): self
    {
        return new self(
            $this->id,
            $this->scopeKey,
            $this->preferences,
            LearningSignalCollection::empty(),
            LearningInsightCollection::empty(),
            LearningRecommendationCollection::empty(),
        );
    }
}
