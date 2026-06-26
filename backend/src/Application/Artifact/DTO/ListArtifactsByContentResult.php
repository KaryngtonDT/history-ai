<?php

declare(strict_types=1);

namespace App\Application\Artifact\DTO;

final readonly class ListArtifactsByContentResult
{
    /**
     * @param list<ArtifactListItem> $items
     */
    public function __construct(
        public array $items,
    ) {
    }
}
