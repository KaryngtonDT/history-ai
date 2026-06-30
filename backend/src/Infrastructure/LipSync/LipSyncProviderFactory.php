<?php

declare(strict_types=1);

namespace App\Infrastructure\LipSync;

use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncProviderInterface;
use App\Infrastructure\LipSync\Exception\InvalidLipSyncConfigurationException;

final class LipSyncProviderFactory
{
    public const string PROVIDER_LATENTSYNC = 'latentsync';

    public function __construct(
        private readonly string $defaultProviderName,
        private readonly LatentSyncProvider $latentSyncProvider,
        private readonly MockLipSyncProvider $mockLipSyncProvider,
    ) {
    }

    public function resolve(?LipSyncProvider $provider = null): LipSyncProviderInterface
    {
        if (null !== $provider) {
            return match ($provider) {
                LipSyncProvider::LatentSync => $this->latentSyncProvider,
                LipSyncProvider::Mock => $this->mockLipSyncProvider,
                LipSyncProvider::Wav2Lip => throw new InvalidLipSyncConfigurationException(sprintf(
                    'Lip sync provider "%s" is not implemented yet.',
                    $provider->value,
                )),
            };
        }

        $normalized = strtolower(trim($this->defaultProviderName));

        if ('' === $normalized || self::PROVIDER_LATENTSYNC === $normalized) {
            return $this->latentSyncProvider;
        }

        if ('mock' === $normalized) {
            return $this->mockLipSyncProvider;
        }

        throw new InvalidLipSyncConfigurationException(sprintf(
            'Unknown LIP_SYNC_PROVIDER value "%s". Supported values: latentsync, mock.',
            $this->defaultProviderName,
        ));
    }
}
