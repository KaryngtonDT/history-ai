<?php

declare(strict_types=1);

namespace App\Application\Learning\Handlers;

use App\Application\Learning\LearningProfileBuilder;
use App\Application\Learning\LearningProfileJsonMapper;

final class UpdateLearningPreferencesHandler
{
    public function __construct(
        private readonly LearningProfileBuilder $builder,
        private readonly LearningProfileJsonMapper $mapper,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $profile = $this->builder->getOrCreate($scopeKey);

        if (array_key_exists('adaptiveRecommendationsEnabled', $payload)) {
            $profile = (bool) $payload['adaptiveRecommendationsEnabled']
                ? $profile->enableAdaptiveRecommendations()
                : $profile->disableAdaptiveRecommendations();
        }

        $profile = $this->builder->refresh($profile);

        return $this->mapper->toArray($profile);
    }
}
