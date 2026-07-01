<?php

declare(strict_types=1);

namespace App\Application\YouTube\Queries;

final readonly class PreviewYouTubeQuery
{
    public function __construct(public string $url)
    {
    }
}
