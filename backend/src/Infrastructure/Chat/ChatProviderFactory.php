<?php

declare(strict_types=1);

namespace App\Infrastructure\Chat;

use App\Domain\Chat\ChatProviderInterface;
use App\Infrastructure\Chat\Exception\InvalidChatProviderConfigurationException;

final class ChatProviderFactory
{
    public const string PROVIDER_MOCK = 'mock';

    public const string PROVIDER_GEMINI = 'gemini';

    public function __construct(
        private readonly string $providerName,
        private readonly string $geminiApiKey,
        private readonly MockChatProvider $mockProvider,
        private readonly GeminiChatTransportInterface $geminiTransport,
        private readonly string $geminiModel = GeminiChatProvider::DEFAULT_MODEL,
    ) {
    }

    public function create(): ChatProviderInterface
    {
        $normalized = strtolower(trim($this->providerName));

        if ('' === $normalized || self::PROVIDER_MOCK === $normalized) {
            return $this->mockProvider;
        }

        if (self::PROVIDER_GEMINI === $normalized) {
            if ('' === trim($this->geminiApiKey)) {
                throw new InvalidChatProviderConfigurationException(
                    'GEMINI_API_KEY is required when CHAT_PROVIDER=gemini.',
                );
            }

            return new GeminiChatProvider(
                $this->geminiTransport,
                $this->geminiApiKey,
                $this->geminiModel,
            );
        }

        throw new InvalidChatProviderConfigurationException(sprintf(
            'Unknown CHAT_PROVIDER value "%s". Supported values: mock, gemini.',
            $this->providerName,
        ));
    }
}
