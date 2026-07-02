<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain\Handlers;

use App\Application\ShadowSecondBrain\WorkspaceBuilder;

final class DeleteBrainBookmarkHandler
{
    public function __construct(
        private readonly WorkspaceBuilder $builder,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(string $scopeKey, string $bookmarkId): array
    {
        $this->builder->removeBookmark($scopeKey, $bookmarkId);

        return [
            'scopeKey' => $scopeKey,
            'removedBookmarkId' => $bookmarkId,
        ];
    }
}
