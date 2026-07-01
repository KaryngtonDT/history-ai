<?php

declare(strict_types=1);

namespace App\Domain\Learning;

enum LearningPreferenceKey: string
{
    case AdaptiveRecommendationsEnabled = 'adaptive_recommendations_enabled';
}

final readonly class LearningPreference
{
    public function __construct(
        private LearningPreferenceKey $key,
        private bool $enabled,
    ) {
    }

    public static function adaptiveRecommendationsEnabled(bool $enabled = false): self
    {
        return new self(LearningPreferenceKey::AdaptiveRecommendationsEnabled, $enabled);
    }

    public function key(): LearningPreferenceKey
    {
        return $this->key;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function withEnabled(bool $enabled): self
    {
        return new self($this->key, $enabled);
    }
}
