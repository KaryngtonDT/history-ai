<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Artifact;

use App\Application\Artifact\DTO\ArtifactListItem;
use App\Application\Artifact\DTO\ListArtifactsByContentResult;

final class ListArtifactsResponse
{
    /**
     * @return list<array{
     *     id: string,
     *     contentId: string,
     *     processingJobId: string,
     *     type: string,
     *     content: string,
     *     createdAt: string
     * }>
     */
    public static function fromResult(ListArtifactsByContentResult $result): array
    {
        return array_map(
            static fn (ArtifactListItem $item): array => $item->toArray(),
            $result->items,
        );
    }
}
