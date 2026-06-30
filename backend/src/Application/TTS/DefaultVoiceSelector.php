<?php

declare(strict_types=1);

namespace App\Application\TTS;

use App\Domain\Translation\TranslationLanguage;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use App\Domain\TTS\Voice;
use App\Domain\TTS\VoiceCatalog;

final class DefaultVoiceSelector
{
    public function resolve(TranslationLanguage $targetLanguage, ?string $voiceId = null): Voice
    {
        $catalog = VoiceCatalog::defaultVoices();

        if (null !== $voiceId && '' !== trim($voiceId)) {
            $selected = $catalog->findById(trim($voiceId));

            if (null === $selected) {
                throw new InvalidAudioArtifactException(sprintf('Voice "%s" is not available.', $voiceId));
            }

            return $selected;
        }

        $voiceLanguage = $this->mapTranslationLanguage($targetLanguage);

        foreach ($catalog->all() as $voice) {
            if ($voice->language() === $voiceLanguage) {
                return $voice;
            }
        }

        $fallback = $catalog->all()[0] ?? null;

        if (null === $fallback) {
            throw new InvalidAudioArtifactException('No voices are configured.');
        }

        return $fallback;
    }

    private function mapTranslationLanguage(TranslationLanguage $language): \App\Domain\TTS\VoiceLanguage
    {
        return match ($language) {
            TranslationLanguage::English => \App\Domain\TTS\VoiceLanguage::English,
            TranslationLanguage::French => \App\Domain\TTS\VoiceLanguage::French,
            TranslationLanguage::German => \App\Domain\TTS\VoiceLanguage::German,
            TranslationLanguage::Spanish => \App\Domain\TTS\VoiceLanguage::Spanish,
            TranslationLanguage::Italian => \App\Domain\TTS\VoiceLanguage::Italian,
            TranslationLanguage::Unknown => \App\Domain\TTS\VoiceLanguage::English,
        };
    }
}
