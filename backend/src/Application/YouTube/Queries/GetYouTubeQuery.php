<?php

declare(strict_types=1);

namespace App\Application\YouTube\Queries;

final readonly class GetYouTubeQuery
{
    public function __construct(public string $youtubeId)
    {
    }
}
