<?php

declare(strict_types=1);

namespace App\Application\ShadowVoice;

use App\Domain\ShadowIdentity\ShadowHumorLevel;
use App\Domain\ShadowIdentity\ShadowVoicePersona;
use App\Domain\ShadowIdentity\ShadowVoiceProfile;

final class ShadowVoicePresetMapper
{
    /**
     * @return array{voiceProfile: ShadowVoiceProfile, persona: ShadowVoicePersona}
     */
    public function apply(ShadowVoicePreset $preset): array
    {
        return match ($preset) {
            ShadowVoicePreset::Developer => [
                'voiceProfile' => ShadowVoiceProfile::default()
                    ->withVoice('technical-precise-en', ShadowVoiceEngine::BrowserTts->value)
                    ->withSpeed(1.05),
                'persona' => ShadowVoicePersona::TechnicalExpert,
            ],
            ShadowVoicePreset::LanguageTeacher => [
                'voiceProfile' => ShadowVoiceProfile::default()
                    ->withVoice('friendly-warm-en', ShadowVoiceEngine::BrowserTts->value)
                    ->withSpeed(0.95),
                'persona' => ShadowVoicePersona::Teacher,
            ],
            ShadowVoicePreset::Historian => [
                'voiceProfile' => ShadowVoiceProfile::default()
                    ->withVoice('documentary-calm-en', ShadowVoiceEngine::BrowserTts->value)
                    ->withSpeed(0.92),
                'persona' => ShadowVoicePersona::Historian,
            ],
            ShadowVoicePreset::Professor => [
                'voiceProfile' => ShadowVoiceProfile::default()
                    ->withVoice('professor-clear-en', ShadowVoiceEngine::BrowserTts->value),
                'persona' => ShadowVoicePersona::Professor,
            ],
            ShadowVoicePreset::Friendly => [
                'voiceProfile' => ShadowVoiceProfile::default()
                    ->withVoice('friendly-warm-en', ShadowVoiceEngine::BrowserTts->value)
                    ->withHumor(ShadowHumorLevel::Medium),
                'persona' => ShadowVoicePersona::FriendlyCompanion,
            ],
            ShadowVoicePreset::Storyteller => [
                'voiceProfile' => ShadowVoiceProfile::default()
                    ->withVoice('storyteller-warm-en', ShadowVoiceEngine::BrowserTts->value)
                    ->withSpeed(0.9),
                'persona' => ShadowVoicePersona::Storyteller,
            ],
            ShadowVoicePreset::Custom => [
                'voiceProfile' => ShadowVoiceProfile::default(),
                'persona' => ShadowVoicePersona::Teacher,
            ],
        };
    }
}
