<?php

declare(strict_types=1);

namespace App\Application\ShadowRelationship\Handlers;

use App\Application\ShadowRelationship\RelationshipContextComposer;

final class GetRelationshipInterestsHandler
{
    public function __construct(
        private readonly RelationshipContextComposer $contextComposer,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey = 'default'): array
    {
        return [
            'scopeKey' => $scopeKey,
            'interests' => array_map(
                static fn ($trait): array => [
                    'key' => $trait->key(),
                    'label' => $trait->label(),
                    'strength' => $trait->strength()->value,
                    'confirmed' => $trait->confirmed(),
                ],
                $this->contextComposer->interests($scopeKey),
            ),
        ];
    }
}
