<?php

declare(strict_types=1);

namespace App\Application\Learning\DTO;

use App\Domain\Shadow\ShadowChallengeLevel;
use App\Domain\Shadow\ShadowExplanationStyle;
use App\Domain\Shadow\ShadowVoiceLanguage;

final readonly class LearningAdaptiveHints
{
    /**
     * @param list<string> $appliedRecommendations
     */
    public function __construct(
        public bool $active,
        public ?ShadowExplanationStyle $explanationStyle = null,
        public ?ShadowChallengeLevel $challengeLevel = null,
        public ?ShadowVoiceLanguage $voiceLanguage = null,
        public ?string $translationStyle = null,
        public ?string $preferredProvider = null,
        public array $appliedRecommendations = [],
    ) {
    }

    public static function inactive(): self
    {
        return new self(active: false);
    }
}
