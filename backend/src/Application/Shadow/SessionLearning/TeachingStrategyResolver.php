<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning;

use App\Domain\Shadow\SessionLearning\PedagogicalConfidence;
use App\Domain\Shadow\SessionLearning\PedagogicalFatigue;
use App\Domain\Shadow\SessionLearning\SessionLearningState;
use App\Domain\Shadow\SessionLearning\SessionVoiceStyleKind;
use App\Domain\Shadow\SessionLearning\SpeakingPaceKind;
use App\Domain\Shadow\SessionLearning\TeachingStrategy;
use App\Domain\Shadow\SessionLearning\TeachingStrategyKind;
use App\Domain\Shadow\ShadowChallengeLevel;
use App\Domain\Shadow\ShadowExplanationStyle;

final class TeachingStrategyResolver
{
    public function resolve(SessionLearningState $state): TeachingStrategy
    {
        if (!$state->preferences()->adaptiveEnabled()) {
            return $this->neutral();
        }

        $explanationStyle = match ($state->strategyKind()) {
            TeachingStrategyKind::ExampleDriven, TeachingStrategyKind::Recovery => ShadowExplanationStyle::ExampleFirst,
            TeachingStrategyKind::ConciseSupport => ShadowExplanationStyle::Short,
            TeachingStrategyKind::StoryBased => ShadowExplanationStyle::Detailed,
            default => ShadowExplanationStyle::Detailed,
        };

        $challengeLevel = match ($state->difficulty()) {
            \App\Domain\Shadow\SessionLearning\PedagogicalDifficulty::Easy => ShadowChallengeLevel::Easy,
            \App\Domain\Shadow\SessionLearning\PedagogicalDifficulty::Advanced => ShadowChallengeLevel::Hard,
            default => ShadowChallengeLevel::Normal,
        };

        $useExamples = in_array($state->strategyKind(), [
            TeachingStrategyKind::ExampleDriven,
            TeachingStrategyKind::Recovery,
            TeachingStrategyKind::Balanced,
        ], true);

        $useAnalogies = PedagogicalConfidence::Struggling === $state->confidence()
            || TeachingStrategyKind::Recovery === $state->strategyKind();

        $offerPausePrompt = PedagogicalFatigue::High === $state->fatigue();

        $summary = sprintf(
            '%s strategy with %s pace and %s voice.',
            str_replace('_', ' ', $state->strategyKind()->value),
            $state->speakingPace()->value,
            $state->voiceStyle()->value,
        );

        return new TeachingStrategy(
            $state->strategyKind(),
            $state->attention(),
            $state->confidence(),
            $state->fatigue(),
            $state->difficulty(),
            $state->speakingPace(),
            $state->voiceStyle(),
            $explanationStyle,
            $challengeLevel,
            $useExamples,
            $useAnalogies,
            $offerPausePrompt,
            $summary,
        );
    }

    public function neutral(): TeachingStrategy
    {
        return new TeachingStrategy(
            TeachingStrategyKind::Balanced,
            \App\Domain\Shadow\SessionLearning\PedagogicalAttention::Medium,
            PedagogicalConfidence::Stable,
            PedagogicalFatigue::Low,
            \App\Domain\Shadow\SessionLearning\PedagogicalDifficulty::Intermediate,
            SpeakingPaceKind::Normal,
            SessionVoiceStyleKind::Neutral,
            ShadowExplanationStyle::Detailed,
            ShadowChallengeLevel::Normal,
            true,
            false,
            false,
            'Balanced strategy with normal pace and neutral voice.',
        );
    }
}
