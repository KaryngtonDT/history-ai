<?php

declare(strict_types=1);

namespace App\Infrastructure\Speech;

use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Infrastructure\Speech\Exception\InvalidSpeechToTextConfigurationException;

final class SpeechToTextProviderFactory
{
    public const string PROVIDER_FASTER_WHISPER = 'faster_whisper';

    public function __construct(
        private readonly string $providerName,
        private readonly FasterWhisperProvider $fasterWhisperProvider,
    ) {
    }

    public function create(): SpeechToTextProviderInterface
    {
        $normalized = strtolower(trim($this->providerName));

        if ('' === $normalized || self::PROVIDER_FASTER_WHISPER === $normalized) {
            return $this->fasterWhisperProvider;
        }

        throw new InvalidSpeechToTextConfigurationException(sprintf(
            'Unknown STT_PROVIDER value "%s". Supported values: faster_whisper.',
            $this->providerName,
        ));
    }
}
