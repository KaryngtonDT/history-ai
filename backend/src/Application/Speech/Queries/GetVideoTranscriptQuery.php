<?php

declare(strict_types=1);

namespace App\Application\Speech\Queries;

final readonly class GetVideoTranscriptQuery
{
    public function __construct(public string $videoId)
    {
    }
}
