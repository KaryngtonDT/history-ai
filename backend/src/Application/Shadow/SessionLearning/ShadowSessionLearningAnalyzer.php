<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning;

use App\Domain\Shadow\SessionLearning\PedagogicalDifficulty;
use App\Domain\Shadow\SessionLearning\PedagogicalEnergy;
use App\Domain\Shadow\SessionLearning\PedagogicalFatigue;
use App\Domain\Shadow\SessionLearning\PedagogicalConfidence;
use App\Domain\Shadow\SessionLearning\PedagogicalPace;
use App\Domain\Shadow\SessionLearning\PedagogicalAttention;
use App\Domain\Shadow\SessionLearning\SessionLearningState;
use App\Domain\Shadow\SessionLearning\SessionObservation;
use App\Domain\Shadow\SessionLearning\SessionObservationType;
use App\Domain\Shadow\SessionLearning\SessionVoiceStyleKind;
use App\Domain\Shadow\SessionLearning\SpeakingPaceKind;
use App\Domain\Shadow\SessionLearning\StrategyAdjustment;
use App\Domain\Shadow\SessionLearning\StrategyAdjustmentCollection;
use App\Domain\Shadow\SessionLearning\TeachingStrategyKind;
use App\Domain\Shadow\ShadowInteractionKind;
use App\Domain\Shadow\ShadowSession;

final class ShadowSessionLearningAnalyzer
{
    public function __construct(
        private readonly AttentionDetector $attentionDetector,
        private readonly FatigueDetector $fatigueDetector,
        private readonly ConfidenceDetector $confidenceDetector,
        private readonly PaceDetector $paceDetector,
    ) {
    }

    public function analyze(ShadowSession $session, SessionLearningState $state): SessionLearningState
    {
        $pauseCount = 0;
        $questionCount = 0;
        $repeatedQuestions = [];

        foreach ($session->interactions()->all() as $interaction) {
            match ($interaction->kind()) {
                ShadowInteractionKind::Pause => ++$pauseCount,
                ShadowInteractionKind::Question => ++$questionCount,
                default => null,
            };

            if (ShadowInteractionKind::Question === $interaction->kind()) {
                $text = strtolower(trim($interaction->question()?->text() ?? ''));
                if ('' !== $text) {
                    $repeatedQuestions[$text] = ($repeatedQuestions[$text] ?? 0) + 1;
                }
            }
        }

        $replayCount = $state->replayCount();
        $skipCount = $state->skipCount();
        $challengeSuccessCount = $state->challengeSuccessCount();
        $slowResponseCount = $state->slowResponseCount();
        $fastResponseCount = $state->fastResponseCount();
        $repeatedQuestionCount = count(array_filter(
            $repeatedQuestions,
            static fn (int $count): bool => $count >= 2,
        ));

        $sessionMinutes = max(0.1, $session->currentTimestamp()->seconds() / 60.0);

        $attention = $this->attentionDetector->detect($pauseCount, $replayCount, $skipCount);
        $fatigue = $this->fatigueDetector->detect($pauseCount, $questionCount, $sessionMinutes);
        $confidence = $this->confidenceDetector->detect(
            $repeatedQuestionCount,
            $replayCount,
            $challengeSuccessCount,
            $slowResponseCount,
        );
        $pace = $this->paceDetector->detect($fastResponseCount, $slowResponseCount, $pauseCount);

        $energy = match ($fatigue) {
            PedagogicalFatigue::High => PedagogicalEnergy::Low,
            PedagogicalFatigue::Medium => PedagogicalEnergy::Medium,
            PedagogicalFatigue::Low => PedagogicalConfidence::Growing === $confidence
                ? PedagogicalEnergy::High
                : PedagogicalEnergy::Medium,
        };

        $difficulty = match ($confidence) {
            PedagogicalConfidence::Growing => PedagogicalDifficulty::Advanced,
            PedagogicalConfidence::Struggling => PedagogicalDifficulty::Easy,
            PedagogicalConfidence::Stable => PedagogicalDifficulty::Intermediate,
        };

        [$strategyKind, $speakingPace, $voiceStyle] = $this->resolveStrategyKnobs(
            $attention,
            $fatigue,
            $confidence,
            $pace,
            $difficulty,
        );

        $adjustments = $this->trackAdjustments(
            $state,
            $session->currentTimestamp()->seconds(),
            $attention,
            $fatigue,
            $confidence,
            $strategyKind,
            $speakingPace,
        );

        return $state->withDerivedState(
            $attention,
            $fatigue,
            $confidence,
            $pace,
            $energy,
            $difficulty,
            $strategyKind,
            $speakingPace,
            $voiceStyle,
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

    public function recordObservation(
        SessionLearningState $state,
        SessionObservationType $type,
        float $timeSeconds,
        ?string $detail = null,
    ): SessionLearningState {
        $observation = new SessionObservation($type, $timeSeconds, $detail);

        $state = $state->withObservation($observation);

        return match ($type) {
            SessionObservationType::Replay => $this->incrementMetric($state, 'replay'),
            SessionObservationType::Skip => $this->incrementMetric($state, 'skip'),
            SessionObservationType::ChallengeSuccess => $this->incrementMetric($state, 'challengeSuccess'),
            SessionObservationType::SlowResponse => $this->incrementMetric($state, 'slowResponse'),
            SessionObservationType::FastResponse => $this->incrementMetric($state, 'fastResponse'),
            default => $state,
        };
    }

    /**
     * @return array{TeachingStrategyKind, SpeakingPaceKind, SessionVoiceStyleKind}
     */
    private function resolveStrategyKnobs(
        PedagogicalAttention $attention,
        PedagogicalFatigue $fatigue,
        PedagogicalConfidence $confidence,
        PedagogicalPace $pace,
        PedagogicalDifficulty $difficulty,
    ): array {
        if (PedagogicalFatigue::High === $fatigue || PedagogicalConfidence::Struggling === $confidence) {
            return [TeachingStrategyKind::Recovery, SpeakingPaceKind::Slow, SessionVoiceStyleKind::Calm];
        }

        if (PedagogicalConfidence::Growing === $confidence && PedagogicalDifficulty::Advanced === $difficulty) {
            return [TeachingStrategyKind::ChallengeFocused, SpeakingPaceKind::Fast, SessionVoiceStyleKind::Dynamic];
        }

        if (PedagogicalAttention::Low === $attention) {
            return [TeachingStrategyKind::ExampleDriven, SpeakingPaceKind::Normal, SessionVoiceStyleKind::Storyteller];
        }

        if (PedagogicalPace::Fast === $pace) {
            return [TeachingStrategyKind::ConciseSupport, SpeakingPaceKind::Fast, SessionVoiceStyleKind::Neutral];
        }

        return [TeachingStrategyKind::Balanced, SpeakingPaceKind::Normal, SessionVoiceStyleKind::Neutral];
    }

    private function trackAdjustments(
        SessionLearningState $state,
        float $timeSeconds,
        PedagogicalAttention $attention,
        PedagogicalFatigue $fatigue,
        PedagogicalConfidence $confidence,
        TeachingStrategyKind $strategyKind,
        SpeakingPaceKind $speakingPace,
    ): StrategyAdjustmentCollection {
        $adjustments = $state->adjustments();

        if ($state->strategyKind() !== $strategyKind) {
            $adjustments = $adjustments->append(new StrategyAdjustment(
                $timeSeconds,
                sprintf('Strategy: %s', $strategyKind->value),
                sprintf(
                    'attention=%s fatigue=%s confidence=%s',
                    $attention->value,
                    $fatigue->value,
                    $confidence->value,
                ),
            ));
        }

        if ($state->speakingPace() !== $speakingPace) {
            $adjustments = $adjustments->append(new StrategyAdjustment(
                $timeSeconds,
                sprintf('Speaking pace: %s', $speakingPace->value),
                'Pace adapted to session signals.',
            ));
        }

        return $adjustments;
    }

    private function incrementMetric(SessionLearningState $state, string $metric): SessionLearningState
    {
        return $state->withDerivedState(
            $state->attention(),
            $state->fatigue(),
            $state->confidence(),
            $state->pace(),
            $state->energy(),
            $state->difficulty(),
            $state->strategyKind(),
            $state->speakingPace(),
            $state->voiceStyle(),
            $state->adjustments(),
            $state->pauseCount(),
            'replay' === $metric ? $state->replayCount() + 1 : $state->replayCount(),
            $state->questionCount(),
            $state->repeatedQuestionCount(),
            'skip' === $metric ? $state->skipCount() + 1 : $state->skipCount(),
            'challengeSuccess' === $metric ? $state->challengeSuccessCount() + 1 : $state->challengeSuccessCount(),
            'slowResponse' === $metric ? $state->slowResponseCount() + 1 : $state->slowResponseCount(),
            'fastResponse' === $metric ? $state->fastResponseCount() + 1 : $state->fastResponseCount(),
        );
    }
}
