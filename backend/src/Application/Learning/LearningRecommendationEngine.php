<?php

declare(strict_types=1);

namespace App\Application\Learning;

use App\Domain\Learning\LearningInsight;
use App\Domain\Learning\LearningInsightCollection;
use App\Domain\Learning\LearningInsightType;
use App\Domain\Learning\LearningRecommendation;
use App\Domain\Learning\LearningRecommendationCollection;
use App\Domain\Learning\LearningRecommendationType;

final class LearningRecommendationEngine
{
    public function generate(LearningInsightCollection $insights): LearningRecommendationCollection
    {
        $collection = LearningRecommendationCollection::empty();

        foreach ($insights->all() as $insight) {
            foreach ($this->recommendationsForInsight($insight) as $recommendation) {
                $collection = $collection->append($recommendation);
            }
        }

        return $collection;
    }

    /**
     * @return list<LearningRecommendation>
     */
    private function recommendationsForInsight(LearningInsight $insight): array
    {
        return match ($insight->type()) {
            LearningInsightType::VocabularyGap => [
                $this->recommend(
                    LearningRecommendationType::ShowVocabularyBeforePlayback,
                    'Repeated vocabulary questions suggest previewing key terms before playback.',
                    $insight,
                ),
                $this->recommend(
                    LearningRecommendationType::ExplainMoreExamples,
                    'Vocabulary gaps were detected; additional examples may help retention.',
                    $insight,
                ),
            ],
            LearningInsightType::GrammarGap => [
                $this->recommend(
                    LearningRecommendationType::UseDetailedExplanations,
                    'Grammar difficulty signals suggest more detailed explanations.',
                    $insight,
                ),
                $this->recommend(
                    LearningRecommendationType::ExplainMoreExamples,
                    'Grammar gaps were detected; more examples can reinforce patterns.',
                    $insight,
                ),
            ],
            LearningInsightType::PreferredExplanationStyle => $this->explanationRecommendations($insight),
            LearningInsightType::PreferredTranslationStyle => $this->translationRecommendations($insight),
            LearningInsightType::PreferredVoiceLanguage => [
                $this->recommend(
                    LearningRecommendationType::PreferVoiceLanguage,
                    $this->voiceLanguageExplanation($insight),
                    $insight,
                ),
            ],
            LearningInsightType::PreferredChallengeLevel => $this->challengeRecommendations($insight),
            LearningInsightType::ProviderPreference => [
                $this->recommend(
                    LearningRecommendationType::PreferProvider,
                    $this->providerExplanation($insight),
                    $insight,
                ),
            ],
            LearningInsightType::QualityRiskPattern => [
                $this->recommend(
                    LearningRecommendationType::SlowDownPlayback,
                    'Recurring low quality scores suggest slowing playback and review cadence.',
                    $insight,
                ),
            ],
            LearningInsightType::PacePreference => [
                $this->recommend(
                    LearningRecommendationType::SlowDownPlayback,
                    'Frequent pauses in technical segments suggest slower playback pacing.',
                    $insight,
                ),
                $this->recommend(
                    LearningRecommendationType::ShowVocabularyBeforePlayback,
                    'Technical segment pauses suggest previewing vocabulary first.',
                    $insight,
                ),
            ],
            LearningInsightType::FrequentTopic => [],
        };
    }

    /**
     * @return list<LearningRecommendation>
     */
    private function explanationRecommendations(LearningInsight $insight): array
    {
        if (str_contains(strtolower($insight->summary()), 'short')) {
            return [
                $this->recommend(
                    LearningRecommendationType::UseShortExplanations,
                    'Explanation depth signals favor concise Shadow answers.',
                    $insight,
                ),
            ];
        }

        return [
            $this->recommend(
                LearningRecommendationType::UseDetailedExplanations,
                'Explanation depth signals favor detailed Shadow answers.',
                $insight,
            ),
        ];
    }

    /**
     * @return list<LearningRecommendation>
     */
    private function translationRecommendations(LearningInsight $insight): array
    {
        if (str_contains(strtolower($insight->summary()), 'literal')) {
            return [
                $this->recommend(
                    LearningRecommendationType::UseLiteralTranslation,
                    'Translation style signals favor literal rendering.',
                    $insight,
                ),
            ];
        }

        return [
            $this->recommend(
                LearningRecommendationType::UseNaturalTranslation,
                'Translation style signals favor natural rendering.',
                $insight,
            ),
        ];
    }

    /**
     * @return list<LearningRecommendation>
     */
    private function challengeRecommendations(LearningInsight $insight): array
    {
        if (str_contains(strtolower($insight->summary()), 'lower')) {
            return [
                $this->recommend(
                    LearningRecommendationType::DecreaseChallengeLevel,
                    'Hard challenge skips and failures suggest reducing proactive tutor difficulty.',
                    $insight,
                ),
            ];
        }

        return [
            $this->recommend(
                LearningRecommendationType::IncreaseChallengeLevel,
                'Easy challenge success suggests increasing proactive tutor difficulty.',
                $insight,
            ),
        ];
    }

    private function voiceLanguageExplanation(LearningInsight $insight): string
    {
        if (preg_match('/Voice language "([a-z]{2})"/', $insight->summary(), $matches) === 1) {
            return sprintf(
                'Voice language preference signals favor Shadow voice language "%s".',
                $matches[1],
            );
        }

        return 'Voice language preference signals suggest updating Shadow default voice language.';
    }

    private function providerExplanation(LearningInsight $insight): string
    {
        if (preg_match('/Provider "([^"]+)"/', $insight->summary(), $matches) === 1) {
            return sprintf(
                'Provider performance signals favor soft preference for "%s" in AI Director recommendations.',
                $matches[1],
            );
        }

        return 'Provider performance signals suggest a soft provider preference for AI Director.';
    }

    private function recommend(
        LearningRecommendationType $type,
        string $explanation,
        LearningInsight $insight,
    ): LearningRecommendation {
        return LearningRecommendation::derive(
            $type,
            $explanation,
            [$insight->id()->value],
        );
    }
}
