<?php

declare(strict_types=1);

namespace App\Infrastructure\Semantic;

use App\Domain\Semantic\EmbeddingProviderInterface;
use App\Infrastructure\Semantic\Exception\InvalidEmbeddingProviderConfigurationException;

final class EmbeddingProviderFactory
{
    public const string PROVIDER_DETERMINISTIC = 'deterministic';

    public const string PROVIDER_GEMINI = 'gemini';

    public function __construct(
        private readonly string $providerName,
        private readonly string $geminiApiKey,
        private readonly DeterministicEmbeddingProvider $deterministicProvider,
        private readonly GeminiEmbeddingTransportInterface $geminiTransport,
        private readonly string $geminiModel = GeminiEmbeddingProvider::DEFAULT_MODEL,
    ) {
    }

    public function create(): EmbeddingProviderInterface
    {
        $normalized = strtolower(trim($this->providerName));

        if ('' === $normalized || self::PROVIDER_DETERMINISTIC === $normalized) {
            return $this->deterministicProvider;
        }

        if (self::PROVIDER_GEMINI === $normalized) {
            if ('' === trim($this->geminiApiKey)) {
                throw new InvalidEmbeddingProviderConfigurationException(
                    'GEMINI_API_KEY is required when EMBEDDING_PROVIDER=gemini.',
                );
            }

            return new GeminiEmbeddingProvider(
                $this->geminiTransport,
                $this->geminiApiKey,
                $this->geminiModel,
            );
        }

        throw new InvalidEmbeddingProviderConfigurationException(sprintf(
            'Unknown EMBEDDING_PROVIDER value "%s". Supported values: deterministic, gemini.',
            $this->providerName,
        ));
    }
}
