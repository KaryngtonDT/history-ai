<?php

declare(strict_types=1);

namespace App\Application\Learning\Handlers;

use App\Application\Learning\LearningAdaptiveAdvisor;
use App\Application\Learning\LearningProfileJsonMapper;
use App\Application\Learning\LearningProfileBuilder;

final class GetLearningRecommendationsHandler
{
    public function __construct(
        private readonly LearningProfileBuilder $builder,
        private readonly LearningAdaptiveAdvisor $advisor,
        private readonly LearningProfileJsonMapper $mapper,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(string $scopeKey = 'default'): array
    {
        $profile = $this->builder->getOrCreate($scopeKey);
        $hints = $this->advisor->hints($scopeKey);

        return [
            'scopeKey' => $scopeKey,
            'adaptiveRecommendationsEnabled' => $profile->adaptiveRecommendationsEnabled(),
            'recommendations' => array_map(
                fn ($recommendation) => [
                    'id' => $recommendation->id()->value,
                    'type' => $recommendation->type()->value,
                    'explanation' => $recommendation->explanation(),
                    'sourceInsightIds' => $recommendation->sourceInsightIds(),
                    'generatedAt' => $recommendation->generatedAt()->format(DATE_ATOM),
                ],
                $profile->recommendations()->all(),
            ),
            'adaptiveHints' => [
                'active' => $hints->active,
                'explanationStyle' => $hints->explanationStyle?->value,
                'challengeLevel' => $hints->challengeLevel?->value,
                'voiceLanguage' => $hints->voiceLanguage?->value,
                'translationStyle' => $hints->translationStyle,
                'preferredProvider' => $hints->preferredProvider,
                'appliedRecommendations' => $hints->appliedRecommendations,
            ],
            'profile' => $this->mapper->toArray($profile),
        ];
    }
}
