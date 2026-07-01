<?php

declare(strict_types=1);

namespace App\Domain\Learning;

final readonly class LearningPreferenceCollection
{
    /**
     * @param list<LearningPreference> $preferences
     */
    public function __construct(private array $preferences)
    {
    }

    public static function default(): self
    {
        return new self([LearningPreference::adaptiveRecommendationsEnabled(false)]);
    }

    public function withPreference(LearningPreference $preference): self
    {
        $updated = [];

        foreach ($this->preferences as $existing) {
            if ($existing->key() !== $preference->key()) {
                $updated[] = $existing;
            }
        }

        $updated[] = $preference;

        return new self($updated);
    }

    public function find(LearningPreferenceKey $key): ?LearningPreference
    {
        foreach ($this->preferences as $preference) {
            if ($preference->key() === $key) {
                return $preference;
            }
        }

        return null;
    }

    public function adaptiveRecommendationsEnabled(): bool
    {
        return $this->find(LearningPreferenceKey::AdaptiveRecommendationsEnabled)?->enabled() ?? false;
    }

    /**
     * @return list<LearningPreference>
     */
    public function all(): array
    {
        return $this->preferences;
    }
}
