<?php

declare(strict_types=1);

namespace App\Application\Shadow\SessionLearning\DTO;

use App\Domain\Shadow\SessionLearning\TeachingStrategy;

final readonly class SessionStrategyView
{
    public function __construct(
        public string $kind,
        public string $attention,
        public string $confidence,
        public string $fatigue,
        public string $difficulty,
        public string $speakingPace,
        public string $voiceStyle,
        public string $explanationStyle,
        public string $challengeLevel,
        public bool $useExamples,
        public bool $useAnalogies,
        public bool $offerPausePrompt,
        public string $summary,
    ) {
    }

    public static function fromStrategy(TeachingStrategy $strategy): self
    {
        return new self(
            $strategy->kind()->value,
            $strategy->attention()->value,
            $strategy->confidence()->value,
            $strategy->fatigue()->value,
            $strategy->difficulty()->value,
            $strategy->speakingPace()->value,
            $strategy->voiceStyle()->value,
            $strategy->explanationStyle()->value,
            $strategy->challengeLevel()->value,
            $strategy->useExamples(),
            $strategy->useAnalogies(),
            $strategy->offerPausePrompt(),
            $strategy->summary(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'attention' => $this->attention,
            'confidence' => $this->confidence,
            'fatigue' => $this->fatigue,
            'difficulty' => $this->difficulty,
            'speakingPace' => $this->speakingPace,
            'voiceStyle' => $this->voiceStyle,
            'explanationStyle' => $this->explanationStyle,
            'challengeLevel' => $this->challengeLevel,
            'useExamples' => $this->useExamples,
            'useAnalogies' => $this->useAnalogies,
            'offerPausePrompt' => $this->offerPausePrompt,
            'summary' => $this->summary,
        ];
    }
}
