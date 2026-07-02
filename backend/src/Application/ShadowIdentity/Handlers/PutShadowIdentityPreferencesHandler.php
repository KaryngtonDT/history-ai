<?php

declare(strict_types=1);

namespace App\Application\ShadowIdentity\Handlers;

use App\Application\ShadowIdentity\ShadowIdentityJsonMapper;
use App\Domain\ShadowIdentity\ShadowIdentityRepositoryInterface;
use App\Domain\ShadowIdentity\ShadowVoicePersona;

final class PutShadowIdentityPreferencesHandler
{
    public function __construct(
        private readonly ShadowIdentityRepositoryInterface $repository,
        private readonly ShadowIdentityJsonMapper $mapper,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function __invoke(array $payload, string $scopeKey = 'default'): array
    {
        $identity = $this->repository->findByScope($scopeKey)
            ?? \App\Domain\ShadowIdentity\ShadowIdentity::create(scopeKey: $scopeKey);

        if (is_string($payload['persona'] ?? null)) {
            $persona = ShadowVoicePersona::tryFrom($payload['persona']);

            if (null !== $persona) {
                $identity = $identity->withPersona($persona);
            }
        }

        if (is_int($payload['challengeLevel'] ?? null)) {
            $identity = $identity->withChallengeLevel($payload['challengeLevel']);
        }

        if (is_array($payload['voiceProfile'] ?? null)) {
            $voice = $identity->preferences()->voiceProfile();
            $voicePayload = $payload['voiceProfile'];

            if (is_string($voicePayload['voiceId'] ?? null) && is_string($voicePayload['engine'] ?? null)) {
                $voice = $voice->withVoice($voicePayload['voiceId'], $voicePayload['engine']);
            }

            if (is_numeric($voicePayload['speed'] ?? null)) {
                $voice = $voice->withSpeed((float) $voicePayload['speed']);
            }

            $identity = $identity->withVoiceProfile($voice);
        }

        $this->repository->save($identity);

        return $this->mapper->toArray($identity);
    }
}
