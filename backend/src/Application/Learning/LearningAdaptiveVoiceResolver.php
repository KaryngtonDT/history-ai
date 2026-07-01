<?php

declare(strict_types=1);

namespace App\Application\Learning;

use App\Application\Learning\DTO\LearningAdaptiveHints;
use App\Application\Shadow\DTO\ShadowAnswerVoiceMetadata;
use App\Domain\Shadow\ShadowVoiceMode;
use App\Domain\Shadow\ShadowVoicePreference;

final class LearningAdaptiveVoiceResolver
{
    public function apply(
        ShadowAnswerVoiceMetadata $voice,
        ShadowVoicePreference $preference,
        LearningAdaptiveHints $hints,
        bool $explicitLanguageRequested,
    ): ShadowAnswerVoiceMetadata {
        if (
            !$hints->active
            || null === $hints->voiceLanguage
            || $explicitLanguageRequested
            || ShadowVoiceMode::Manual === $preference->mode()
        ) {
            return $voice;
        }

        return new ShadowAnswerVoiceMetadata(
            answerLanguage: $hints->voiceLanguage,
            speechLanguage: $hints->voiceLanguage,
            fallbackUsed: false,
            reason: 'adaptive_voice_preference',
        );
    }
}
