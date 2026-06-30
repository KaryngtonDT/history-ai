<?php

declare(strict_types=1);

namespace App\Application\VideoIntelligence\Queries;

final readonly class GetVideoIntelligenceQuery
{
    public function __construct(public string $videoId)
    {
    }
}
