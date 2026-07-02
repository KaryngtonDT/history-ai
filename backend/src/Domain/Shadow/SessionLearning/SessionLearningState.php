<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Video\VideoId;

final readonly class SessionLearningState
{
    public function __construct(
        private ShadowSessionId $sessionId,
        private VideoId $videoId,
        private SessionLearningPreferences $preferences,
        private PedagogicalAttention $attention,
        private PedagogicalFatigue $fatigue,
        private PedagogicalConfidence $confidence,
        private PedagogicalPace $pace,
        private PedagogicalEnergy $energy,
        private PedagogicalDifficulty $difficulty,
        private TeachingStrategyKind $strategyKind,
        private SpeakingPaceKind $speakingPace,
        private SessionVoiceStyleKind $voiceStyle,
        private SessionObservationCollection $observations,
        private StrategyAdjustmentCollection $adjustments,
        private int $pauseCount,
        private int $replayCount,
        private int $questionCount,
        private int $repeatedQuestionCount,
        private int $skipCount,
        private int $challengeSuccessCount,
        private int $slowResponseCount,
        private int $fastResponseCount,
    ) {
    }

    public static function start(ShadowSessionId $sessionId, VideoId $videoId): self
    {
        return new self(
            $sessionId,
            $videoId,
            SessionLearningPreferences::default(),
            PedagogicalAttention::Medium,
            PedagogicalFatigue::Low,
            PedagogicalConfidence::Stable,
            PedagogicalPace::Normal,
            PedagogicalEnergy::Medium,
            PedagogicalDifficulty::Intermediate,
            TeachingStrategyKind::Balanced,
            SpeakingPaceKind::Normal,
            SessionVoiceStyleKind::Neutral,
            SessionObservationCollection::empty(),
            StrategyAdjustmentCollection::empty(),
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
        );
    }

    public function sessionId(): ShadowSessionId
    {
        return $this->sessionId;
    }

    public function videoId(): VideoId
    {
        return $this->videoId;
    }

    public function preferences(): SessionLearningPreferences
    {
        return $this->preferences;
    }

    public function attention(): PedagogicalAttention
    {
        return $this->attention;
    }

    public function fatigue(): PedagogicalFatigue
    {
        return $this->fatigue;
    }

    public function confidence(): PedagogicalConfidence
    {
        return $this->confidence;
    }

    public function pace(): PedagogicalPace
    {
        return $this->pace;
    }

    public function energy(): PedagogicalEnergy
    {
        return $this->energy;
    }

    public function difficulty(): PedagogicalDifficulty
    {
        return $this->difficulty;
    }

    public function strategyKind(): TeachingStrategyKind
    {
        return $this->strategyKind;
    }

    public function speakingPace(): SpeakingPaceKind
    {
        return $this->speakingPace;
    }

    public function voiceStyle(): SessionVoiceStyleKind
    {
        return $this->voiceStyle;
    }

    public function observations(): SessionObservationCollection
    {
        return $this->observations;
    }

    public function adjustments(): StrategyAdjustmentCollection
    {
        return $this->adjustments;
    }

    public function pauseCount(): int
    {
        return $this->pauseCount;
    }

    public function replayCount(): int
    {
        return $this->replayCount;
    }

    public function questionCount(): int
    {
        return $this->questionCount;
    }

    public function repeatedQuestionCount(): int
    {
        return $this->repeatedQuestionCount;
    }

    public function skipCount(): int
    {
        return $this->skipCount;
    }

    public function challengeSuccessCount(): int
    {
        return $this->challengeSuccessCount;
    }

    public function slowResponseCount(): int
    {
        return $this->slowResponseCount;
    }

    public function fastResponseCount(): int
    {
        return $this->fastResponseCount;
    }

    public function withPreferences(SessionLearningPreferences $preferences): self
    {
        return $this->rebuild(preferences: $preferences);
    }

    public function withObservation(SessionObservation $observation): self
    {
        return $this->rebuild(
            observations: $this->observations->append($observation),
        );
    }

    public function withDerivedState(
        PedagogicalAttention $attention,
        PedagogicalFatigue $fatigue,
        PedagogicalConfidence $confidence,
        PedagogicalPace $pace,
        PedagogicalEnergy $energy,
        PedagogicalDifficulty $difficulty,
        TeachingStrategyKind $strategyKind,
        SpeakingPaceKind $speakingPace,
        SessionVoiceStyleKind $voiceStyle,
        StrategyAdjustmentCollection $adjustments,
        int $pauseCount,
        int $replayCount,
        int $questionCount,
        int $repeatedQuestionCount,
        int $skipCount,
        int $challengeSuccessCount,
        int $slowResponseCount,
        int $fastResponseCount,
    ): self {
        return new self(
            $this->sessionId,
            $this->videoId,
            $this->preferences,
            $attention,
            $fatigue,
            $confidence,
            $pace,
            $energy,
            $difficulty,
            $strategyKind,
            $speakingPace,
            $voiceStyle,
            $this->observations,
            $adjustments,
            $pauseCount,
            $replayCount,
            $questionCount,
            $repeatedQuestionCount,
            $skipCount,
            $challengeSuccessCount,
            $slowResponseCount,
            $fastResponseCount,
        );
    }

    private function rebuild(
        ?SessionLearningPreferences $preferences = null,
        ?SessionObservationCollection $observations = null,
    ): self {
        return new self(
            $this->sessionId,
            $this->videoId,
            $preferences ?? $this->preferences,
            $this->attention,
            $this->fatigue,
            $this->confidence,
            $this->pace,
            $this->energy,
            $this->difficulty,
            $this->strategyKind,
            $this->speakingPace,
            $this->voiceStyle,
            $observations ?? $this->observations,
            $this->adjustments,
            $this->pauseCount,
            $this->replayCount,
            $this->questionCount,
            $this->repeatedQuestionCount,
            $this->skipCount,
            $this->challengeSuccessCount,
            $this->slowResponseCount,
            $this->fastResponseCount,
        );
    }
}
