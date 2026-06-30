<?php

declare(strict_types=1);

namespace App\Infrastructure\VoiceClone;

use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceCloneProviderInterface;
use App\Infrastructure\VoiceClone\Exception\InvalidVoiceCloneConfigurationException;

final class VoiceCloneProviderFactory
{
    public const string PROVIDER_OPENVOICE = 'openvoice';

    public function __construct(
        private readonly string $defaultProviderName,
        private readonly OpenVoiceProvider $openVoiceProvider,
        private readonly MockVoiceCloneProvider $mockVoiceCloneProvider,
    ) {
    }

    public function resolve(?VoiceCloneProvider $provider = null): VoiceCloneProviderInterface
    {
        if (null !== $provider) {
            return match ($provider) {
                VoiceCloneProvider::OpenVoice => $this->openVoiceProvider,
                VoiceCloneProvider::Mock => $this->mockVoiceCloneProvider,
                VoiceCloneProvider::SeedVC => throw new InvalidVoiceCloneConfigurationException(sprintf(
                    'Voice clone provider "%s" is not implemented yet.',
                    $provider->value,
                )),
            };
        }

        $normalized = strtolower(trim($this->defaultProviderName));

        if ('' === $normalized || self::PROVIDER_OPENVOICE === $normalized) {
            return $this->openVoiceProvider;
        }

        if ('mock' === $normalized) {
            return $this->mockVoiceCloneProvider;
        }

        throw new InvalidVoiceCloneConfigurationException(sprintf(
            'Unknown VOICE_CLONE_PROVIDER value "%s". Supported values: openvoice, mock.',
            $this->defaultProviderName,
        ));
    }
}
