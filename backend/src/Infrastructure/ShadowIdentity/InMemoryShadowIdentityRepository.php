<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowIdentity;

use App\Domain\ShadowIdentity\ShadowIdentity;
use App\Domain\ShadowIdentity\ShadowIdentityId;
use App\Domain\ShadowIdentity\ShadowIdentityRepositoryInterface;

final class InMemoryShadowIdentityRepository implements ShadowIdentityRepositoryInterface
{
    /** @var array<string, ShadowIdentity> */
    private array $profilesById = [];

    /** @var array<string, string> */
    private array $scopeIndex = [];

    public function findByScope(string $scopeKey): ?ShadowIdentity
    {
        $profileId = $this->scopeIndex[$scopeKey] ?? null;

        if (null === $profileId) {
            return null;
        }

        return $this->profilesById[$profileId] ?? null;
    }

    public function findById(ShadowIdentityId $id): ?ShadowIdentity
    {
        return $this->profilesById[$id->value] ?? null;
    }

    public function save(ShadowIdentity $identity): void
    {
        $this->profilesById[$identity->id()->value] = $identity;
        $this->scopeIndex[$identity->scopeKey()] = $identity->id()->value;
    }

    public function clear(): void
    {
        $this->profilesById = [];
        $this->scopeIndex = [];
    }
}
