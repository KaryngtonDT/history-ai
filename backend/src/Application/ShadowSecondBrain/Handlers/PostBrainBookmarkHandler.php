<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain\Handlers;

use App\Application\ShadowSecondBrain\WorkspaceBuilder;

final class PostBrainBookmarkHandler
{
    public function __construct(
        private readonly WorkspaceBuilder $builder,
    ) {
    }

    /** @param array<string, mixed> $payload */
    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey, array $payload): array
    {
        $beforeCount = count($this->builder->getWorkspace($scopeKey)->bookmarks()->all());
        $workspace = $this->builder->addBookmark($scopeKey, $payload);
        $bookmarks = $workspace->bookmarks()->all();
        $created = $bookmarks[$beforeCount] ?? $bookmarks[array_key_last($bookmarks)];

        return [
            'scopeKey' => $scopeKey,
            'bookmark' => [
                'id' => $created->id(),
                'label' => $created->label(),
                'tags' => $created->tags(),
                'conceptKey' => $created->conceptKey(),
                'resourceType' => $created->resourceType()?->value,
                'resourceId' => $created->resourceId(),
            ],
        ];
    }
}
