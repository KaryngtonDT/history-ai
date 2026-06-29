<?php

declare(strict_types=1);

namespace App\Application\Translation\Queries;

final readonly class ListVideoTranslationsQuery
{
    public function __construct(public string $videoId)
    {
    }
}
