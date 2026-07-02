<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning\DTO;

use App\Domain\Shadow\SessionLearning\SessionLearningState;
use App\Domain\Shadow\SessionLearning\StrategyAdjustment;
use App\Domain\Shadow\SessionLearning\TeachingStrategy;

final readonly class SessionLearningView
{
    /**
     * @param list<array{timeSeconds: float, label: string, reason: string}> $adjustments
     */
    public function __construct(
        public string $sessionId,
        public bool $adaptiveEnabled,
        public string $attention,
        public string $confidence,
        public string $fatigue,
        public string $pace,
        public string $energy,
        public string $difficulty,
        public string $strategyKind,
        public string $speakingPace,
        public string $voiceStyle,
        public array $adjustments,
    ) {
    }

    public static function fromState(SessionLearningState $state): self
    {
        return new self(
            $state->sessionId()->value,
            $state->preferences()->adaptiveEnabled(),
            $state->attention()->value,
            $state->confidence()->value,
            $state->fatigue()->value,
            $state->pace()->value,
            $state->energy()->value,
            $state->difficulty()->value,
            $state->strategyKind()->value,
            $state->speakingPace()->value,
            $state->voiceStyle()->value,
            array_map(
                static fn (StrategyAdjustment $item): array => [
                    'timeSeconds' => $item->timeSeconds(),
                    'label' => $item->label(),
                    'reason' => $item->reason(),
                ],
                $state->adjustments()->all(),
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'adaptiveEnabled' => $this->adaptiveEnabled,
            'attention' => $this->attention,
            'confidence' => $this->confidence,
            'fatigue' => $this->fatigue,
            'pace' => $this->pace,
            'energy' => $this->energy,
            'difficulty' => $this->difficulty,
            'strategyKind' => $this->strategyKind,
            'speakingPace' => $this->speakingPace,
            'voiceStyle' => $this->voiceStyle,
            'adjustments' => $this->adjustments,
        ];
    }
}
