<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

interface ShadowIdentityRepositoryInterface
{
    public function findByScope(string $scopeKey): ?ShadowIdentity;

    public function findById(ShadowIdentityId $id): ?ShadowIdentity;

    public function save(ShadowIdentity $identity): void;
}
