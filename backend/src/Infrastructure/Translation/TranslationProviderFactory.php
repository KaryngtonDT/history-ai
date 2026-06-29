<?php

declare(strict_types=1);

namespace App\Infrastructure\Translation;

use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationProviderInterface;
use App\Infrastructure\Translation\Exception\InvalidTranslationConfigurationException;

final class TranslationProviderFactory
{
    public const string PROVIDER_OLLAMA = 'ollama';

    public function __construct(
        private readonly string $defaultProviderName,
        private readonly OllamaTranslationProvider $ollamaTranslationProvider,
        private readonly MockTranslationProvider $mockTranslationProvider,
    ) {
    }

    public function create(?TranslationProvider $provider = null): TranslationProviderInterface
    {
        if (null !== $provider) {
            return match ($provider) {
                TranslationProvider::Qwen => $this->ollamaTranslationProvider,
                TranslationProvider::Mock => $this->mockTranslationProvider,
                TranslationProvider::DeepSeek,
                TranslationProvider::Gemini,
                TranslationProvider::Gpt => throw new InvalidTranslationConfigurationException(sprintf(
                    'Translation provider "%s" is not implemented yet.',
                    $provider->value,
                )),
            };
        }

        $normalized = strtolower(trim($this->defaultProviderName));

        if ('' === $normalized || self::PROVIDER_OLLAMA === $normalized) {
            return $this->ollamaTranslationProvider;
        }

        if ('mock' === $normalized) {
            return $this->mockTranslationProvider;
        }

        throw new InvalidTranslationConfigurationException(sprintf(
            'Unknown TRANSLATION_PROVIDER value "%s". Supported values: ollama, mock.',
            $this->defaultProviderName,
        ));
    }
}
