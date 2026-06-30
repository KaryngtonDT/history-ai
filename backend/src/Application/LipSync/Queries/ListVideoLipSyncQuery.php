<?php

declare(strict_types=1);

namespace App\Application\LipSync\Queries;

final readonly class ListVideoLipSyncQuery
{
    public function __construct(public string $videoId)
    {
    }
}
