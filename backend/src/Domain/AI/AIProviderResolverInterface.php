<?php

declare(strict_types=1);

namespace App\Domain\AI;

use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationProviderInterface;

interface AIProviderResolverInterface
{
    public function registry(): AIEngineRegistry;

    public function resolveSpeechToText(?string $providerId = null): SpeechToTextProviderInterface;

    public function resolveTranslation(?TranslationProvider $provider = null): TranslationProviderInterface;
}
