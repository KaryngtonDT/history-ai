<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

use App\Domain\ShadowIdentity\Exception\InvalidShadowIdentityException;

final readonly class ShadowIdentity
{
    public function __construct(
        private ShadowIdentityId $id,
        private string $scopeKey,
        private ShadowIdentityPreferences $preferences,
        private ShadowIdentitySnapshotCollection $history,
    ) {
        if ('' === trim($scopeKey)) {
            throw new InvalidShadowIdentityException('Shadow identity scope cannot be empty.');
        }
    }

    public static function create(
        ?ShadowIdentityId $id = null,
        string $scopeKey = 'default',
    ): self {
        $preferences = ShadowIdentityPreferences::default();

        return new self(
            $id ?? ShadowIdentityId::generate(),
            trim($scopeKey),
            $preferences,
            ShadowIdentitySnapshotCollection::empty()->append(
                ShadowIdentitySnapshot::capture($preferences, 'Initial profile', 'system'),
            ),
        );
    }

    public function id(): ShadowIdentityId
    {
        return $this->id;
    }

    public function scopeKey(): string
    {
        return $this->scopeKey;
    }

    public function preferences(): ShadowIdentityPreferences
    {
        return $this->preferences;
    }

    public function history(): ShadowIdentitySnapshotCollection
    {
        return $this->history;
    }

    public function applyPreferences(
        ShadowIdentityPreferences $preferences,
        string $label,
        string $source = 'user',
    ): self {
        return new self(
            $this->id,
            $this->scopeKey,
            $preferences,
            $this->history->append(
                ShadowIdentitySnapshot::capture($preferences, $label, $source),
            ),
        );
    }

    public function withPersona(ShadowVoicePersona $persona): self
    {
        return $this->applyPreferences(
            $this->preferences->withPersona($persona),
            sprintf('Persona → %s', $persona->value),
        );
    }

    public function withVoiceProfile(ShadowVoiceProfile $voiceProfile): self
    {
        return $this->applyPreferences(
            $this->preferences->withVoiceProfile($voiceProfile),
            sprintf('Voice → %s', $voiceProfile->voiceId()),
        );
    }

    public function withChallengeLevel(int $level): self
    {
        return $this->applyPreferences(
            $this->preferences->withChallengeProfile(
                $this->preferences->challengeProfile()->withLevel($level),
            ),
            sprintf('Challenge → %d', $level),
        );
    }

    public function reset(): self
    {
        $defaults = ShadowIdentityPreferences::default();

        return new self(
            $this->id,
            $this->scopeKey,
            $defaults,
            $this->history->append(
                ShadowIdentitySnapshot::capture($defaults, 'Profile reset', 'user'),
            ),
        );
    }

    public function forgetPreference(string $key): self
    {
        return $this->applyPreferences(
            $this->preferences->withMemoryPolicy(
                $this->preferences->memoryPolicy()->forgetPreference($key),
            ),
            sprintf('Forgot preference: %s', $key),
        );
    }
}
