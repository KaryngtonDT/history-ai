<?php

declare(strict_types=1);

namespace App\Application\Learning;

use App\Application\Learning\DTO\LearningAdaptiveHints;
use App\Domain\Learning\LearningProfileRepositoryInterface;
use App\Domain\Learning\LearningRecommendation;
use App\Domain\Learning\LearningRecommendationType;
use App\Domain\Shadow\ShadowChallengeLevel;
use App\Domain\Shadow\ShadowExplanationStyle;
use App\Domain\Shadow\ShadowVoiceLanguage;

final class LearningAdaptiveAdvisor
{
    public function __construct(
        private readonly LearningProfileRepositoryInterface $repository,
    ) {
    }

    public function hints(string $scopeKey = 'default'): LearningAdaptiveHints
    {
        $profile = $this->repository->findByScope($scopeKey);

        if (null === $profile || !$profile->adaptiveRecommendationsEnabled()) {
            return LearningAdaptiveHints::inactive();
        }

        $explanationStyle = null;
        $challengeLevel = null;
        $voiceLanguage = null;
        $translationStyle = null;
        $preferredProvider = null;
        $applied = [];

        foreach ($profile->recommendations()->all() as $recommendation) {
            $this->applyRecommendation(
                $recommendation,
                $explanationStyle,
                $challengeLevel,
                $voiceLanguage,
                $translationStyle,
                $preferredProvider,
                $applied,
            );
        }

        return new LearningAdaptiveHints(
            active: true,
            explanationStyle: $explanationStyle,
            challengeLevel: $challengeLevel,
            voiceLanguage: $voiceLanguage,
            translationStyle: $translationStyle,
            preferredProvider: $preferredProvider,
            appliedRecommendations: $applied,
        );
    }

    /**
     * @param list<string> $applied
     */
    private function applyRecommendation(
        LearningRecommendation $recommendation,
        ?ShadowExplanationStyle &$explanationStyle,
        ?ShadowChallengeLevel &$challengeLevel,
        ?ShadowVoiceLanguage &$voiceLanguage,
        ?string &$translationStyle,
        ?string &$preferredProvider,
        array &$applied,
    ): void {
        $applied[] = $recommendation->type()->value;

        match ($recommendation->type()) {
            LearningRecommendationType::UseShortExplanations => $explanationStyle = ShadowExplanationStyle::Short,
            LearningRecommendationType::UseDetailedExplanations => $explanationStyle = ShadowExplanationStyle::Detailed,
            LearningRecommendationType::DecreaseChallengeLevel => $challengeLevel = ShadowChallengeLevel::Easy,
            LearningRecommendationType::IncreaseChallengeLevel => $challengeLevel = ShadowChallengeLevel::Hard,
            LearningRecommendationType::UseLiteralTranslation => $translationStyle = 'literal',
            LearningRecommendationType::UseNaturalTranslation => $translationStyle = 'natural',
            LearningRecommendationType::PreferVoiceLanguage => $voiceLanguage = $this->extractVoiceLanguage($recommendation->explanation()),
            LearningRecommendationType::PreferProvider => $preferredProvider = $this->extractProviderId($recommendation->explanation()),
            LearningRecommendationType::SlowDownPlayback => $explanationStyle = ShadowExplanationStyle::Short,
            default => null,
        };
    }

    private function extractVoiceLanguage(string $explanation): ?ShadowVoiceLanguage
    {
        if (preg_match('/"([a-z]{2})"/', $explanation, $matches) === 1) {
            return ShadowVoiceLanguage::tryFrom($matches[1]);
        }

        return null;
    }

    private function extractProviderId(string $explanation): ?string
    {
        if (preg_match('/"([^"]+)"/', $explanation, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
