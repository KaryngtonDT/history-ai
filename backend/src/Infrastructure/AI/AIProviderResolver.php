<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Domain\AI\AIEngineCapability;
use App\Domain\AI\AIEngineConfiguration;
use App\Domain\AI\AIEngineProvider;
use App\Domain\AI\AIEngineRegistry;
use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationProviderInterface;
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\TTS\TextToSpeechProviderInterface;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceCloneProviderInterface;
use App\Infrastructure\AI\Exception\InvalidAIEngineConfigurationException;
use App\Infrastructure\Speech\SpeechToTextProviderFactory;
use App\Infrastructure\Translation\TranslationProviderFactory;
use App\Infrastructure\TTS\TextToSpeechProviderFactory;
use App\Infrastructure\VoiceClone\VoiceCloneProviderFactory;

final class AIProviderResolver implements AIProviderResolverInterface
{
    public function __construct(
        private readonly AIEngineRegistry $registry,
        private readonly AIEngineConfiguration $configuration,
        private readonly SpeechToTextProviderFactory $speechToTextProviderFactory,
        private readonly TranslationProviderFactory $translationProviderFactory,
        private readonly TextToSpeechProviderFactory $textToSpeechProviderFactory,
        private readonly VoiceCloneProviderFactory $voiceCloneProviderFactory,
    ) {
    }

    public function registry(): AIEngineRegistry
    {
        return $this->registry;
    }

    public function resolveSpeechToText(?string $providerId = null): SpeechToTextProviderInterface
    {
        $resolvedProviderId = $providerId ?? $this->configuration->defaultProviderFor(AIEngineCapability::SpeechToText);

        if (null === $resolvedProviderId) {
            throw new InvalidAIEngineConfigurationException('No speech-to-text provider configured.');
        }

        $this->assertProviderEnabled(AIEngineCapability::SpeechToText, $resolvedProviderId);

        return match ($resolvedProviderId) {
            AIEngineRegistryFactory::PROVIDER_FASTER_WHISPER => $this->speechToTextProviderFactory->create(),
            default => throw new InvalidAIEngineConfigurationException(sprintf(
                'Speech-to-text provider "%s" is not registered.',
                $resolvedProviderId,
            )),
        };
    }

    public function resolveTranslation(?TranslationProvider $provider = null): TranslationProviderInterface
    {
        if (null !== $provider) {
            $providerId = $this->mapTranslationProviderToRegistryId($provider);
            $this->assertProviderEnabled(AIEngineCapability::Translation, $providerId);

            return $this->translationProviderFactory->resolve($provider);
        }

        $resolvedProviderId = $this->configuration->defaultProviderFor(AIEngineCapability::Translation);

        if (null === $resolvedProviderId) {
            throw new InvalidAIEngineConfigurationException('No translation provider configured.');
        }

        $this->assertProviderEnabled(AIEngineCapability::Translation, $resolvedProviderId);

        return match ($resolvedProviderId) {
            AIEngineRegistryFactory::PROVIDER_OLLAMA => $this->translationProviderFactory->resolve(null),
            default => throw new InvalidAIEngineConfigurationException(sprintf(
                'Translation provider "%s" is not registered.',
                $resolvedProviderId,
            )),
        };
    }

    public function resolveTextToSpeech(?TextToSpeechProvider $provider = null): TextToSpeechProviderInterface
    {
        if (null !== $provider) {
            $providerId = $provider->value;
            $this->assertProviderEnabled(AIEngineCapability::TextToSpeech, $providerId);

            return $this->textToSpeechProviderFactory->resolve($provider);
        }

        $resolvedProviderId = $this->configuration->defaultProviderFor(AIEngineCapability::TextToSpeech);

        if (null === $resolvedProviderId) {
            throw new InvalidAIEngineConfigurationException('No text-to-speech provider configured.');
        }

        $this->assertProviderEnabled(AIEngineCapability::TextToSpeech, $resolvedProviderId);

        return match ($resolvedProviderId) {
            AIEngineRegistryFactory::PROVIDER_F5_TTS => $this->textToSpeechProviderFactory->resolve(null),
            default => throw new InvalidAIEngineConfigurationException(sprintf(
                'Text-to-speech provider "%s" is not registered.',
                $resolvedProviderId,
            )),
        };
    }

    public function resolveVoiceClone(?VoiceCloneProvider $provider = null): VoiceCloneProviderInterface
    {
        if (null !== $provider) {
            $providerId = $provider->value;
            $this->assertProviderEnabled(AIEngineCapability::VoiceClone, $providerId);

            return $this->voiceCloneProviderFactory->resolve($provider);
        }

        $resolvedProviderId = $this->configuration->defaultProviderFor(AIEngineCapability::VoiceClone);

        if (null === $resolvedProviderId) {
            throw new InvalidAIEngineConfigurationException('No voice clone provider configured.');
        }

        $this->assertProviderEnabled(AIEngineCapability::VoiceClone, $resolvedProviderId);

        return match ($resolvedProviderId) {
            AIEngineRegistryFactory::PROVIDER_OPENVOICE => $this->voiceCloneProviderFactory->resolve(null),
            default => throw new InvalidAIEngineConfigurationException(sprintf(
                'Voice clone provider "%s" is not registered.',
                $resolvedProviderId,
            )),
        };
    }

    private function assertProviderEnabled(AIEngineCapability $capability, string $providerId): void
    {
        $provider = $this->findProvider($capability, $providerId);

        if (null === $provider) {
            throw new InvalidAIEngineConfigurationException(sprintf(
                'Provider "%s" is not registered for capability "%s".',
                $providerId,
                $capability->value,
            ));
        }

        if (!$provider->isEnabled()) {
            throw new InvalidAIEngineConfigurationException(sprintf(
                'Provider "%s" is disabled for capability "%s".',
                $providerId,
                $capability->value,
            ));
        }
    }

    private function findProvider(AIEngineCapability $capability, string $providerId): ?AIEngineProvider
    {
        foreach ($this->registry->allProviders() as $provider) {
            if ($provider->capability() === $capability && $provider->providerId() === $providerId) {
                return $provider;
            }
        }

        return null;
    }

    private function mapTranslationProviderToRegistryId(TranslationProvider $provider): string
    {
        return match ($provider) {
            TranslationProvider::Qwen,
            TranslationProvider::DeepSeek,
            TranslationProvider::Gemini,
            TranslationProvider::Gpt => AIEngineRegistryFactory::PROVIDER_OLLAMA,
            TranslationProvider::Mock => 'mock',
        };
    }
}
