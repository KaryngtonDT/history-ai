<?php

declare(strict_types=1);

namespace App\Application\Learning;

use App\Domain\Learning\LearningProfile;
use App\Domain\Learning\LearningProfileRepositoryInterface;
use App\Domain\Learning\LearningRecommendationCollection;
use App\Domain\Learning\LearningSignal;

final class LearningProfileBuilder
{
    public function __construct(
        private readonly LearningProfileRepositoryInterface $repository,
        private readonly LearningSignalCollector $signalCollector,
        private readonly LearningInsightGenerator $insightGenerator,
        private readonly LearningRecommendationEngine $recommendationEngine,
    ) {
    }

    public function getOrCreate(string $scopeKey = 'default'): LearningProfile
    {
        return $this->repository->findByScope($scopeKey) ?? LearningProfile::create(scopeKey: $scopeKey);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function recordPayload(string $scopeKey, array $payload): LearningProfile
    {
        return $this->recordSignals($scopeKey, $this->signalCollector->collect($payload));
    }

    /**
     * @param list<LearningSignal> $signals
     */
    public function recordSignals(string $scopeKey, array $signals): LearningProfile
    {
        $profile = $this->getOrCreate($scopeKey);

        foreach ($signals as $signal) {
            $profile = $profile->recordSignal($signal);
        }

        return $this->refresh($profile);
    }

    public function refresh(LearningProfile $profile): LearningProfile
    {
        $insights = $this->insightGenerator->generate($profile->signals());
        $profile = $profile->withInsights($insights);

        $recommendations = $profile->adaptiveRecommendationsEnabled()
            ? $this->recommendationEngine->generate($profile->insights())
            : LearningRecommendationCollection::empty();

        $profile = $profile->withRecommendations($recommendations);
        $this->repository->save($profile);

        return $profile;
    }

    public function reset(string $scopeKey = 'default'): LearningProfile
    {
        $profile = $this->getOrCreate($scopeKey)->reset();

        return $this->refresh($profile);
    }
}
