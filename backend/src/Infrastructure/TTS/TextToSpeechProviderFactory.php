<?php

declare(strict_types=1);

namespace App\Infrastructure\TTS;

use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\TextToSpeechProviderInterface;
use App\Infrastructure\TTS\Exception\InvalidTextToSpeechConfigurationException;

final class TextToSpeechProviderFactory
{
    public const string PROVIDER_F5 = 'f5';

    public function __construct(
        private readonly string $defaultProviderName,
        private readonly F5TextToSpeechProvider $f5TextToSpeechProvider,
        private readonly MockTextToSpeechProvider $mockTextToSpeechProvider,
    ) {
    }

    public function resolve(?TextToSpeechProvider $provider = null): TextToSpeechProviderInterface
    {
        if (null !== $provider) {
            return match ($provider) {
                TextToSpeechProvider::F5TTS => $this->f5TextToSpeechProvider,
                TextToSpeechProvider::Mock => $this->mockTextToSpeechProvider,
                TextToSpeechProvider::Kokoro,
                TextToSpeechProvider::XTTS => throw new InvalidTextToSpeechConfigurationException(sprintf(
                    'Text-to-speech provider "%s" is not implemented yet.',
                    $provider->value,
                )),
            };
        }

        $normalized = strtolower(trim($this->defaultProviderName));

        if ('' === $normalized || self::PROVIDER_F5 === $normalized || 'f5_tts' === $normalized) {
            return $this->f5TextToSpeechProvider;
        }

        if ('mock' === $normalized) {
            return $this->mockTextToSpeechProvider;
        }

        throw new InvalidTextToSpeechConfigurationException(sprintf(
            'Unknown TTS_PROVIDER value "%s". Supported values: f5, mock.',
            $this->defaultProviderName,
        ));
    }
}
