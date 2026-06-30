<?php

declare(strict_types=1);

namespace App\Domain\AI;

use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationProviderInterface;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\TextToSpeechProviderInterface;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceCloneProviderInterface;

interface AIProviderResolverInterface
{
    public function registry(): AIEngineRegistry;

    public function resolveSpeechToText(?string $providerId = null): SpeechToTextProviderInterface;

    public function resolveTranslation(?TranslationProvider $provider = null): TranslationProviderInterface;

    public function resolveTextToSpeech(?TextToSpeechProvider $provider = null): TextToSpeechProviderInterface;

    public function resolveVoiceClone(?VoiceCloneProvider $provider = null): VoiceCloneProviderInterface;
}
