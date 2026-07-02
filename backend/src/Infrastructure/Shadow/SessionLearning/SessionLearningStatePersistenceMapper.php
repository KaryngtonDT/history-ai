<?php

declare(strict_types=1);

namespace App\Infrastructure\Shadow\SessionLearning;

use App\Domain\Shadow\SessionLearning\SessionLearningState;
use App\Domain\Shadow\SessionLearning\SessionLearningStateRepositoryInterface;
use App\Domain\Shadow\SessionLearning\SessionLearningPreferences;
use App\Domain\Shadow\SessionLearning\SessionObservation;
use App\Domain\Shadow\SessionLearning\SessionObservationCollection;
use App\Domain\Shadow\SessionLearning\SessionObservationType;
use App\Domain\Shadow\SessionLearning\StrategyAdjustment;
use App\Domain\Shadow\SessionLearning\StrategyAdjustmentCollection;
use App\Domain\Shadow\SessionLearning\PedagogicalAttention;
use App\Domain\Shadow\SessionLearning\PedagogicalFatigue;
use App\Domain\Shadow\SessionLearning\PedagogicalConfidence;
use App\Domain\Shadow\SessionLearning\PedagogicalPace;
use App\Domain\Shadow\SessionLearning\PedagogicalEnergy;
use App\Domain\Shadow\SessionLearning\PedagogicalDifficulty;
use App\Domain\Shadow\SessionLearning\TeachingStrategyKind;
use App\Domain\Shadow\SessionLearning\SpeakingPaceKind;
use App\Domain\Shadow\SessionLearning\SessionVoiceStyleKind;
use App\Domain\Shadow\ShadowSessionId;
use App\Domain\Video\VideoId;
use JsonException;
use RuntimeException;

final class SessionLearningStatePersistenceMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(SessionLearningState $state): array
    {
        return [
            'sessionId' => $state->sessionId()->value,
            'videoId' => $state->videoId()->value,
            'preferences' => [
                'adaptiveEnabled' => $state->preferences()->adaptiveEnabled(),
            ],
            'attention' => $state->attention()->value,
            'fatigue' => $state->fatigue()->value,
            'confidence' => $state->confidence()->value,
            'pace' => $state->pace()->value,
            'energy' => $state->energy()->value,
            'difficulty' => $state->difficulty()->value,
            'strategyKind' => $state->strategyKind()->value,
            'speakingPace' => $state->speakingPace()->value,
            'voiceStyle' => $state->voiceStyle()->value,
            'observations' => array_map(
                static fn (SessionObservation $item): array => [
                    'type' => $item->type()->value,
                    'timeSeconds' => $item->timeSeconds(),
                    'detail' => $item->detail(),
                ],
                $state->observations()->all(),
            ),
            'adjustments' => array_map(
                static fn (StrategyAdjustment $item): array => [
                    'timeSeconds' => $item->timeSeconds(),
                    'label' => $item->label(),
                    'reason' => $item->reason(),
                ],
                $state->adjustments()->all(),
            ),
            'metrics' => [
                'pauseCount' => $state->pauseCount(),
                'replayCount' => $state->replayCount(),
                'questionCount' => $state->questionCount(),
                'repeatedQuestionCount' => $state->repeatedQuestionCount(),
                'skipCount' => $state->skipCount(),
                'challengeSuccessCount' => $state->challengeSuccessCount(),
                'slowResponseCount' => $state->slowResponseCount(),
                'fastResponseCount' => $state->fastResponseCount(),
            ],
        ];
    }

    public function fromJson(string $json): SessionLearningState
    {
        try {
            /** @var array<string, mixed> $data */
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to decode session learning state JSON.', 0, $exception);
        }

        $metrics = is_array($data['metrics'] ?? null) ? $data['metrics'] : [];

        return new SessionLearningState(
            new ShadowSessionId((string) ($data['sessionId'] ?? '')),
            new VideoId((string) ($data['videoId'] ?? '')),
            new SessionLearningPreferences((bool) ($data['preferences']['adaptiveEnabled'] ?? true)),
            PedagogicalAttention::from((string) ($data['attention'] ?? PedagogicalAttention::Medium->value)),
            PedagogicalFatigue::from((string) ($data['fatigue'] ?? PedagogicalFatigue::Low->value)),
            PedagogicalConfidence::from((string) ($data['confidence'] ?? PedagogicalConfidence::Stable->value)),
            PedagogicalPace::from((string) ($data['pace'] ?? PedagogicalPace::Normal->value)),
            PedagogicalEnergy::from((string) ($data['energy'] ?? PedagogicalEnergy::Medium->value)),
            PedagogicalDifficulty::from((string) ($data['difficulty'] ?? PedagogicalDifficulty::Intermediate->value)),
            TeachingStrategyKind::from((string) ($data['strategyKind'] ?? TeachingStrategyKind::Balanced->value)),
            SpeakingPaceKind::from((string) ($data['speakingPace'] ?? SpeakingPaceKind::Normal->value)),
            SessionVoiceStyleKind::from((string) ($data['voiceStyle'] ?? SessionVoiceStyleKind::Neutral->value)),
            $this->mapObservations($data['observations'] ?? []),
            $this->mapAdjustments($data['adjustments'] ?? []),
            (int) ($metrics['pauseCount'] ?? 0),
            (int) ($metrics['replayCount'] ?? 0),
            (int) ($metrics['questionCount'] ?? 0),
            (int) ($metrics['repeatedQuestionCount'] ?? 0),
            (int) ($metrics['skipCount'] ?? 0),
            (int) ($metrics['challengeSuccessCount'] ?? 0),
            (int) ($metrics['slowResponseCount'] ?? 0),
            (int) ($metrics['fastResponseCount'] ?? 0),
        );
    }

    /**
     * @param mixed $items
     */
    private function mapObservations(mixed $items): SessionObservationCollection
    {
        if (!is_array($items)) {
            return SessionObservationCollection::empty();
        }

        $observations = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $observations[] = new SessionObservation(
                SessionObservationType::from((string) ($item['type'] ?? SessionObservationType::Pause->value)),
                (float) ($item['timeSeconds'] ?? 0),
                isset($item['detail']) ? (string) $item['detail'] : null,
            );
        }

        return new SessionObservationCollection($observations);
    }

    /**
     * @param mixed $items
     */
    private function mapAdjustments(mixed $items): StrategyAdjustmentCollection
    {
        if (!is_array($items)) {
            return StrategyAdjustmentCollection::empty();
        }

        $adjustments = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $adjustments[] = new StrategyAdjustment(
                (float) ($item['timeSeconds'] ?? 0),
                (string) ($item['label'] ?? ''),
                (string) ($item['reason'] ?? ''),
            );
        }

        return new StrategyAdjustmentCollection($adjustments);
    }
}
