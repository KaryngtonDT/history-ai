<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

use App\Domain\Shadow\ShadowChallengeLevel;
use App\Domain\Shadow\ShadowExplanationStyle;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Video\VideoId;

final readonly class TeachingStrategy
{
    public function __construct(
        private TeachingStrategyKind $kind,
        private PedagogicalAttention $attention,
        private PedagogicalConfidence $confidence,
        private PedagogicalFatigue $fatigue,
        private PedagogicalDifficulty $difficulty,
        private SpeakingPaceKind $speakingPace,
        private SessionVoiceStyleKind $voiceStyle,
        private ShadowExplanationStyle $explanationStyle,
        private ShadowChallengeLevel $challengeLevel,
        private bool $useExamples,
        private bool $useAnalogies,
        private bool $offerPausePrompt,
        private string $summary,
    ) {
    }

    public function kind(): TeachingStrategyKind
    {
        return $this->kind;
    }

    public function attention(): PedagogicalAttention
    {
        return $this->attention;
    }

    public function confidence(): PedagogicalConfidence
    {
        return $this->confidence;
    }

    public function fatigue(): PedagogicalFatigue
    {
        return $this->fatigue;
    }

    public function difficulty(): PedagogicalDifficulty
    {
        return $this->difficulty;
    }

    public function speakingPace(): SpeakingPaceKind
    {
        return $this->speakingPace;
    }

    public function voiceStyle(): SessionVoiceStyleKind
    {
        return $this->voiceStyle;
    }

    public function explanationStyle(): ShadowExplanationStyle
    {
        return $this->explanationStyle;
    }

    public function challengeLevel(): ShadowChallengeLevel
    {
        return $this->challengeLevel;
    }

    public function useExamples(): bool
    {
        return $this->useExamples;
    }

    public function useAnalogies(): bool
    {
        return $this->useAnalogies;
    }

    public function offerPausePrompt(): bool
    {
        return $this->offerPausePrompt;
    }

    public function summary(): string
    {
        return $this->summary;
    }
}
